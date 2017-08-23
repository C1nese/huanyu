<?php
import("COM.Interface.PayInterface");
/*
* 快钱在线支付类
*
* 用法如下:
*
* ********************** 发 送 *******************
* import("ORG.Pay.Kuaiqian");
* $kuaiqian						= new Kuaiqian();
* $kuaiqian->merchantAcctId		= C('ZHIFU_KUAIQIAN_USER');		//人民币网关账户号
* $kuaiqian->key					= C('ZHIFU_KUAIQIAN_KEY');		//人民币网关密钥
* $kuaiqian->pageUrl				= C('SITE_DOMAIN').__URL__."/pay_return/type/kuaiqian";  //接受支付结果的页面地址
* $kuaiqian->orderId				= $info['orderid'];			//商户订单号,由字母、数字、或[-][_]组成
* $kuaiqian->orderAmount			= $info['number']*100;		//订单金额,以分为单位，必须是整型数字,比方2，代表0.02元	
* $kuaiqian->send();
*
* ********************** 接 收 *******************
* import("ORG.Pay.Kuaiqian");
* $kuaiqian						= new Kuaiqian();
* $data							= $kuaiqian->receive();
* 
* 
* if( $data['status'] == 1 ) //支付成功
* {
* 
* }
* else
* {
* 	if( $data['info'] == 'md5ValidateFail'  ) //因为md5校检失败,造成支付失败
* 	{
* 		
* 	}
* 	else //其他原因造成的支付失败
* 	{
* 
* 	}
* }
*/
class Kuaiqian implements PayInterface
{
	////////////////////////参数列表/////////////////////
	/*
	 * @Description: 快钱人民币支付网关接口范例
	 * @Copyright (c) 上海快钱信息服务有限公司
	 * @version 2.0
	 */
	public $Gateway_URL			= 'https://www.99bill.com/gateway/recvMerchantInfoAction.htm';			//快钱支付网关地址
	public $merchantAcctId		= '';			//人民币网关账户号
	public $key					= '';			//人民币网关密钥
	public $inputCharset		= "1";			//字符集  1代表UTF-8; 2代表GBK; 3代表gb2312
	public $pageUrl				= '';			//接受支付结果的页面地址
	public $bgUrl				= "";			//接受支付结果的服务器页面地址
	public $version				= "v2.0";		//网关版本.固定值
	public $language			= "1";			//语言种类. 1代表中文；2代表英文
	public $signType			= "1";			//签名类型  1代表MD5签名
	public $payerName			= 'test';		//支付人姓名
	public $payerContactType	= "1";				//支付人联系方式类型  1代表Email
	public $payerContact		= 'test@163.com';	//支付人联系方式,只能选择Email或手机号
	public $orderId				= '';			//商户订单号,由字母、数字、或[-][_]组成
	public $orderAmount			= 0;		//订单金额,以分为单位，必须是整型数字,比方2，代表0.02元	
	public $orderTime			= '';				//订单提交时间 14位数字。年[4位]月[2位]日[2位]时[2位]分[2位]秒[2位]
	public $productName			= "电子账户充值";		//商品名称
	public $productNum			= "1";					//商品数量,可为空，非空时必须为数字
	public $productId			= "电子币";				//商品代码,可为字符或者数字
	public $productDesc			= "1电子币=1元；";		//商品描述
	public $ext1				= "";		//扩展字段1,在支付结束后原样返回给商户
	public $ext2				= "";		//扩展字段2,在支付结束后原样返回给商户
		
	//支付方式.固定选择值
	///只能选择00、10、11、12、13、14
	///00：组合支付（网关支付页面显示快钱支持的各种支付方式，推荐使用）10：银行卡支付（网关支付页面只显示银行卡支付）.11：电话银行支付（网关支付页面只显示电话支付）.12：快钱账户支付（网关支付页面只显示快钱账户支付）.13：线下支付（网关支付页面只显示线下支付方式）.14：B2B支付（网关支付页面只显示B2B支付，但需要向快钱申请开通才能使用）
	public $payType			= "00";

	//银行代码
	///实现直接跳转到银行页面去支付,只在payType=10时才需设置参数
	public $bankId				= "";

	//同一订单禁止重复提交标志
	///固定选择值： 1、0
	///1代表同一订单号只允许提交1次；0表示同一订单号在没有支付成功的前提下可重复提交多次。默认为0建议实物购物车结算类商户采用0；虚拟产品类商户采用1
	public $redoFlag			= "0";


	public $pid				= ""; ///合作伙伴在快钱的用户编号

	/*
	* 构造函数
	*/
	function __construct() 
	{
		$this->orderTime		= date('YmdHis');
	}
	
	//设置支付金额
	public function setMoney($money)
	{
		$this->orderAmount = $money*100;
	}

	//获取支付金额
	public function getMoney()
	{
		return $this->orderAmount / 100;
	}


	//返回接口名称
	public static function getName()
	{
		return '快钱';
	}

	//返回接口描述
	public static function getMemo()
	{
		return '快钱是国内领先的独立第三方支付企业，旨在为各类企业及个人提供安全、便捷和保密的支付清算与账务服务，其推出的支付产品包括但不限于人民币支付，外卡支付，神州行卡支付，联通充值卡支付，VPOS支付等众多支付产品, 支持互联网、手机、电话和POS等多种终端, 以满足各类企业和个人的不同支付需求。截至2009年6月30日，快钱已拥有4100万注册用户和逾31万商业合作伙伴，并荣获中国信息安全产品测评认证中心颁发的“支付清算系统安全技术保障级一级”认证证书和国际PCI安全认证。';
	}

	/*
	* 返回配置信息
	*/
	public static function getConfigInfo()
	{
		return array(
			array('name'=>'支付方式名称','config_name'=>'kuaiqian_name','config_value'=> '快钱支付'),
			array('name'=>'人民币网关账户号','config_name'=>'kuaiqian_account','config_value'=> ''),
			array('name'=>'人民币网关密钥','config_name'=>'kuaiqian_key','config_value'=> ''),
		);
	}

	public function getFormHtml()
	{
		//发送的数据摘要
		$signMsg	= $this->getSendMD5();
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
	<span class="success">在线充值的时候请不要关闭页面！充值成功后页面自动跳转..</span>
	</div>
	<div style="display:none">
		<form action="{$this->Gateway_URL}" method="post" id="frm1">
			<input type="hidden" name="inputCharset" value="{$this->inputCharset}"/>
			<input type="hidden" name="bgUrl" value="{$this->bgUrl}"/>
			<input type="hidden" name="pageUrl" value="{$this->pageUrl}"/>
			<input type="hidden" name="version" value="{$this->version}"/>
			<input type="hidden" name="language" value="{$this->language}"/>
			<input type="hidden" name="signType" value="{$this->signType}"/>
			<input type="hidden" name="signMsg" value="{$signMsg}"/>
			<input type="hidden" name="merchantAcctId" value="{$this->merchantAcctId}"/>
			<input type="hidden" name="payerName" value="{$this->payerName}"/>
			<input type="hidden" name="payerContactType" value="{$this->payerContactType}"/>
			<input type="hidden" name="payerContact" value="{$this->payerContact}"/>
			<input type="hidden" name="orderId" value="{$this->orderId}"/>
			<input type="hidden" name="orderAmount" value="{$this->orderAmount}"/>
			<input type="hidden" name="orderTime" value="{$this->orderTime}"/>
			<input type="hidden" name="productName" value="{$this->productName}"/>
			<input type="hidden" name="productNum" value="{$this->productNum}"/>
			<input type="hidden" name="productId" value="{$this->productId}"/>
			<input type="hidden" name="productDesc" value="{$this->productDesc}"/>
			<input type="hidden" name="ext1" value="{$this->ext1}"/>
			<input type="hidden" name="ext2" value="{$this->ext2}"/>
			<input type="hidden" name="payType" value="{$this->payType}"/>
			<input type="hidden" name="bankId" value="{$this->bankId}"/>
			<input type="hidden" name="redoFlag" value="{$this->redoFlag}"/>
			<input type="hidden" name="pid" value="{$this->pid}"/>
		</form>
	</div>
</div>
</body>
</html>
<script language="javascript">
document.getElementById("frm1").submit();
</script>
EOF;
	}

	/*
	* 处理收到的数据
	*/
	public function receive()
	{
		/*
		* @Description: 快钱人民币支付网关接口范例
		* @Copyright (c) 上海快钱信息服务有限公司
		* @version 2.0
		*/

		//获取人民币网关账户号
		$merchantAcctId=trim($_REQUEST['merchantAcctId']);

		//设置人民币网关密钥
		///区分大小写
		$key=$this->key;

		//获取网关版本.固定值
		///快钱会根据版本号来调用对应的接口处理程序。
		///本代码版本号固定为v2.0
		$version=trim($_REQUEST['version']);

		//获取语言种类.固定选择值。
		///只能选择1、2、3
		///1代表中文；2代表英文
		///默认值为1
		$language=trim($_REQUEST['language']);

		//签名类型.固定值
		///1代表MD5签名
		///当前版本固定为1
		$signType=trim($_REQUEST['signType']);

		//获取支付方式
		///值为：10、11、12、13、14
		///00：组合支付（网关支付页面显示快钱支持的各种支付方式，推荐使用）10：银行卡支付（网关支付页面只显示银行卡支付）.11：电话银行支付（网关支付页面只显示电话支付）.12：快钱账户支付（网关支付页面只显示快钱账户支付）.13：线下支付（网关支付页面只显示线下支付方式）.14：B2B支付（网关支付页面只显示B2B支付，但需要向快钱申请开通才能使用）
		$payType=trim($_REQUEST['payType']);

		//获取银行代码
		///参见银行代码列表
		$bankId=trim($_REQUEST['bankId']);

		//获取商户订单号
		$orderId=trim($_REQUEST['orderId']);

		//获取订单提交时间
		///获取商户提交订单时的时间.14位数字。年[4位]月[2位]日[2位]时[2位]分[2位]秒[2位]
		///如：20080101010101
		$orderTime=trim($_REQUEST['orderTime']);

		//获取原始订单金额
		///订单提交到快钱时的金额，单位为分。
		///比方2 ，代表0.02元
		$orderAmount=trim($_REQUEST['orderAmount']);

		//获取快钱交易号
		///获取该交易在快钱的交易号
		$dealId=trim($_REQUEST['dealId']);

		//获取银行交易号
		///如果使用银行卡支付时，在银行的交易号。如不是通过银行支付，则为空
		$bankDealId=trim($_REQUEST['bankDealId']);

		//获取在快钱交易时间
		///14位数字。年[4位]月[2位]日[2位]时[2位]分[2位]秒[2位]
		///如；20080101010101
		$dealTime=trim($_REQUEST['dealTime']);

		//获取实际支付金额
		///单位为分
		///比方 2 ，代表0.02元
		$payAmount=trim($_REQUEST['payAmount']);

		//获取交易手续费
		///单位为分
		///比方 2 ，代表0.02元
		$fee=trim($_REQUEST['fee']);

		//获取扩展字段1
		$ext1=trim($_REQUEST['ext1']);

		//获取扩展字段2
		$ext2=trim($_REQUEST['ext2']);

		//获取处理结果
		///10代表 成功; 11代表 失败
		///00代表 下订单成功（仅对电话银行支付订单返回）;01代表 下订单失败（仅对电话银行支付订单返回）
		$payResult=trim($_REQUEST['payResult']);

		//获取错误代码
		///详细见文档错误代码列表
		$errCode=trim($_REQUEST['errCode']);

		//获取加密签名串
		$signMsg=trim($_REQUEST['signMsg']);



		//生成加密串。必须保持如下顺序。
		$signature= $this->appendParam($signature,"merchantAcctId",$merchantAcctId);
		$signature= $this->appendParam($signature,"version",$version);
		$signature= $this->appendParam($signature,"language",$language);
		$signature= $this->appendParam($signature,"signType",$signType);
		$signature= $this->appendParam($signature,"payType",$payType);
		$signature= $this->appendParam($signature,"bankId",$bankId);
		$signature= $this->appendParam($signature,"orderId",$orderId);
		$signature= $this->appendParam($signature,"orderTime",$orderTime);
		$signature= $this->appendParam($signature,"orderAmount",$orderAmount);
		$signature= $this->appendParam($signature,"dealId",$dealId);
		$signature= $this->appendParam($signature,"bankDealId",$bankDealId);
		$signature= $this->appendParam($signature,"dealTime",$dealTime);
		$signature= $this->appendParam($signature,"payAmount",$payAmount);
		$signature= $this->appendParam($signature,"fee",$fee);
		$signature= $this->appendParam($signature,"ext1",$ext1);
		$signature= $this->appendParam($signature,"ext2",$ext2);
		$signature= $this->appendParam($signature,"payResult",$payResult);
		$signature= $this->appendParam($signature,"errCode",$errCode);
		$signature= $this->appendParam($signature,"key",$key);
		$signature= md5($signature);

		$data = array();
		$data['orderId']		= $orderId;		//订单编号
		$data['orderAmount']	= $orderAmount;	//订单金额  单位: 分
		//md5摘要不一样
		if( strtoupper($signMsg) != strtoupper($signature) )
		{
			$data['status']		= 0;
			$data['info']		= 'md5ValidateFail';
		}
		//md5摘要一样
		else
		{
			if( $payResult == '10' )	//支付成功
			{
				$data['status']		= 1;
				$data['info']		= 'paySuccess';
			}
			else
			{
				$data['status']		= 0;
				$data['info']		= 'payFail';
			}
		}
		return $data;	
	}

	/*
	* 摘要发送的数据
	*/
	private function getSendMD5()
	{
		//生成加密签名串
		///请务必按照如下顺序和规则组成加密串！
		$signMsgVal ='';
		$signMsgVal = $this->appendParam($signMsgVal,"inputCharset",$this->inputCharset);
		$signMsgVal = $this->appendParam($signMsgVal,"pageUrl",$this->pageUrl);
		$signMsgVal = $this->appendParam($signMsgVal,"bgUrl",$this->bgUrl);
		$signMsgVal = $this->appendParam($signMsgVal,"version",$this->version);
		$signMsgVal = $this->appendParam($signMsgVal,"language",$this->language);
		$signMsgVal = $this->appendParam($signMsgVal,"signType",$this->signType);
		$signMsgVal = $this->appendParam($signMsgVal,"merchantAcctId",$this->merchantAcctId);
		$signMsgVal = $this->appendParam($signMsgVal,"payerName",$this->payerName);
		$signMsgVal = $this->appendParam($signMsgVal,"payerContactType",$this->payerContactType);
		$signMsgVal = $this->appendParam($signMsgVal,"payerContact",$this->payerContact);
		$signMsgVal = $this->appendParam($signMsgVal,"orderId",$this->orderId);
		$signMsgVal = $this->appendParam($signMsgVal,"orderAmount",$this->orderAmount);
		$signMsgVal = $this->appendParam($signMsgVal,"orderTime",$this->orderTime);
		$signMsgVal = $this->appendParam($signMsgVal,"productName",$this->productName);
		$signMsgVal = $this->appendParam($signMsgVal,"productNum",$this->productNum);
		$signMsgVal = $this->appendParam($signMsgVal,"productId",$this->productId);
		$signMsgVal = $this->appendParam($signMsgVal,"productDesc",$this->productDesc);
		$signMsgVal = $this->appendParam($signMsgVal,"ext1",$this->ext1);
		$signMsgVal = $this->appendParam($signMsgVal,"ext2",$this->ext2);
		$signMsgVal = $this->appendParam($signMsgVal,"payType",$this->payType);	
		$signMsgVal = $this->appendParam($signMsgVal,"bankId",$this->bankId);
		$signMsgVal = $this->appendParam($signMsgVal,"redoFlag",$this->redoFlag);
		$signMsgVal = $this->appendParam($signMsgVal,"pid",$this->pid);
		$signMsgVal = $this->appendParam($signMsgVal,"key",$this->key);
		return strtoupper(md5($signMsgVal));
	}

	//参数链接
	private function appendParam($returnStr,$paramId,$paramValue)
	{
		if($returnStr!="")
		{
			if($paramValue!="")
			{
				$returnStr .= "&".$paramId."=".$paramValue;
			}
		}
		else
		{
			if($paramValue!="")
			{
				$returnStr = $paramId."=".$paramValue;
			}
		}
		return $returnStr;
	}
}
?>