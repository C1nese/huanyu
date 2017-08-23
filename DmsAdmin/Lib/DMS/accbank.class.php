<?php
	/*电子货币模块*/
	class accbank extends stru
	{
		//扣款流程
		public function accok(&$sale,$data=array(),$user,$saleobj,$adminacc=false){
			//直接生成的订单
			if(!isset($sale['id']) && ($sale['报单状态']=='未确认' || $sale['报单状态']=='空单' || $sale['报单状态']=='回填'))
				return true;
			//审核的订单
			if(isset($sale['id']) && $sale['报单状态']!='未确认')
				return true;
			//后台审核并且不扣款的
			if($adminacc && !$saleobj->adminAccDeduct) return true;
			//不是后台审核，则使用当前登入用户（这里前台审核单会列出当前登录用户所能审核的订单
            if($sale['付款人编号']!="")$user[$saleobj->accstr]=$sale['付款人编号'];
			if(!$adminacc && $saleobj->user!='admin'){
				//$accuser = M('货币')->where(array("编号"=>$_SESSION[C('USER_AUTH_NUM')]))->find();//货币分离
				$accuser = M('货币')->where(array("编号"=>$user[$saleobj->accstr]))->find();//扣除节点中accstr的钱包
			}else{
				$accuser = M('货币')->where(array("编号"=>$user[$saleobj->accstr]))->find();//货币分离
			}
			//实际支付金额
			$wuliu=isset($sale['物流费'])?$sale['物流费']:0;
			$paymoney = $sale['实付款']+$wuliu;
			$lastmoney=$paymoney;
			//获取订单中的配置信息
			!isset($sale['accbank']) && $sale['accbank']='';
			if($sale['accbank']!=""){
				$bankcons=json_decode($sale['accbank'],true);
			}else{
				$bankcons=$this->getcon("bank",array("name"=>"","minval"=>"0%","maxval"=>'100%'),true);
				$sale["accbank"]=json_encode($bankcons);
			}
			foreach($bankcons as $bankcon){
				$minmoney=$paymoney*$bankcon['minval']/100;
				$maxmoney=$paymoney*$bankcon['maxval']/100;
				//判断钱包是否余额充足
				if((string)$minmoney >(string)$accuser[$bankcon['name']] && (string)$lastmoney>(string)$accuser[$bankcon['name']]){
					return L('用户编号为'.$accuser['编号'].'的用户'.$bankcon['name'].'余额不足');
				}else{
					//判断钱包可支付的最大金额
					if((string)$accuser[$bankcon['name']]<(string)$maxmoney){
						$maxmoney=$accuser[$bankcon['name']];
					}
					if((string)$lastmoney<(string)$maxmoney){
						$maxmoney=$lastmoney;
					}
					$res = X("fun_bank@".$bankcon['name'])->set($accuser['编号'],$sale['编号'],-$maxmoney,$saleobj->byname,X('user')->byname.'['.$sale['编号'].']'.$saleobj->byname.'花费'.$maxmoney);
					// if(is_numeric($res)){//btx增加返回结果判断
					//     return $res;
			  //                   }
			                    $lastmoney-=$maxmoney;
				}
			}
			$sale['付款人编号']  = $accuser['编号'];
			return true;
		}
		//生成json扣款数据
		public function makejson($setbank){
			$bankary=array();
			$bankcons=$this->getcon("bank",array("name"=>"","minval"=>"0%","maxval"=>'100%'),true);
			if(!isset($setbank) || $setbank==""){
				$bankjson=json_encode($bankcons);
			}else{
				foreach($bankcons as $bankcon){
					if(isset($setbank[$bankcon['name']])){
						$bankcon['minval']=$setbank[$bankcon['name']];
						$bankcon['maxval']=$setbank[$bankcon['name']];
						$bankary[]=$bankcon;
					}
				}
				$bankjson=json_encode($bankary);
			}
			return $bankjson;
		}
	}
?>