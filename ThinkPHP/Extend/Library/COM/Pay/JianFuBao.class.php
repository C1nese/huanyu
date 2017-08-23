<?php
import("COM.Interface.PayInterface");

/*
* 简付宝支付类
*
*/
class Jianfubao implements PayInterface
{
	public $Gateway_URL			= 'https://payment.easy8pay.net/gateway.aspx';				//环迅支付网关地址
//	public $Gateway_URL			= 'http://testpay.easy8pay.net/gateway.aspx';				//环迅测试地址
	public $Billno				= 0;				//订单号
	public $Amount				= 0;				//金额
	public $Date				= '';				//时间
	public $Currency_Type		= 'RMB';			//支付币种
	public $Mer_code			= '';				//交易账户号
	public $Mer_key				= '';				//商户证书：登陆http://merchant.ips.com.cn/商户后台下载的商户证书内容
	public $OrderEncodeType		= 2;				//订单支付加密方式
	public $RetEncodeType		= 12;				//交易返回加密方式
	public $SignMD5				= '';				//提交时的数据摘要
	public $sendMsg				= '在线充值的时候请不要关闭页面！充值成功后页面自动跳转..';	//发送充值时的提示
	public $Gateway_Type		= '01';				//支付方式 借记卡
	public $Lang				= 'GB';				//语言
	public $Merchanturl			= '';				//支付成功浏览器重定向的URL,完整路径带http
	public $FailUrl				= '';				//支付失败浏览器重定向的URL,完整路径带http
	public $ErrorUrl			= '';				//支付错误浏览器重定向的URL
	public $Rettype				= 1;				//是否提供Server返回方式
	public $ServerUrl			= '';				//Server返回的URL,完整路径带http
	public $ServerLocationUrl	= '';				//代理收发服务URL
	public $isSupportCredit		= false;			//是否支持银行直连
	public $bank				= '';				//直连的银行
	private $message			= ''; //消息提示
	public $Attach				= '';				

	/*
	* 构造函数
	*/
	function __construct() 
	{
			//读取接口表中的pay_type
		$arr = M('pay_onlineaccount',' ')->where(array('pay_type'=>'JianFuBao'))->order("pay_amount asc,id desc")->find();
	
		//将$arr['pay_attr'] 返序列化
	    $data_arr = unserialize($arr['pay_attr']);//是一个二维数组 
	    //查询金额最小的金额的记录
	      $data = array();
	   foreach((array)$data_arr as $key=>$v)
	   {
	   	 
	   	   foreach((array)$v as $key1=>$v1){
	   	   	 
	         $data[$key1] = $v1;
	      	}
	   }
		//读取数据库中的设置
		$Model						= M();
		$account					= isset($data['jianfubao_account'])?$data['jianfubao_account']:'';
		$key						= isset($data['jianfubao_key'])?$data['jianfubao_key']:'';
		$proxy						= isset($data['jianfubao_proxy'])?$data['jianfubao_proxy']:'';
		$credit						= isset($data['jianfubao_credit'])?$data['jianfubao_credit']:0;
		$this->Mer_code				= $account?$account:'';
		$this->Mer_key				= $key?$key:'';
		$this->ServerLocationUrl	= $proxy?$proxy:'';
		$this->isSupportCredit		= $credit=='1'?true:false;
		$this->Date					= date('Ymd');
	}
	

	//设置支付返回地址
	public function setServerurl($url)
	{
		$this->ServerUrl  = $url;
	}

	//设置浏览器跳转地址
	public function setLocationUrl($url)
	{
	
	}

	//设置订单id
	public function setOrderId($id)
	{
		$this->Billno = $id;
	}
	//设置会员编号
	public function setUserid($id){
	   $this->username = $id;
	}
	//获取订单id
	public function getOrderId()
	{
		return $this->Billno;
	}

	//设置支付金额
	public function setMoney($money)
	{

		$this->Amount = number_format($money,2,'.','');
	}

	//获取支付金额
	public function getMoney()
	{
		return $this->Amount;
	}


	//返回接口名称
	public static function getName()
	{
		return '简付宝';
	}

	//返回接口描述
	public static function getMemo()
	{
		return '简付宝以简单、快捷、安全的支付服务为直销行业提供专业的第三方支付服务。';
	}

	//是否支持银行直连
	public function isSupportCredit()
	{
		return $this->isSupportCredit;
	}

	//设置直连的银行
	public function setCreditBank($bank)
	{
		$this->bank = $bank;
	}
	//提供的直连银行的列表
	public static function getBankList(){}

	//返回当前直连银行的中文名称
	public function getCreditBankName(){}

	/*
	* 返回配置信息
	*/
	public static function getConfigInfo()
	{
		return array(
			array(
				'config_name'=>'jianfubao_name',
				'config_value'=> '简付宝',
				'name'=>'支付方式名称',
				'type'=>'text',
				'style'=>'width:100px',
			),
			array(
				'config_name'=>'jianfubao_account',
				'config_value'=> '',
				'name'=>'商户号',
				'type'=>'text',
				'style'=>'width:100px',
			),
			array(
				'config_name'=>'jianfubao_key',
				'config_value'=> '',
				'name'=>'商户证书',
				'type'=>'text',
				'style'=>'width:400px',
			),
			array(
				'config_name'=>'huanxun_proxy',
				'config_value'=> '',
				'name'=>'php转发Url',
				'type'=>'text',
				'style'=>'width:350px',
				'memo'=>'<a href="/Admin/Common/pay_location.php.txt" target="_blank">下载php转发文件</a>',
			),
		);
	}

	/*
	* 发送充值请求
	*/
	public function submit()
	{

		$this->SignMD5			= $this->getSendMD5();
		$_action_url			= $this->Gateway_URL;
		$_location_url			= '';


		print <<<EOF
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
  <head>
    <title>跳转......</title>
    <meta http-equiv="content-Type" content="text/html; charset=gb2312" />
  </head>
  <body>
    <form action="{$_action_url}" method="post" id="frm1">
      <input type="hidden" name="MerCode" value="{$this->Mer_code}">
      <input type="hidden" name="MerOrderNo" value="{$this->Billno}">
      <input type="hidden" name="Amount" value="{$this->Amount}" >
      <input type="hidden" name="OrderDate" value="{$this->Date}">
      <input type="hidden" name="Currency" value="{$this->Currency_Type}">
      <input type="hidden" name="GatewayType" value="{$this->Gateway_Type}">
      <input type="hidden" name="Language" value="{$this->Lang}">
      <input type="hidden" name="ReturnUrl" value="{$this->Merchanturl}">
      <input type="hidden" name="GoodsInfo" value="{$this->Attach}">
      <input type="hidden" name="OrderEncodeType" value="{$this->OrderEncodeType}">
      <input type="hidden" name="RetEncodeType" value="{$this->RetEncodeType}">
      <input type="hidden" name="Rettype" value="{$this->Rettype}">
      <input type="hidden" name="ServerUrl" value="{$this->ServerUrl}">
      <input type="hidden" name="SignMD5" value="{$this->SignMD5}">
    </form>
    <script language="javascript">
      document.getElementById("frm1").submit();
    </script>
  </body>
</html>
EOF;
		exit;
	}
	
	/*
	* 摘要发送的数据
	*/
	private function getSendMD5()
	{
		//订单支付接口的Md5摘要，原文=订单号+金额+日期+支付币种+商户证书 
		return md5($this->Billno . $this->Amount . $this->Date . $this->Currency_Type . $this->Mer_key);
	}

	public function getMessage()
	{
		return $this->message;
	}

	/*
	* 处理收到的数据
	*/
	public function receive()
	{
	
		$data['amount']			= $_REQUEST['Amount'];			//金额
		$data['date']			= $_REQUEST['OrderDate'];			//时间
		$data['succ']			= $_REQUEST['Succ'];			//成功标志
		$this->message 			= $_REQUEST['Msg'];
		$data['Currency_Type']	= $_REQUEST['Currency'];	//支付币种
		$data['Mer_key']		= $this->Mer_key;	//商户key(商户证书)
		$signature				= $_REQUEST['Signature'];		//返回的摘要
		$data['ipsbillno']		= $_REQUEST['SysOrderNo'];		//订单号码
		$data['retEncodeType']	= $_REQUEST['RetencodeType'];	//交易返回加密方式
		$data['orderid']		= $_REQUEST['MerOrderNo'];
		
		//md5摘要不一样
		if( $signature != $this->getReceiveMD5($data) )
		{
			$this->message = '签名验证失败!';
			return false;
		}
		//md5摘要一样
		else
		{
			if( $data['succ'] == 'Y' )	//支付成功
			{
			
				$this->message = '支付成功!';
				return true;
			}
			else
			{
				$this->message = '支付失败!';
				return false;
			}
		}
	}


	
	/*
	* 摘要收到的数据
	*/
	private function getReceiveMD5($data)
	{
		return md5($data['orderid']. $data['amount'] . $data['date'] .  $data['succ'] . $data['ipsbillno'] . $data['Currency_Type'] . $data['Mer_key'] );	
	}

}
?>