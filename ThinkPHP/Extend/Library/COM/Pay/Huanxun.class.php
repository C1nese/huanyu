<?php
import("COM.Interface.PayInterface");

/*
* 环迅支付类
*
*/
class Huanxun implements PayInterface
{
	public $Gateway_URL			= 'https://pay.ips.com.cn/ipayment.aspx';				//环迅支付网关地址
//	public $Gateway_URL			= 'http://pay.ips.net.cn/ipayment.aspx';				//环迅测试地址
	public $Billno				= 0;				//订单号
	public $Amount				= 0;				//金额
	public $Date				= '';				//时间
	public $Currency_Type		= 'RMB';			//支付币种
	public $Mer_code			= '';				//环迅帐号
	public $Mer_key				= '';				//环迅帐号key(商户证书)
	public $OrderEncodeType		= 5;				//订单支付加密方式
	public $RetEncodeType		= 17;				//交易返回加密方式
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

	/*
	* 构造函数
	*/
	function __construct() 
	{
			//读取接口表中的pay_type
		$arr = M('pay_onlineaccount',' ')->where(array('pay_type'=>'Huanxun'))->order("pay_amount asc,id desc")->find();
	
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
		$account					= isset($data['huanxun_account'])?$data['huanxun_account']:'';
		$key						= isset($data['huanxun_key'])?$data['huanxun_key']:'';
		$proxy						= isset($data['huanxun_proxy'])?$data['huanxun_proxy']:'';
		$credit						= isset($data['huanxun_credit'])?$data['huanxun_credit']:'0';
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
		return '环迅';
	}

	//返回接口描述
	public static function getMemo()
	{
		return 'PS(www.ips.com)账户是上海环迅于2005年推出的新一代基于电子邮件的互联网多币种收付款工具。截止到目前，IPS账户具备在线充值、在线收付款、在线转账、网上退款和网上提款等多种功能，并支持多种账户充值方式。';
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

	/*
	* 返回配置信息
	*/
	public static function getConfigInfo()
	{
		return array(
			array(
				'config_name'=>'huanxun_name',
				'config_value'=> '环迅支付',
				'name'=>'支付方式名称',
				'type'=>'text',
				'style'=>'width:100px',
			),
			array(
				'config_name'=>'huanxun_account',
				'config_value'=> '',
				'name'=>'环迅帐号',
				'type'=>'text',
				'style'=>'width:100px',
			),
			array(
				'config_name'=>'huanxun_key',
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
			array(
				'config_name'=>'huanxun_credit',
				'config_value'=> '0',
				'name'=>'银行直连',
				'type'=>'radio',
				'options'=>array(
					'1'=>'使用',
					'0'=>'不使用'
				),
			),
		);
	}

	//提供的直连银行的列表
	public static function getBankList()
	{
		return array(
			'00056'=>'北京农村商业银行',
			'00050'=>'北京银行',
			'00095'=>'渤海银行',
			'00096'=>'东亚银行',
			'00057'=>'光大银行',
			'00052'=>'广发银行',
			'00081'=>'杭州银行',
			'00041'=>'华夏银行',
			'00005'=>'交通银行',
			'00013'=>'民生银行',
			'00085'=>'宁波银行',
			'00087'=>'平安银行',
			'00032'=>'浦东发展银行',
			'00084'=>'上海银行',
			'00023'=>'深圳发展银行',
			'00016'=>'兴业银行',
			'00051'=>'邮政储蓄',
			'00021'=>'招商银行',
			'00086'=>'浙商银行',
			'00004'=>'中国工商银行 | 银行卡支付',
			'00026'=>'中国工商银行 | 手机支付',
			'00015'=>'中国建设银行',
			'00017'=>'中国农业银行',
			'00083'=>'中国银行',
			'00054'=>'中信银行',
		);
	}

	//返回银行名称
	public function getCreditBankName()
	{
		$bankList = Huanxun::getBankList();
		return $bankList[$this->bank];
	}

	//是否使用代理
	public function is_proxy()
	{
		return $this->ServerLocationUrl==''?false:true;
	}

	/*
	* 发送充值请求
	*/
	public function submit()
	{

		$this->SignMD5			= $this->getSendMD5();
		$_action_url			= $this->Gateway_URL;
		$_location_url			= '';

		//是否使用代理跳转
		if(	$this->ServerLocationUrl != '' )
		{
			$_action_url	= $this->ServerLocationUrl;
			$_location_url	= base64_encode($this->Gateway_URL);
		}
		$other_content			= '';
		//是否使用银行直连
		if( $this->isSupportCredit )
		{
			$other_content .= '<input type="hidden" name="DoCredit" value="1" />'."\r\n";
			$other_content .= '		<input type="hidden" name="Bankco" value="'.$this->bank.'" />';
		}
		print <<<EOF
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
<title>页面提示</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
html, body{margin:0; padding:0; border:0 none;font:14px Tahoma,Verdana;line-height:150%;background:white}
a{text-decoration:none; color:#174B73; border-bottom:1px dashed gray}
a:hover{color:#F60; border-bottom:1px dashed gray}
div.message{margin:10% auto 0px auto;clear:both;padding:5px;border:0px solid silver; text-align:center; width:45%}
span.wait{color:blue;font-weight:bold}
span.error{color:red;font-weight:bold}
span.success{color:blue;font-weight:bold}
div.msg{margin:20px 0px}
</style>
</head>
<body>
<div class="message">
	<div class="msg">
	<span class="success">{$this->sendMsg}</span>
	</div>
	<div style="display:none">
		<form action="{$_action_url}" method="post" id="frm1">
		<input type="hidden" name="Mer_code" value="{$this->Mer_code}" />
		<input type="hidden" name="location_url" value="{$_location_url}" />
		<input type="hidden" name="Billno" value="{$this->Billno}" />
		<input type="hidden" name="Amount" value="{$this->Amount}" />
		<input type="hidden" name="Date" value="{$this->Date}" />
		<input type="hidden" name="Currency_Type" value="{$this->Currency_Type}" />
		<input type="hidden" name="Gateway_Type" value="{$this->Gateway_Type}" />
		<input type="hidden" name="Lang" value="{$this->Lang}" />
		<input type="hidden" name="Merchanturl" value="{$this->Merchanturl}" />
		<input type="hidden" name="FailUrl" value="{$this->FailUrl}" />
		<input type="hidden" name="ErrorUrl" value="{$this->ErrorUrl}" />
		<input type="hidden" name="Attach" value="{$this->Attach}" />
		<input type="hidden" name="DispAmount" value="{$this->DispAmount}" />
		<input type="hidden" name="OrderEncodeType" value="{$this->OrderEncodeType}" />
		<input type="hidden" name="RetEncodeType" value="{$this->RetEncodeType}" />
		<input type="hidden" name="Rettype" value="{$this->Rettype}" />
		<input type="hidden" name="ServerUrl" value="{$this->ServerUrl}" />
		<input type="hidden" name="SignMD5" value="{$this->SignMD5}" />
		{$other_content}
		</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
document.getElementById("frm1").submit();
</script>
EOF;
		exit;
	}
	
	/*
	* 摘要发送的数据
	*/
	private function getSendMD5()
	{

		return md5( 'billno'. $this->Billno . 'currencytype' . $this->Currency_Type .'amount'. $this->Amount . 'date' . $this->Date . 'orderencodetype' . $this->OrderEncodeType . $this->Mer_key);
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
	
		$data['amount']			= $_REQUEST['amount'];			//金额
		$data['date']			= $_REQUEST['date'];			//时间
		$data['succ']			= $_REQUEST['succ'];			//成功标志
		$data['Currency_Type']	= $_REQUEST['Currency_type'];	//支付币种
		$data['Mer_key']		= $this->Mer_key;	//商户key(商户证书)
		$signature				= $_REQUEST['signature'];		//环迅返回的摘要
		$data['ipsbillno']		= $_REQUEST['ipsbillno'];		//环迅订单号码
		$data['retEncodeType']	= $_REQUEST['retencodetype'];	//交易返回加密方式
		$data['orderid']		= $_REQUEST['billno'];

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
		return md5( 'billno' . $data['orderid'] . 'currencytype' . $data['Currency_Type']. 'amount' . $data['amount'] . 'date' . $data['date'] . 'succ' .  $data['succ'] . 'ipsbillno' . $data['ipsbillno'] . 'retencodetype' . $data['retEncodeType'] . $data['Mer_key'] );	
	}

}
?>