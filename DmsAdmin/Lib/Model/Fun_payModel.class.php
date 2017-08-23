<?php
// 支付结果模型
class Fun_payModel 
{
	/*
	* 支付成功
	* orderId
	*/
private $config=array();
	public function _initialize(){
		
	}
	public function success($orderId,$args)
	{
		if(empty($this->config)){
			$this->config=require ROOT_PATH.'DmsAdmin/Conf/config.php';
		}
		$PayOrder	= M();
		$info		= $PayOrder->table("pay_order")->lock(true)->where(array('orderId'=>$orderId))->find();
			
			//$user=M()->table('dms_用户');
			$user=M()->table('dms_货币');
		    $re = $user->where(array("编号"=>$args['userid']))->lock(true)->find();
			if($re){
				//$memo= $args['userid'].'通过'.$args['payment'].'成功充值 '.$info['realmoney'].$args['type'];
                $memo= $args['userid'].'通过在线支付成功充值 '.$info['realmoney'].$args['type'];
				$PayOrder->table("pay_order")->where(array('orderId'=>$orderId))->save(array('memo'=>$memo));
				$data=array();
				$data['余额'] = floatval($re[$args['type']]) + floatval($info['realmoney']);
				//$res=M()->table('dms_用户')->where(array("编号"=>$args['userid']))->save(array($args['type']=>$data['余额']));
				$res=M()->table('dms_货币')->where(array("编号"=>$args['userid']))->save(array($args['type']=>$data['余额']));
                /*btx 同步货币 start*/
                if($res && $args['type'] == '换购钱包'){
                    $aPostData['username'] = $args['userid'];
                    $aPostData['num'] = $info['realmoney'];
                    $aPostData['note'] = $args['userid'].'在环宇天下通过在线支付成功充值 '.$info['realmoney'].$args['type'];
                    $aPostData['operate'] = '在线充值';
                    $aPostData['type'] = 1;
                    if(isset($aPostData['type'])){
                        $res = cCurlInit('http://mall.mzooe.com/phone/index.php?act=connect_hytx&op=sync_money',$aPostData);
                    }
                }
                /*btx 同步货币 end*/
				$data['备注'] = $memo;	
				$data['来源'] = $args['userid'];
				$data['时间'] = time();
				$data['编号'] = $args['userid'];
				$data['类型'] = '在线支付';
				$data['金额'] = $info['realmoney'];
				$bank =M()->table('dms_'.$args['type'].'明细');
				$bank->add($data);
				$updata=array();
				$updata['订单号']=$orderId;
				$updata['编号']=$args['userid'];
				$updata['金额']=$info['realmoney'];
				$updata['支付方式']=$args['payment'];
				$updata['支付时间']= time();
				$updata['备注']=$memo;
				$updata['状态']=1;
				M()->table('dms_onlinepay')->add($updata);
                //修改用户的到帐金额
	                if($args['paytypes']){
		                //读取接口表中的pay_type
				        $arr = M('pay_onlineaccount',' ')->lock(true)->where(array('pay_type'=>$args['paytypes']))->order("pay_amount asc,id desc")->find();
				        $amount = $arr['pay_amount']+$info['realmoney'];
			           	 M('pay_onlineaccount',' ')->where(array('id'=>$arr))->save(array('pay_amount'=>$amount));
		           	}
				}
		
	}

	//支付失败
	public function fail($orderId,$args)
	{
		$PayOrder	= M();
		$info		= $PayOrder->table("pay_order")->lock(true)->where("orderId='$orderId'")->find();
		if( $info['status']==0 )
		{
			$where['orderId']	= $orderId;
			$data['memo']		= $args['userid'].'通过'.$args['payment'].' 充值 '.$info['realmoney'].$args['type'].'失败';
			$PayOrder->table("pay_order")->where($where)->save($data);
			$updata=array();
				$updata['订单号']=$orderId;
				$updata['编号']=$args['userid'];
				$updata['金额']=$info['realmoney'];
				$updata['支付方式']=$args['payment'];
				$updata['支付时间']=time();
				$updata['备注']=$data['memo'];
				$updata['状态']=2;
				M()->table($this->config['DB_PREFIX'].'onlinepay')->add($updata);
		}
	}
}
?>