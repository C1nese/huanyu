<?php
	class fun_stock extends stru
	{
		public $use=true;   //该标签是否可用
		public $splitStart=true;//是否开启拆分功能（显示拆分列表）
		public $adminSell=false;//是否开启管理员一键挂单功能（显示一键挂单操作）
		
		public $tradeBank='';  //购买股票所需要的货币
		//public $name='';//存储股票数量的账户
		public $decimalLen=2;
		public $stockStartMoney=0.1;  //股票起始价格
		public $stockMoneyForm="[股票原始价格]+floor([股票交易总数]/100000)*0.001"; //股票价格计算公式
		//[股票原始价V格]+floor((start([股票已认购量],10)+[股票交易总数])/100000)*0.01
		//[股票原始价格]+floor([股票交易总数]/100000)*0.001
		public $stockMinint=10;  //股票交易的最小整数倍
		public $stockClose=false;  //股票市场休市
		public $stockInputPrice=false;  //用户定价
		public $type=array(0=>'自由户卖出',1=>'自由户买入',3=>'托管户卖出');
		public $cashBank='';  //出售股票
		public $priceLen=4;  //价格小数位
        private $addstock=array();//用于三进三出增加的股票数量['nameid']='num'
		private $cashmoney=array();//用于三进三出中用于存购买股票账户挂单卖出的股票的钱 ['nameid']='money'
		private $trademoney=array();//购买后剩余应该返回到交易账户的钱['nameid']='money'
		public $stockOutTax=0;  //股票出售手续费
		public $stockBuyTax=0;  //股票购买手续费	
		const TRADE_SELL = 1;
		const TRADE_BUY = 2;
		//$type=1卖出，2$买入
		private $logstr='';
		
		//获得股票价格
		public function stockPrice($time=NULL)
		{
			$stockForm=$this->getatt('stockMoneyForm');
			if($stockForm==""){
				return$this->getatt('stockMoneyForm');
			}
			
			$stockprice=$stockForm;
			$num=$this->getatt("stockTrade");
			if($time!=NULL){
				$where['addtime']=array('elt',$time);
				$num=M($this->name."交易")->where($where)->sum('num');
			    if($num==null) $num=0;
			}else{
				$where="1";
			}
			preg_match_all('/\[(.*)\]/U',$stockForm,$truevals,PREG_SET_ORDER);
			if(count($truevals)>0){
				foreach($truevals as $trueval)
				{
					if($trueval[1]=='股票原始价格'){
						$stockprice=str_replace($trueval[0],$this->getatt('stockStartMoney'),$stockprice);
						}
					if($trueval[1]=='股票交易总数'){
						$stockprice=str_replace($trueval[0],$num,$stockprice);
						}
					if($trueval[1]=='股票已认购量'){
						$stockprice=str_replace($trueval[0],$this->getatt('stockHasGive'),$stockprice);
						}
					if($trueval[1]=='股票拆分'){
						$stockprice=str_replace($trueval[0],$this->getatt('StockSplit'),$stockprice);
						}
				}
			}
			preg_match_all('/start\((.*),(.*)\)/U',$stockprice,$twovals,PREG_SET_ORDER);
			
			if(count($twovals)>0){
				foreach($twovals as $twoval)
				{
					if($twoval[1]>$twoval[2]){
					  $stockprice=str_replace($twoval[0],$twoval[1]-$twoval[2],$stockprice);
					}else{
						 $stockprice=str_replace($twoval[0],0,$stockprice);
					}
				}
			}
			eval('$stockprice='.$stockprice.';');
			$stockprice=sprintf("%01.".$this->priceLen."f",$stockprice);
			if($stockprice!=$this->getatt('stockNowPrice')){
				 $this->setatt('stockNowPrice',$stockprice);
                 $this->uptrend(array('价格'=>$stockprice));
				 //$this->trendxml();
			}
			return $stockprice;
		}
		//判断交易账户余额
		public function tradeMoney($money,$user)
		{
			if($user[$this->tradeBank]<$money){
		 	 return false;
			}
			//M($this->parent()->name)->where("编号='".$user['编号']."'")->save(array($this->cashBank=>array('exp',$this->cashBank.'-'.$money)));
			return true;
		}
		//挂单记录
		public function setcompany($userid,$price,$num,$account,$type)
		{
			//$type=1卖出，成交价格高于卖价的，2买入，成交价格低于买价的
			//增加了挂单记录
			$order_m=M($this->name."市场");
			$data=array();
			$data['编号']=$userid;
			$data['price']=$price;
			$data['num'] =$num;
			$data['num1']=$num;
			$data['num2']=0;
			$data['addtime']=systemTime();
			$data['type']=$type;
			$data['账户']=$account;
			$data['tradeinfo']=serialize(array());
			$id=$order_m->add($data);
			//剩余购买量
			$surplus = $num;		

			//如果优先购买公司发售股,下面有段一样的代码，是后购买
			$uparray=array();
			if($type == self::TRADE_BUY && $this->stockPrice()<=$price){
				$comnum=$this->isbuyComStock($userid,$surplus,$account,$price,$uparray);
				$surplus-=$comnum;
			}
			if($surplus>0){
				//得到匹配交易信息
				$where=array(
					'price'=>array(($type==1 ? 'egt' :'elt') ,$price),
					'type'=>array('eq',$type==1? 2 : 1),
					'编号'=>array('neq',$data['编号']),
					'num'=>array('gt',0),
					'state'=>array('eq',0),
					);		
				$lists=$order_m->order(($type==1 ? "price desc," : "price asc,") . "addtime asc")
							   ->where($where)
							   ->select();

				//定义交易期间的剩余成交量
				foreach($lists as $list)
				{
					$diffmoney=0;  //购买股票时有可能购买比定价低的股票 故应该返还
					if($surplus<=0) break;
					//得到当前用户的股券交易最大量
					$thisnum = $list['num']<$surplus ? $list['num'] : $surplus;
					//当前股券每股价格基准
					$thisprice=$type == self::TRADE_BUY ? $list['price'] : $price;
					//当前用户的交易额
					$thismoney=$thisprice * $thisnum;
					//卖出得到现金金额   当前循环用户的买入，得到股票数量
					if($type == self::TRADE_SELL)
					{
						$username=$userid;//卖出
						$tranaccount=$account;
						$fromuser=$list['编号'];//买入
						$sellid=$id;
						$buyid=$list['id'];
						//所查找的订单为买入单  订单价格-交易价 为差价  需返款
						if($thisprice<$list['price']){
							$diffmoney=($list['price']-$thisprice)*$thisnum;
						}
					} 
					//买入得到股券数量   当前循环用户的卖出 得到现金金额
					if($type == self::TRADE_BUY)
					{
						$username=$list['编号'];//卖出
						$tranaccount=$account;
						$fromuser=$userid;//买入
						$sellid=$list['id'];
						$buyid=$id;
						//所查找的订单为卖出单  提交价格-交易价 为差价  需返款
						if($thisprice<$price){
							$diffmoney=($price-$thisprice)*$thisnum;
						}
					}
					$tradeinfo=unserialize($list['tradeinfo']);
					$tradeinfo[]=array(
						'name'=>$userid,
						'price'=>$thisprice,
						'num'=>$thisnum,
						'time'=>systemTime(),
						'money'=>$thismoney
					);
					$update=array(
						'num'=>array('exp','num-'.$thisnum),
						'num2'=>array('exp','num2+'.$thisnum),
						'tradeinfo'=>serialize($tradeinfo)
						);
					$order_m->where(array('id'=>$list['id']))->save($update);
				   //返款  买入便宜的
				   if($diffmoney>0){
					  bankset($this->tradeBank,$fromuser,$diffmoney,L('买入返款'),"买入ID".$buyid.",卖出ID".$sellid);
					}
				   
					//用户表中$username的 现金 $this->cashBank  账户 + $thismoney并增加一个现金账户交易明细
					$memo=$this->parent()->name.$fromuser."花费买入".$thismoney."元".$username."卖出股票的".$thisnum."股,每股".$thisprice."元";
					 //交易记录
					 $this->setdetail($username,$sellid,$fromuser,$buyid,$thisnum,$thisprice,$tranaccount,$memo);
					$this->selldo($username,$fromuser,$tranaccount,$thismoney,$sellid);
					// 并增加一个股票账户交易明细
					$this->setrecord($fromuser,$thisprice,$thisnum,$tranaccount,$memo,2);
					$this->uptrend(array('成交量'=>$thisnum,'成交金额'=>$thismoney));
					$surplus-=$thisnum;
					
					//明细
					$uparray[]=array(
						'name'=>$list['编号'],
						'price'=>$thisprice,
						'num'=>$thisnum,
						'time'=>systemTime(),
						'money'=>$thismoney
					);
				}
				//执行三进三出
				//$this->buydeal();
				
			}
			//如果还有没购买的则购买公司发售股,公司股不优先
			if($surplus>0){
				if($type == self::TRADE_BUY && $this->stockPrice()<=$price){
					$comnum=$this->isbuyComStock($userid,$surplus,$account,$price,$uparray,false);
					$surplus-=$comnum;
				}
			}
			$order_m->where(array('id'=>$id))->save(array('tradeinfo'=>serialize($uparray),'num'=>$surplus,'num2'=>$num-$surplus));
		}
		
		public function isbuyComStock($userid,$surplus,$account,$price,&$uparray,$buy=true){
			$comnum=0;
			if($this->getatt('buyComStock')==$buy){
				//实际购买公司发行股数量
				$comnum=$this->buyComStock($surplus);
				if($comnum>0){			
					$comprice=$this->stockPrice();
					$commoney=$comnum*$comprice;
					
					$this->setrecord($userid,$comprice,$comnum,$account,"购买公司发行股".$comnum."股，".$comprice."/股",2);
					//更新公司发行量
					$this->upComStock($comnum);
					$this->uptrend(array('成交量'=>$comnum,'成交金额'=>$commoney));

					//返款
					if($price>$comprice){
						$trmoney=($price-$comprice)*$comnum;
						bankset($this->tradeBank,$userid,$trmoney,L('买入返款'),"自动买入比预期价格便宜的公司发行股返款".$trmoney);
					}

					$uparray[]=array(
						'name'=>'公司',
						'price'=>$comprice,
						'num'=>$comnum,
						'time'=>systemTime(),
						'money'=>$commoney
					);
				}
			}
			return $comnum;
		}
		//可以购买的公司发行股
		public function buyComStock($num)
		{
			$return=0;

            //取得总发行量
            $all=$this->getatt('stockAllNum');

			//当总发行量为0，即没有上限;
			if($all==0) return $num;

			//取得已认购量
			$havegive=$this->getatt('stockHasGive');
			
			//公司发行剩余可购买数量
			$left=$all-$havegive;

			//如果发行量已经满足
			if($left<=0) return $return;

            $return=( $left > $num ) ? $num : $left;
			return $return;	
		}

		 //增加股票交易明细
		public function setrecord($userid,$price,$num,$account,$memo,$type,$usersave=true)
		{
			$user_m=M('用户');
			$userinfo=$user_m->where("编号='".$userid."'")->find();
			$data=array();
			$data['编号']   =$userid;
			$data['price']  =$price;
			$data['num']    =$num;
			$data['addtime']=systemTime();
			$data['memo']   =$memo;
			$data['type']   =$type;
			$data['账户']   =$account;
			if($usersave){
				/*
				if($account==$this->name){
				   if($type==2){  //当为股票账户并且为增加时 判断是否超出了最大
					    $data['余额']   =$userinfo[$this->name]+$data['num'];
				        $max=$this->havemax($userinfo);//得到最大的上限
						$newnum=$userinfo[$this->name]+$num;  //得到理论应该的总数
						$update=array();
						$update[$account]=$newnum;
						if($newnum>$max && $max>0){   //判断是否超出了
							$left=$newnum-$max;  //多出进入托管账户的股券
							$update[$this->name."托管"]=array("exp",$this->name."托管+".$left);
							$update[$account]=$max;
							$data['num']     =$max-$userinfo[$this->name];
							$data['余额']   =$userinfo[$this->name]+$data['num'];
							$data1           =$data;
							$data1["num"]    =$left;
							$data1["账户"]   =$this->name."托管";
							$data1['余额']   =$userinfo[$data1["账户"]]+$left;
							$data1['memo'].=",".$account."超出最大限额".$max.",超出的".$left."部分自动转入".$this->name."托管账户";
							M($this->name."明细")->add($data1);
						}
						$user_m->where("编号='".$userid."'")->save($update);
				   }
				   if($type==1){
					   $data['余额']   =$userinfo[$account]-$data['num'];
					   $userupdata=array($account=>array('exp',$account.'-'.$data['num']));
				       $rs=$user_m->where("编号='".$userid."'")->save($userupdata);
				   }
				}else{*/
					$p=($type==1)?'-':'+';
					if($type==1){
					   $data['余额']   =$userinfo[$account]-$data['num'];
					}else{
						$data['余额']   =$userinfo[$account]+$data['num'];
					}
					$userupdata=array($account=>array('exp','`'.$account.'`'.$p.$data['num']));
					$rs=$user_m->where("编号='".$userid."'")->save($userupdata);
				//}
			}
			if($data['num']>0){
				M($this->name."明细")->add($data);
			}
		}
		
		//更新公司的已认购股数
		public function upComStock($num)
		{
			$oldnum=$this->getatt('stockHasGive');
			$newnum=$oldnum+$num;
			$this->setatt('stockHasGive',$newnum);
			$this->uptrend(array("认购量"=>$num));
			$this->upstockprice();
		}
		 //更新走势图 array('价格'=>,'认购量'=>,'成交量'=>,'成交金额'=>)
        public function uptrend($data)
		{
			$today=strtotime(date("Y-m-d",systemTime()));
			if(!empty($data)){
				$where=array();
				$where['计算日期']=$today;
				$todayinfo=M($this->name."走势")->where($where)->find();
				if(isset($data['认购量'])){
					$data['认购量']+=$todayinfo['认购量'];
				}
				if(isset($data['成交量'])){
					$data['成交量']+=$todayinfo['成交量'];
				}
				if(isset($data['成交金额'])){
					$data['成交金额']+=$todayinfo['成交金额'];
				}
				if($todayinfo){
					M($this->name."走势")->where($where)->save($data);
				}else{
					$data['计算日期']=$today;
					M($this->name."走势")->add($data);
				}
			}
		}
		
		//更新股票价格
		public function upstockprice()
		{
           $stockForm=$this->getatt('stockMoneyForm');
		   if($stockForm==""){
			   return;
			}
			$stockprice=$stockForm;
			$num=$this->getatt("stockTrade");
		   preg_match_all('/\[(.*)\]/U',$stockForm,$truevals,PREG_SET_ORDER);
			if(count($truevals)>0){
				foreach($truevals as $trueval)
				{
					if($trueval[1]=='股票原始价格'){
						$stockprice=str_replace($trueval[0],$this->getatt('stockStartMoney'),$stockprice);
						}
					if($trueval[1]=='股票交易总数'){
						$stockprice=str_replace($trueval[0],$num,$stockprice);
						}
					if($trueval[1]=='股票已认购量'){
						$stockprice=str_replace($trueval[0],$this->getatt('stockHasGive'),$stockprice);
						}
					if($trueval[1]=='股票拆分'){
						$stockprice=str_replace($trueval[0],$this->getatt('StockSplit'),$stockprice);
						}
				}
			}
			preg_match_all('/start\((.*),(.*)\)/U',$stockprice,$twovals,PREG_SET_ORDER);
			
			if(count($twovals)>0){
				foreach($twovals as $twoval)
				{
					if($twoval[1]>$twoval[2]){
					  $stockprice=str_replace($twoval[0],$twoval[1]-$twoval[2],$stockprice);
					}else{
						 $stockprice=str_replace($twoval[0],0,$stockprice);
					}
				}
			}
			eval('$stockprice='.$stockprice.';');
			$stockprice=sprintf("%01.".$this->priceLen."f",$stockprice);
			$this->setatt('stockNowPrice',$stockprice);
            $this->uptrend(array('价格'=>$stockprice));
			//$this->trendxml();
		    return;
		}
		public function setdetail($username,$sellid,$fromuser,$buyid,$num,$price,$account,$memo)
		{
			if($num>0){
				 $data=array(
					 '买入ID'=>$buyid,
					 '买入编号'=>$fromuser,
					 '卖出ID'=>$sellid,
					 '卖出编号'=>$username,
					 'num'=>$num,
					 'price'=>$price,
					 '账户'=>$account,
					 'addtime'=>systemTime(),
					 'memo'=>$memo,
					 );
				
				 $rs=M($this->name."交易")->add($data);
				 if($rs){
					 $oldtrade=$this->getatt("stockTrade");
					 $this->setatt("stockTrade",$oldtrade+$num);
					 $this->upstockprice();
				}
			}
			return;
		}

		//用户股票卖出后的操作
		/*
		$account 为卖出股票的账户
		$money   为卖出股票获得的金额
		*/
		public function selldo($userid,$buyid,$account,$money,$sellid='')
		{
			if($money<=0) return;
			$cons=$this->getcon("selldo",array('mode'=>'','val'=>'','from'=>'','bank'=>''),true);
			if(empty($cons))  return;

			//卖出用户信息
			$user=M('用户')->where(array('编号'=>$userid))->find();
			$tax=0;
			$allmoney=$money;
			$leftmoney=$money;
			foreach($cons as $con)
			{
				
				//将$con['val']转换为实际的操作金额
				$thismoney=$this->getnum($allmoney,$leftmoney,$con['val'],$con["from"]);
                //当mode为tax 即为手续费时
				if($con['mode']=='tax'){
					 $leftmoney-=$thismoney;
					 $tax+=$thismoney;
				}
				//进入账户
				if($con['mode']=='Inbank'){
					if($thismoney>0){
					   bankset($con['bank'],$userid,$thismoney,$con['bankmode'],$con['bankmemo'].",交易单号".$sellid.",购买人".$buyid);
					}
				}
			}		
 		}

		//撤销交易市场中的未完成的所有单
	public function cancelall()
	{
		$stock_model=M($this->name."市场");
		$where=array();
		$where['num']=array('gt',0);
		$where['state']=array('eq',0);
		$all=$stock_model->where($where)->select();
		if(empty($all)) return;
		$idarr=array();
		foreach($all as $list)
		{
			$idarr[]=$list['id'];
			$memo="公司撤销所有市场中的订单";
			if($list['type']==1){
                $this->setrecord($list['编号'],$list['price'],$list['num'],$list['账户'],$memo,2);
			}
			if($list['type']==2){
				$money=$list['price']*$list['num'];
				if($money>0){
					  bankset($this->tradeBank,$list['编号'],$money,$this->name."撤单",$memo);
				}
			}
		}
		if(empty($idarr)) return;
			$update=array();
			$update['state']=1;
			$upwhere=array();
			$upwhere['id']=array('in',$idarr);
            $rs=$stock_model->where($upwhere)->save($update);
	 }

	 //股票拆骨
	 public function splitstock($num)
		{
		   if($num==1) return;
           $user_model=M('用户');
		   $where=array();
		   $where[$this->name]=array('gt',0);
           $list=$user_model->where($where)->select();
		   //$rs=$user_model->where($where)->save($update);
		   if(empty($list)) return;
		   foreach($list as $info)
			{
			    $update=array();
			    $newstock=abs($info[$this->name]*($num-1));

				$memo="公司拆股,".$num."倍";
				$type=($num>1)?2:1;
				$this->setrecord($info['编号'],$this->getatt('stockNowPrice'),$newstock,$this->name,$memo,$type);
				/*$userinfo=$info;
				$userinfo[$this->name]=$newstock;
			    $max=$this->havemax($userinfo);
				if($newstock>$max && $max>0){
                    $update[$this->name]=$max;
					$truestock=$max;
					$left=$newstock-$max;
					$update[$this->name."托管"]=$left;
				}else{
					$update[$this->name]=$newstock;
					$truestock=$newstock;
				}
				$changenum=$truestock-$info[$this->name];
				$type=($changenum>0)?2:1;
				$memo="公司拆股,".$num."倍";
				$this->setrecord($info['编号'],$this->getatt('stockNowPrice'),abs($changenum),$this->name,$memo,$type);
				//托管账户
				if(isset($update[$this->name."托管"]) && $update[$this->name."托管"]>0){
				$this->setrecord($info['编号'],$this->getatt('stockNowPrice'),$update[$this->name."托管"],$this->name."托管",$memo,2);
				}*/
			}
		}
		
		//股票拆分后更新的配置
		public function upconf($num)
		{
			// $old=$this->getatt("StockSplit");
			 $publish=$this->getatt("stockAllNum");
			 $this->setatt("StockSplit",$num);
			 if($publish>0){
			  $this->setatt("stockAllNum",$publish*$num);
			 }
		}

		public function event_sysclear()
		{
            $model=M();
			$model->execute('truncate table `dms_'.$this->name.'市场`');
			$model->execute('truncate table `dms_'.$this->name.'明细'.'`');
			$model->execute('truncate table `dms_'.$this->name.'交易'.'`');
			$model->execute('truncate table `dms_'.$this->name.'拆股'.'`');
			$model->execute('truncate table `dms_'.$this->name.'走势'.'`');
		}
		public function event_valadd($user,$val,$option)	
		{
			//当前剩余金额  这里的$val是应该购买股票的金钱数
			$buycons=$this->getcon("addbuy",array("val"=>'',"mode"=>'','from'=>'','type'=>''),true);
			$option["memo"]=str_replace('$val',$val,$option["memo"]);
			$allval=$val; //总的金额
			$leftval=$val;//剩余金额 每次循环都有可能减少
			foreach($buycons as $buycon)
			{
			  $buycon["memo"]=$option["memo"];
				//当前应该购买的股票的金额
			  $t_val=$this->getbuynum($allval,$leftval,$buycon['val'],$buycon['from']);
			  $leftval-=$t_val;//剩余的用户购买股票的金额
				switch($buycon['mode'])
				{
				case "publish":
					$leftval+=$this->buyPublish($user,$t_val,$buycon);
					//购买上级的托管股，此买入直接判定上级托管股数量，直接成交----
				break;
				case "uptrust":
					$leftval+=$this->buyUptrust($user,$t_val,$buycon);
				//购买挂单，按照当前价格挂买入单，走成交流程-----
				break;
				case "usersell":
					$leftval+=$this->buyUsersell($user,$t_val,$buycon);
				break;
				}
			}
			if($leftval>0){
				$this->parent()->get("./fun_bank[@name='".$this->tradeBank."']")->set($user['编号'],$user['编号'],$leftval,$this->name."注册返款","注册配送股票剩余".$leftval);
			}
		}
		 //购买netname对应的上级网体的
		public function buyUptrust($user,$val,$option)
		{
			$net=$this->parent()->get("./*[@name='".$option['netname']."']");
			//得到上级
			$upuserid=$user[$net->name."_上级编号"];
			//当上级为空则返回
			if($upuserid==''||$upuserid==NULL) return $val;
			$uptrustuser=M('用户')->where(array('编号'=>$upuserid))->find();
			//若托管账户的股券数量为0 返回
			if($uptrustuser[$this->name.'托管'] <= 0) return $val;

			//取得当前金额的小数取整
			//$val=round($val,$this->decimalLen);

			//取得当前股票价格
			$price=$this->stockPrice();

			if($val<$price) return $val;

			//计算当前进入的金额val可以买多少股票得到num
			$num=floor($val/$price);

            //得到推荐人托管账户可购买的数量
			$locktop=true;
			if(isset($option['locktop']) && $option['locktop']=="false"){
				$locktop=false;
			}
			$truenum=$this->getTrustNum($uptrustuser,$num,$locktop);
            if($truenum==0) return $val;
		    $leftval=$val-$truenum*$price;
			$thismoney=$truenum*$price;
           
		    //增加股票配股明细以及配股操作
		    $memo=$option["memo"].",".$user['编号']."购买".$thismoney."元上级".$net->name.$upuserid.$this->parent()->name.$truenum."股，每股".$price."元";
			$this->setrecord($user['编号'],$price,$truenum,$this->name,$memo,2);
			$this->setrecord($upuserid,$price,$truenum,$this->name."托管",$memo,1);
			$this->uptrend(array('成交量'=>$truenum,'成交金额'=>$thismoney));
			//判断是否增加托管本月累计
			$intop=true;
			if(isset($option['intop']) && $option['intop']=="false"){
               $intop=false;
			}
            $this->savetrustall($upuserid,$truenum,$intop);
			//卖出后执行selldo
			$this->selldo($upuserid,$user['编号'],$this->name.'托管',$thismoney);
			
			return $leftval;
		}
		//购买用户卖出股票
		public function buyUsersell($user,$val,$option)
		{
			//取得当前金额的小数取整
			//$val=round($val,$this->decimalLen);
            if($val<=0) return $val;

			//取得当前股票价格
			$price=$this->stockPrice();
           
			$order_m=M($this->name."市场");
			
            
            $where=array(
				'price'=>array('elt',$price),
				'type'=>array('eq', 1),
				'编号'=>array('neq',$user['编号']),
				'num'=>array('gt',0),
				'state'=>array('eq',0),
				);
			$account="";
            if($option['type']=="trade") $account=$this->name;
			if($option['type']=="trust") $account=$this->name."托管";
			if($account!="") $where["账户"]=array("eq",$account);
			$leftval=$this->buyProcess($user['编号'],$where,$val,$this->name,$option);
			//执行三进三出
            $this->buydeal();
			return $leftval;
		}
		//购买公司发行
		public function buyPublish($user,$val,$option=array())
		{
			//取得当前金额的小数取整
			//$val=round($val,$this->decimalLen);


			//取得当前股票价格
			$price=$this->stockPrice();

			if($val<$price) return $val;

			//计算当前进入的金额val可以买多少股票得到num
			$num=floor($val/$price);
			//得到实际购买的公司发行股
			$truenum=$this->buyComStock($num);
            if($truenum==0) return $val;
			//判断购买股票剩余的钱
			$leftval=$val-$truenum*$price;

			//增加股票配股明细以及配股操作
			$this->setrecord($user['编号'],$price,$truenum,$this->name,$option["memo"]."，购买公司发行股".$truenum."股",2);

			//更新最新发行量为:$newhasgive
			$this->upComStock($truenum);
			return $leftval;
		}
		
        //判段用户托管账户可交易金额
		public function getTrustNum($userinfo,$num,$locktop=true)
		{
			//当交易的数量小于等于0时 返回0
		    if($num<=0) return 0;
			//根据用户账户的数量判断实际数量
			if($userinfo[$this->name.'托管']>$num){
				$truenum=$num;
			}else{
				$truenum=$userinfo[$this->name.'托管'];
			}	
			if(!$locktop) return $truenum;
			//得到最大出售比例
			$maxrate=$this->getatt("stockTrustMax");
			//当比例为0为返回0
			if($maxrate==0) return 0;
            
			//得到股票托管_当月金额
			$monthmoney=$userinfo[$this->name."托管_当月账户"];
			if($monthmoney==0) return 0;

			if($truenum>$monthmoney) $truenum=$monthmoney;
			//当比例为100 返回
			if($maxrate==100) return $truenum;
		    /*$allsell=$userinfo[$this->name."托管_本月累计"]+$truenum;
			$allnum=$userinfo[$this->name."托管"]+$userinfo[$this->name."托管_本月累计"];
			if($allsell>$allnum*$maxrate/100){
				$truenum=(($userinfo[$this->name."托管"]+$userinfo[$this->name."托管_本月累计"])*$maxrate/100-$userinfo[$this->name."托管_本月累计"]);
			}*/
			$maxnum=$monthmoney*$maxrate/100;
			//得到本月挂单的数量
			$today=systemTime();
			$starttime=strtotime(date("Y-m",$today));//本月开始
			$daynum=intval(date("t",$starttime));
			$endtime=$starttime+$daynum*86400;//本月开始
			$has=M($this->name."市场")->where("编号='".$userinfo['编号']."' AND 账户='".$this->name."托管' AND addtime>=".$starttime." AND addtime<".$endtime." AND state!=1")->sum("num1"); //本月未撤销的全部挂单
			$has1=M($this->name."市场")->where("编号='".$userinfo['编号']."' AND 账户='".$this->name."托管' AND addtime>=".$starttime." AND addtime<".$endtime." AND state==1")->sum("num2");//本月撤销的已卖出的
			$has=($has==null)?0:$has;
			$has1=($has1==null)?0:$has1;
			$allhas=$has+$has1;
			if($allhas>=$maxnum) return 0;

			$allhas1=$allhas+$truenum;
			if($allhas1<=$maxnum){
				$return=$truenum;
			}else{
				$return=$maxnum-$allhas;
			}
			return $truenum;
		}
		public function upalltruest($userid,$num)
		{
           M('用户')->where(array("编号"=>$userid))->save(array($this->name."托管_本月累计"=>array("exp",$this->name."托管_本月累计+".$num)));
		}
		//对xml中的$val转换成对应金额
	    public function getbuynum($allval,$leftval,$xmlval,$from)
	  	{
		if($from=="all") $val=$allval;
		if($from=="surplus" || $from=="") $val=$leftval;
		if(strstr($xmlval,'%')){
			$num = $val * substr($xmlval,0,-1) * 0.01; 
		}elseif($xmlval==''){
			$num = $val; 
		}else{
			$num = $xmlval;
		}
		return $num;
		}
        public function setrecord1($userid,$price,$num,$type,$usersave=true){
			if($type==0) {
				$newtype=1; 
				$account=$this->name;
				}
			if($type==1) {
				$newtype=2;
				$account=$this->name;
				}
			if($type==2) {
				$newtype=1;
				$account=$this->name."托管";
				}
			$newmemo=$this->parent()->name.$userid.$this->type[$type].$this->name.$num."股";
			$this->setrecord($userid,$price,$num,$account,$newmemo,$newtype,$usersave);
		}

		//更新股票交易明细
       
       //买入操作产生的三进三出处理
		public function buydeal()
		{
			//print_r($this->cashmoney);
			//购买股票账户$this->name挂单卖出的股票
			while(!empty($this->cashmoney)){
							//var_dump($this->cashmoney);die;
				//三进三处购买的账户
				$buyaccount=$this->name;
			    $first=array_shift($this->cashmoney);
				foreach($first as $name=>$info){
                   $tradeaccount=$name; //产生三进三出的股票
				   $stockinaccount=$info["getaccount"];//三进三出购买股票进入的账户
				   $leftinaccount=$info["leftin"];//三进三出购买后剩余的钱 进入的账户
				   $userid=$info['name'];
				   $money=$info['money'];
				   $this->dealbuy($buyaccount,$tradeaccount,$stockinaccount,$leftinaccount,$userid,$money);
				}
			}
			while(!empty($this->trademoney)){
			    $first=array_shift($this->trademoney);
				foreach($first as $inbank=>$info1){
				    $userid=$info1['name'];
				    $money=$info1['money'];
				    $this->parent()->get("./fun_bank[@name='".$inbank."']")->set($userid,$userid,$money,$this->name."三进三出买入返款","三进三出购买剩余".$money);
				}
			}
		}

  
		//三进三出购买
		public function dealbuy($buyaccount,$tradeaccount,$stockinaccount,$leftinaccount,$userid,$money)
		{
			if($money<=0) return;
			$order_m=M($this->name."市场");
			$where=array(
				'type'=>array('eq',1),
				'编号'=>array('neq',$userid),
				'num'=>array('gt',0),
				);
			if($buyaccount!=""){
				$where["账户"]=array("eq",$buyaccount);
			}
             if($stockinaccount=="") $stockinaccount=$tradeaccount;
			 $leftmoney=$this->buyProcess($userid,$where,$money,$stockinaccount);
			 $user=array();
			 $user["编号"]=$userid;
		     $leftmoney1=$this->buyPublish($user,$leftmoney);
		 if($leftmoney1>0){
			 if(!array_key_exists($userid,$this->trademoney[$leftinaccount])){
						$this->trademoney[$userid][$leftinaccount]=array('name'=>$userid,'money'=>0);
					}
			 $this->trademoney[$userid][$leftinaccount]['money']+=$leftmoney;
		 }
		}
        /*
		更新托管账户累记
		*/

		public function savetrustall($userid,$num,$intop=true)
		{
          if(!$intop || $num==0) return;
		  M('用户')->where(array("编号"=>$userid))->save(array($this->name."托管_本月累计"=>array("exp",$this->name."托管_本月累计+".$num)));
		  return;
		}
        /*
		** $buywhere 购买股票条件
		** $buymoney 购买股票的金额
		** $inaacount购买的股票进入的 账户若为空则默认为$this->name
		*/
		//购买流程
		public function buyProcess($userid,$buywhere,$buymoney,$inaccount,$option=array())
		{
           $order_m=M($this->name."市场");
		   //得到可购买的记录
		   //记录时优先购买单价便宜的  其次是挂单时间早的
		   $lists=$order_m->order("price asc,addtime asc")
						   ->where($buywhere)
						   ->select();
		   //当没找到记录时 返回
		   if(empty($lists)) return $buymoney;
		   //定义剩余可购买股票的金额
		   $leftmoney=$buymoney;
		  
           //循环记录
		   foreach($lists as $list)
			{
			    //判断用于购买股票的钱是否大于最便宜的一股的钱 若小于直接跳出循环
				if($leftmoney<$thisprice) break;
			    $sellid=$list['id'];
				//当前股券每股价格基准
				$thisprice=$list['price'];

                //得到当前用户的股券交易最大量
				$maxnum = floor($leftmoney/$thisprice);
				//当前实际购买的股票数
                $thisnum=( $maxnum > $list['num'] ) ? $list['num'] : $maxnum;
                //当前用户的交易额
				$thismoney=$thisprice * $thisnum;
                //购买用户
				$fromuser=$userid;
				//卖出用户
                $username=$list['编号'];
				$tranaccount=$list['账户'];
				//更新当前卖出订单的信息
				$tradeinfo=unserialize($list['tradeinfo']);
				$tradeinfo[]=array(
					'name'=>$userid,
					'price'=>$thisprice,
					'num'=>$thisnum,
					'time'=>systemTime(),
					'money'=>$thismoney
					);
                $update=array(
					'num'=>array('exp','num-'.$thisnum),
					'num2'=>array('exp','num2+'.$thisnum),
					'tradeinfo'=>serialize($tradeinfo)
					);
				$order_m->where(array('id'=>$list['id']))->save($update);

				$memo=$option['memo'].$this->parent()->byname.$fromuser."花费买入".$thismoney."元".$username.$tranaccount."卖出股票的".$thisnum."股,每股".$thisprice."元";
                 //当前list用户卖出股票后的操作
				$this->selldo($username,$fromuser,$tranaccount,$thismoney);
				// 并增加一个股票账户交易明细
				$this->setdetail($username,$sellid,$fromuser,$buyid,$thisnum,$thisprice,$tranaccount,$memo);
               //当前userid的账户增加股票
			    if($tranaccount==$this->name."托管")
				{
					 $this->savetrustall($username,$thisnum);
				}
				$this->setrecord($fromuser,$thisprice,$thisnum,$inaccount,$memo,2);

				$this->uptrend(array('成交量'=>$thisnum,'成交金额'=>$thismoney));
				 //剩余的钱
				$leftmoney -= $thismoney;	
			}
			return $leftmoney;
           
		}
	

		
		//生成走势图的xml文件
       public function trendxml()
		{
		   $model=M($this->name."走势");
		   $xml='<?xml version="1.0" encoding="utf-8"?> 
		         <root>';
		   $beishu=100;
		   $list=$model->order("计算日期 desc")->limit("31")->select();
		   $lastday=$list[0]['计算日期'];
		   $data=array();
			   for($i=31;$i--;$i>=0){
				   $abc=$lastday-$i*86400;
				   $data[$abc]=array(
					   'price'=>0,
					   'bf'=>0
					   );
			   }
			 $maxprice=0;
			 $minprice=0;
		   foreach($list as $val)
			{
			   if(array_key_exists($val['计算日期'],$data)){
				   if($val['价格']>$maxprice) $maxprice=$val['价格'];
                   if($val['价格']<$minprice) $minprice=$val['价格'];
				   $data[$val['计算日期']]=array(
					   'price'=>$val['价格'],
					   'bf'=>$val['价格']);
			   }
			}
			if($minprice==0 && $maxprice==0){
				$config1='<config>
						<minval value="0.0"/>
						<maxval value="0.1"/>
						<minval value="0.2"/>
						<minval value="0.3"/>
						<minval value="0.4"/>
						<minval value="0.5"/>
						<minval value="0.6"/>
						<minval value="0.7"/>
						<minval value="0.8"/>
						<minval value="0.9"/>
						<minval value="1.0"/>
                  </config>';
			}
			if($maxprice!=0){
				$eve=$maxprice/10;
               $config1='<config><minval value="0.000"/>';
			   for($i=1;$i<=10;$i++)
				{
                $config1.='<minval value="'.sprintf ("%.3f ",$eve*$i).'"/>';$beishu=Ceil(100/$maxprice);
				}
				$config1.='</config>';
				
			}
			
			$config2='<config>';
			$datas='<datas>';
			foreach($data as $key=>$eve)
			{
				$off=$lastday-$key;
				if($off==0 || ($off/86400)%6==0){
               $config2.='<days day="'.date("Y-m-d",$key).'"/>';
				}
			   $datas.='<data val="'.sprintf ("%.3f ",$eve['price']).'" bf="'.$eve['bf']*$beishu.'" />';
			}

		   $datas.='</datas>';
		   $config2.='</config>';
		   $xml.=$datas.$config1.$config2.'</root>';
		   file_put_contents(ROOT_PATH.APP_NAME."/Services.xml",$xml);
		}

	
			//对xml中的$val转换成对应金额
	    public function getnum($allval,$leftval,$xmlval,$from)
	  	{
			if($from=="all") $val=$allval;
			if($from=="surplus" || $from=="") $val=$leftval;
			if(strstr($xmlval,'%')){
				$num = $val * substr($xmlval,0,-1) * 0.01; 
			}elseif($xmlval==''){
				$num = $val; 
			}else{
				$num = $xmlval;
			}
			return $num;
		}

		//判断数量
		public function judgeNum($num,$user,$name)
		{
			if($user[$name]<$num)
			{
				return false;
			}
			return true;
		}
		//判断返回股票账户存在上限
		 public function  havemax($userinfo)
		{ 
			  $cons=$this->getcon('havemax',array("val"=>0,"where"=>""));
			  if(empty($cons)) return 0;
			  $where=array();
			  $return=0;
			  foreach($cons as $con)
			  {
					if(transform($con['where'],$userinfo)){
						 $return=$con['val'];
						 break;
					  }
			  }
			  return $return;
		}
		public function event_modifyId($oldbh,$newbh)
		{ 
			M()->execute("update dms_" . $this->name . "交易 set 买入编号='{$newbh}' where 买入编号='{$oldbh}'");
			M()->execute("update dms_" . $this->name . "交易 set 卖出编号='{$newbh}' where 卖出编号='{$oldbh}'");
			M()->execute("update dms_" . $this->name . "明细 set 编号='{$newbh}' where 编号='{$oldbh}'");
			M()->execute("update dms_" . $this->name . "市场 set 编号='{$newbh}' where 编号='{$oldbh}'");
		}
		/*
		foreach(X('fun_stock') as $fun_stock)
		{
			//股票起始价格
            $tlearray['stockStartMoney:'.$fun_stock->Path()]=array(
				'name'=>'stockStartMoney:'.$fun_stock->Path(),
			    'data'=>$fun_stock->stockStartMoney,
			    'app'=>$this->APP,
			);
			//股票价格计算公式
			$tlearray['stockMoneyForm:'.$fun_stock->Path()]=array(
			     'name'=>'stockMoneyForm:'.$fun_stock->Path(),
			     'data'=>$fun_stock->stockMoneyForm,
			     'app'=>$this->APP,
			);
			//股票交易最低整数倍
			$tlearray['stockMinint:'.$fun_stock->Path()]=array(
			 'name'=>'stockMinint:'.$fun_stock->Path(),
			 'data'=>$fun_stock->stockMinint,
			 'app'=>$this->APP,
			);
			//股票休市
			$tlearray['stockClose:'.$fun_stock->Path()]=array(
			 'name'=>'stockClose:'.$fun_stock->Path(),
			 'data'=>$fun_stock->stockClose,
			 'app'=>$this->APP,
			);
			//股票价格小数位
			$tlearray['decimalLen:'.$fun_stock->Path()]=array(
			 'name'=>'decimalLen:'.$fun_stock->Path(),
			 'data'=>$fun_stock->decimalLen,
			 'app'=>$this->APP,
			);
			//股票出售手续费
			$tlearray['stockOutTax:'.$fun_stock->Path()]=array(
			 'name'=>'stockOutTax:'.$fun_stock->Path(),
			 'data'=>$fun_stock->stockOutTax,
			 'app'=>$this->APP,
			);
			//股票购买手续费
			$tlearray['stockBuyTax:'.$fun_stock->Path()]=array(
			 'name'=>'stockBuyTax:'.$fun_stock->Path(),
			 'data'=>$fun_stock->stockBuyTax,
			 'app'=>$this->APP,
			);
			//开启公司大盘
			$tlearray['startComGrail:'.$fun_stock->Path()]=array(
			 'name'=>'startComGrail:'.$fun_stock->Path(),
			 'data'=>true,
			 'app'=>$this->APP,
			);
			//股票买入是否购买公司发行
			$tlearray['buyComStock:'.$fun_stock->Path()]=array(
			 'name'=>'buyComStock:'.$fun_stock->Path(),
			 'data'=>true,
			 'app'=>$this->APP,
			);
			//股票总发行量
			$tlearray['stockAllNum:'.$fun_stock->Path()]=array(
			 'name'=>'stockAllNum:'.$fun_stock->Path(),
			 'data'=>0,
			 'app'=>$this->APP,
			);
			//股票已认购量
			$tlearray['stockHasGive:'.$fun_stock->Path()]=array(
			 'name'=>'stockHasGive:'.$fun_stock->Path(),
			 'data'=>0,
			 'app'=>$this->APP,
			);
			//股票三进三出
			//开启三进三出
			$tlearray['startStockAutoBuy:'.$fun_stock->Path()]=array(
			 'name'=>'startStockAutoBuy:'.$fun_stock->Path(),
			 'data'=>false,
			 'app'=>$this->APP,
			);
			//卖出收入的百分比进行购买
			$tlearray['stockAutoBuyRate:'.$fun_stock->Path()]=array(
			 'name'=>'stockAutoBuyRate:'.$fun_stock->Path(),
			 'data'=>30,
			 'app'=>$this->APP,
			);
			//用于三进三出的钱是否自动购买
			$tlearray['stockAutoBuy:'.$fun_stock->Path()]=array(
			 'name'=>'stockAutoBuy:'.$fun_stock->Path(),
			 'data'=>true,
			 'app'=>$this->APP,
			);
			//托管账户的每月固定限卖 
			$tlearray['stockTrustMax:'.$fun_stock->Path()]=array(
			 'name'=>'stockTrustMax:'.$fun_stock->Path(),
			 'data'=>100,
			 'app'=>$this->APP,
			);
			//托管账户三进三出开关
			$tlearray['TrustAuto:'.$fun_stock->Path()]=array(
			 'name'=>'TrustAuto:'.$fun_stock->Path(),
			 'data'=>false,
			 'app'=>$this->APP,
			);
			//股票现有价格
			$tlearray['stockNowPrice:'.$fun_stock->Path()]=array(
			 'name'=>'stockNowPrice:'.$fun_stock->Path(),
			 'data'=>$fun_stock->stockStartMoney,
			 'app'=>$this->APP,
			);
			//股票交易总数
			$tlearray['stockTrade:'.$fun_stock->Path()]=array(
			 'name'=>'stockTrade:'.$fun_stock->Path(),
			 'data'=>0,
			 'app'=>$this->APP,
			);
			//股票拆分倍数
			$tlearray['StockSplit:'.$fun_stock->Path()]=array(
			 'name'=>'StockSplit:'.$fun_stock->Path(),
			 'data'=>1,
			 'app'=>$this->APP,
			);
			//股票开盘价格
			$tlearray['StockOpening:'.$fun_stock->Path()]=array(
			 'name'=>'StockOpening:'.$fun_stock->Path(),
			 'data'=>0.1,
			 'app'=>$this->APP,
			);

			$filearray['StockOpening:'.$fun_stock->Path()] =0.1;
			$filearray['StockSplit:'.$fun_stock->Path()] =1;
			$filearray['stockTrade:'.$fun_stock->Path()] =0;
			$filearray['stockNowPrice:'.$fun_stock->Path()] =$fun_stock->stockStartMoney;
			$filearray['TrustAuto:'.$fun_stock->Path()] =false;
			$filearray['stockTrustMax:'.$fun_stock->Path()] =100;
			$filearray['buyComStock:'.$fun_stock->Path()] =true;
			$filearray['startComGrail:'.$fun_stock->Path()] =true;
			$filearray['startStockAutoBuy:'.$fun_stock->Path()] =false;
			$filearray['stockAutoBuy:'.$fun_stock->Path()] =true;
			$filearray['stockAutoBuyRate:'.$fun_stock->Path()] =30;
			$filearray['stockHasGive:'.$fun_stock->Path()] =0;
			$filearray['stockAllNum:'.$fun_stock->Path()] =0;
			$filearray['stockStartMoney:'.$fun_stock->Path()] =$fun_stock->stockStartMoney;
			$filearray['stockMoneyForm:'.$fun_stock->Path()] =$fun_stock->stockMoneyForm;
			$filearray['stockMinint:'.$fun_stock->Path()] =$fun_stock->stockMinint;
			$filearray['stockClose:'.$fun_stock->Path()] =$fun_stock->stockClose;
			$filearray['decimalLen:'.$fun_stock->Path()] =$fun_stock->decimalLen;
			$filearray['stockOutTax:'.$fun_stock->Path()] =$fun_stock->stockOutTax;
			$filearray['stockBuyTax:'.$fun_stock->Path()] =$fun_stock->stockBuyTax;
		}		
		*/
	}
?>