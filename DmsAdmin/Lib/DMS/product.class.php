<?php
	class product extends stru{
		//是否存在成本价
		public $cost = true;
		//是否显示总库存量
		public $stock = true;
		//是否显示图片
		public $image = true;
		//是否增加产品PV
		public $productPV = false;
		//是否显示规格
		public $guige = false;
		//产品数量验证
		public $productnumCheck = true;
		//默认没有添加产品分类的情况下，自动添加的分类名称，逗号分隔
		public $class='';
		//自定义价格
		public function getfieldCon(){
			return $this->getcon('con',array('name'=>''));
		}
		
		//获得产品数组注册升级购买页面
		public function getProductArray($sale){
			$proname=$this->name;
			$product = M($proname);
			$productArr = array();
			//可使用的分类
			$Category=M($proname."_分类")->where(array('状态'=>'使用'))->getField('id,名称');
			if(!$Category) return $productArr;
			//选出产品
			$proWhere=array('状态'=>'使用',$sale->productMoney=>array('gt',0),'分类'=>array('in',implode(',',$Category)));
			//开启数量验证或者开启出入库
			if($this->productnumCheck || adminshow('prostock')){
				$proWhere['可订购数量']=array('gt',0);
			}
			$productList = $product->where($proWhere)->select();
			if($productList){			
				foreach($productList as $keys=>$pro)
				{
					$proCateName = $pro['分类'];
					$ids = explode(',',$pro['所属功能']);
					//获取当前产品的产品功能
					$progong_res = M('产品_功能')->where(array('id'=>array('in',$ids)))->select();
					if($progong_res){
						//遍历产品所属功能的节点名称
						$jiedian = '';						
						foreach($progong_res as $key=>$v){
							$jiedian.= ','.$v['节点名称'];
						}
						//把当前产品所属的节点名称形成一个数组
						$pro_jidian = array();
						$pro_jidian = explode(",",trim($jiedian,','));
					   	
						//判断此产品是否属于这些节点
						if(!in_array($sale->name,$pro_jidian)){
					  		continue;
						}
						unset($pro_jidian);
					}
					$productArr[$proCateName][] = $pro;
				}
			}
			return $productArr;
		}
		
		//产品验证
		public function formVerify(&$ret,&$saleobj){
			$productArr = isset($_POST['productNum'])?$_POST['productNum']:array();
			$productModel = M($this->name);
			$productCountMoney = 0;
			$productPV = 0;
			foreach($productArr as $productid=>$productnum){
				$productResult = $productModel->find($productid);
				$productnum = intval($productnum);
				$productCountMoney += $productnum * $productResult[$saleobj->productMoney];
				$productPV += $productnum * $productResult['PV'];	
				//验证库存
				if($productnum !=0 && ($this->productnumCheck || adminshow('prostock'))){
					$_POST['productnum'.$productid] = $productnum;
					$ret[] = array('productnum'.$productid,'0,'.$productResult['可订购数量'],$productResult['名称'].'<font color="red">库存不足</font>',1,'between');
				}
			}
			
			$_POST['productCountMoney'] = $productCountMoney;
			$_POST['productCountPV'] = $productPV;
			
			//验证整数倍
			if($saleobj->productMoneyInt !== 0){
				$ret[] = array('productCountMoney',array($this,"productMoneyInt"),$saleobj->productName.'总金额必须为'.$saleobj->productMoneyInt.'的整数倍',1,'function',3,array($saleobj->productMoneyInt));
			}
			//判断一下注册是否填写的是空点 如果是空点的话则默认为产品不再进行验证
			if(get_class($saleobj)=='sale_reg'){
			    //判断是否注册的是空点
			    if(isset($_POST['nullMode']) && $_POST['nullMode']>0){
			    	$saleobj->productMoneyMode = 0;
			    }
			}
			
			//验证金额还是pv,sale_buy目前不支持pv验证
			if($saleobj->productCheck==''){
				$check='productCountMoney';
				$salecheck='money';
				$yzstr='金额';
			}elseif(strtolower($saleobj->productCheck)=='pv'){
				$check='productCountPV';
				$salecheck='pvmoney';
				$yzstr='PV';
			}
			//获取注册信息
			$saleMoneys = $saleobj->getSaleMoneys($_POST);
			//必须等于
			if($saleobj->productMoneyMode == 1){
			    $ret[] = array($check,$saleMoneys[$salecheck],$saleobj->productName.'总'.$yzstr.'必须等于报单'.$yzstr.':'.$saleMoneys[$salecheck],1,'equal');
			}elseif($saleobj->productMoneyMode == 2){//大于等于
				//sale_buy，不用提示‘报单金额或pv’字段
				$bdstr='报单'.$yzstr;
				if(get_class($saleobj)=='sale_buy' && $saleMoneys[$salecheck]==0){
					$saleMoneys[$salecheck]=1;
					$bdstr='';
				}
				$ret[] = array($check,$saleMoneys[$salecheck].',99999999999',$saleobj->productName.'总'.$yzstr.'必须大于或等于'.$bdstr.':'.$saleMoneys[$salecheck],1,'between');
			}
		}

		//验证总金额倍数
		public function productMoneyInt($productMoney,$int){
			if($productMoney%$int == 0){
				return true;
			}else{
				return false;
			}
		}
		
		//获取购物信息
		public function setField(&$sdata,$productArr,$sale){
			$sdata["购物金额"] 	= 0;
			$sdata["购物PV"]	= 0;
			$sdata["产品总重量"]= 0;
			$productdata=array();
			$productModel = M($this->name);
			foreach($productArr as $productid=>$productnum){
				$productnum = abs(intval($productnum));
				if($productnum>0){
					$productResult = $productModel->find($productid);
					$sdata["购物金额"] += $productnum * $productResult[$sale->productMoney];
					$sdata["产品总重量"] += $productnum * $productResult['重量'];
					$sdata["购物PV"] += $productnum * $productResult['PV'];	
					$productdata[] = array(
						'产品id'=>$productResult['id'],
						'产品节点'=>$this->name,
						'名称'=>$productResult['名称'],
						'分类'=>$productResult['分类'],
						'规格'=>$productResult['规格'],
						'数量'=>$productnum,
						'总重量'=>$productnum * $productResult['重量'],
						'价格'=>$productResult[$sale->productMoney],
						'PV'=>$productResult['PV'],
						'总价'=>$productnum * $productResult[$sale->productMoney],
						'总PV'=>$productnum * $productResult['PV'],
					);
				}
			}
			if($productdata){
				$sdata["产品"] = 1;
			}
			$sdata["物流状态"] = "未发货";
			//是否开启物流收费
			if($sale->logistic){
				$sdata["物流费"]=$this->getWlf($sdata['产品总重量'],$sdata['收货省份']);
			}
			return $productdata;
		}
		
		//计算物流费
		public function getWlf($weight,$province){
			$wlf=0;
			if($weight>0){
				$where=array('发往省份'=>$province);
				
				$res  = M('产品物流管理')->where($where)->find();
				if(!$res){
					//查询标准的
					$res = M('产品物流管理')->where(array('是否全国标准'=>1))->find();
				}
				if($res){
					if($weight<=$res['首重']){
						$wlf = floatval($res['首重价格']);
					}else{	  	  
						$wlf = floatval($res['首重价格']+((float)($weight-$res['首重'])*$res['续重价格']));
					}
				}
			}
			return $wlf;
		}
		
		//发货出库
		public function outpro($saleid,$memo="公司发货"){
			$sdata=M("报单")->where(array("id"=>$saleid))->find();
			if($sdata && $sdata['产品']==1){
				$products=M("产品订单")->where(array("报单id"=>$saleid))->select();
				if($products){
					foreach($products as $pro){
						$product=X("product@".$pro['产品节点']);
						//减少数量
						$product->stock($pro['产品id'],$pro['数量'],'数量');
						if(adminshow('prostock')){
							//出库
							$data=array(
								'产品id'=>$pro['产品id'],
								'报单id'=>$saleid,
								'数量'=>-$pro['数量'],
								'产品节点'=>$pro['产品节点'],
								'操作人'=>$_SESSION["loginAdminAccount"],
								'操作时间'=>systemTime(),
								'备注'=>$memo
							); 
							$res = M('产品库存')->add($data);
						}
					}
				}
			}
		}
		
		//减去数量
		public function stock($proid,$num,$dec='可订购数量'){
			//减去数量
			if($this->productnumCheck || adminshow('prostock')){
				if($num>0){
					$pro=M($this->name)->find($proid);
					if($pro){
						M($this->name)->where(array('id'=>$proid))->setDec($dec,$num);
					}
				}
			}
		}
		//验证数量
		public function checknum($proid,$num,$check='可订购数量'){
			$back='';
			$msg=array("可订购数量"=>"可订购库存","数量"=>"未发货库存");
			if(($this->productnumCheck && $check=='可订购数量')  || adminshow('prostock')){
				$pro=M($this->name)->find($proid);
				if($pro && $pro[$check]<$num){
					$back=$this->name."ID:".$proid."的".$msg[$check]."不足";
				}
			}
			return $back;
		}
		//清空
		public function event_sysclear(){
			//M()->execute('truncate table `dms_'.$this->name.'`');
			//M()->execute('truncate table `dms_'.$this->name.'_分类'.'`');
			if(adminshow('prostock')){
				M()->execute('truncate table `dms_产品库存`');
			}
			//入库和初始化数量
			foreach(X("product") as $product){
				$name=$product->name;
				M()->execute("update dms_".$name." set 可订购数量=数量");
				if(adminshow('prostock')){
					$proinfo=M($name)->where(array("可订购数量"=>array("gt",0)))->select();
					if(!$proinfo) continue;
					foreach($proinfo as $pro){
						$indata=array(
							'产品id'=>$pro['id'],
							'数量'=>$pro['可订购数量'],
							'产品节点'=>$name,
							'操作人'=>$_SESSION["loginAdminAccount"],
							'操作时间'=>time(),
							'备注'=>'系统初始化'
						); 
						$res = M('产品库存')->add($indata);
					}
				}
			}
		}
	}
?>