<?php
/*
* 支付测试模块
*/
class PayTestAction extends CommonAction 
{
	public function index()
	{
		//打印出当前已安装的支付接口
		import("COM.Pay.Pay");
		$payList = Pay::getPayList();

		//dump($payList);
		$this->assign('list',$payList);
		$this->display();
	}

	/**
	* 支付确认
	*/
	public function pay_confirm()
	{
		if( $_POST['money'] == '' || floatval($_POST['money']) <= 0 )
		{
			$this->error("请输入支付金额!");
		}
		
		if( $_POST['payment'] == '' )
		{
			$this->error("请选择支付方式");
		}

		$payment	= $_REQUEST['payment'];


		import("COM.Pay.Pay");
		$pay				= new Pay($payment,true,$_POST['money']);
	
	
		$events		= array(
			'init' => array(
				'app'		=> 'Admin',
				'group'		=> '',
				'model'		=> 'PayResult',
				'method'	=> 'init',
				'args'		=> array(
					'userid'	=> '张三'
				),
			),
			'success' => array(
				'app'		=> 'Admin',
				'group'		=> '',
				'model'		=> 'PayResult',
				'method'	=> 'success',
				'args'		=> array(
					'userid'	=> '张三'
				),
			),
		);

		$pay->bind($events);


		//测试提交
		//$pay->testSubmit(false);

		//正式提交
		$pay->submit();
		exit;
	}


	//检查支付接口是否已经安装
	private function checkPayment(&$model,$payment)
	{
		$where['app']		= 'Admin';
		$where['name']		= 'payment_installed';
		$where['data']		= $payment;

		if( $model->where($where)->find() )	
		{
			return 1;
		}
		return 0;
	}
}
?>