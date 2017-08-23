<?php
exit;
/////////////////////////////////    环迅支付类用法     /////////////////////////


//环迅支付类

//用法如下:


/*------- 发 送 ------*/

import("ORG.Pay.Huanxun");
$huanxun				= new Huanxun();
$huanxun->Billno		= '0001';		//订单号
$huanxun->Amount		= '100';		//支付金额
$huanxun->Mer_code		= 'abc';		//商户帐号
$huanxun->Mer_key		= '123456';		//商户KEY
$huanxun->Merchanturl	= '';			//支付成功浏览器重定向的URL,完整路径带http
$huanxun->Attach		= '';			//附加的数据
$huanxun->send();





/*------- 接 收 -------*/


import("ORG.Pay.Huanxun");
$huanxun				= new Huanxun();
$huanxun->Mer_key		= '123456';		//商户KEY
$data					= $huanxun->receive();
if( $data['status'] == '1' ) //支付成功
{

}
else	//支付失败
{
	if( $data['info'] == 'md5ValidateFail'  ) //因为md5校检失败,造成支付失败
	{

	}
	else //其他原因造成的支付失败
	{
	
	}
}

/////////////////////////////////        快钱支付类用法        //////////////////////////


//快钱在线支付类

//用法如下:


/*------- 发 送 ------*/

import("ORG.Pay.Kuaiqian");
$kuaiqian						= new Kuaiqian();
$kuaiqian->merchantAcctId		= C('ZHIFU_KUAIQIAN_USER');		//人民币网关账户号
$kuaiqian->key					= C('ZHIFU_KUAIQIAN_KEY');		//人民币网关密钥
$kuaiqian->pageUrl				= C('SITE_DOMAIN').__URL__."/pay_return/type/kuaiqian";  //接受支付结果的页面地址
$kuaiqian->orderId				= $info['orderid'];			//商户订单号,由字母、数字、或[-][_]组成
$kuaiqian->orderAmount			= $info['number']*100;		//订单金额,以分为单位，必须是整型数字,比方2，代表0.02元	
$kuaiqian->send();


/*------- 接 收 -------*/

import("ORG.Pay.Kuaiqian");
$kuaiqian						= new Kuaiqian();
$kuaiqian->key					= C('ZHIFU_KUAIQIAN_KEY');		//人民币网关密钥
$data							= $kuaiqian->receive();


if( $data['status'] == 1 ) //支付成功
{

}
else
{
	if( $data['info'] == 'md5ValidateFail'  ) //因为md5校检失败,造成支付失败
	{
		
	}
	else //其他原因造成的支付失败
	{

	}
}


/////////////////////////////////     支付宝支付类用法      //////////////////////////


//用法如下:

/*------- 发 送 ------*/

$security_code   = C('ZHIFU_ZHIFUBAO_KEY'); //安全检验码
$notify_url      = C('SITE_DOMAIN').__URL__."/pay_notify";	//交易过程中服务器通知的页面 要用 http://格式的完整路径
$return_url      = C('SITE_DOMAIN').__URL__."/pay_return/type/zhifubao";	//付完款后跳转的页面 要用 http://格式的完整路径

$parameter1 = array(
	"_input_charset"  => 'utf-8',   //字符编码格式 目前支持 GBK 或 utf-8
	"body"            => "会员".$info['userid']."支付宝充值",        //商品描述，必填
	"notify_url"      => $notify_url,       //异步返回
	"out_trade_no"    => $info['orderid'],   //商品外部交易号，必填（保证唯一性）
	"partner"         => C('ZHIFU_ZHIFUBAO_USER'),     //合作商户号
	"payment_type"    => "1",				//默认为1,不需要修改
	"return_url"      => $return_url,       //同步返回
	"seller_email"    => C('ZHIFU_ZHIFUBAO_ACCOUNT'),	//卖家支付宝帐户
	"service"         => "create_direct_pay_by_user",  //交易类型
	"show_url"        => '',				//商品相关网站
	"subject"         => '帐号充值',        //商品名称，必填
	"total_fee"       => $info['number']    //商品单价，必填（价格不能为0）
);
import("ORG.Pay.Zhifubao");
$zhifubao	= new Zhifubao($parameter1,$security_code);
$link		= $zhifubao->create_url();
$this->assign('link',$link);
$this->display('zhifubao_pay');


/* 模版内容如下 */
/*
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
<title>页面提示</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='Refresh' content='1;URL={$link}'>
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
</div>
</body>
</html>
*/


/*------- 接 收 -------*/

import("ORG.Pay.ZhifubaoNotify");
$zhifubao_account	= C('ZHIFU_ZHIFUBAO_USER');
$security_code		= C('ZHIFU_ZHIFUBAO_KEY'); //安全检验码
$ZhifubaoNotify		= new ZhifubaoNotify($zhifubao_account,$security_code);
$verify_result		= $ZhifubaoNotify->return_verify();
$orderid			= $_GET['out_trade_no'];


if( $verify_result ) //支付成功
{

}
else	//支付失败
{

}



/////////////////////////////////    网银在线支付类用法     /////////////////////////


//网银在线支付类

//用法如下:


/*------- 发 送 ------*/

import("ORG.Pay.BankOnline");
$bankOnline					= new BankOnline();
$bankOnline->v_oid			= '0001';		//订单号
$bankOnline->v_amount		= '100';		//支付金额
$bankOnline->v_mid			= 'abc';		//商户帐号
$bankOnline->key			= '123456';		//商户KEY
$bankOnline->v_url			= '';			//支付完成浏览器重定向的URL,完整路径带http
$bankOnline->remark1		= '';			//备注字段1
$bankOnline->remark2		= '';			//备注字段2
$bankOnline->send();




/*------- 接 收 -------*/
import("ORG.Pay.BankOnline");
$bankOnline					= new BankOnline();
$bankOnline->key			= '123456';		//商户KEY
$data						= $bankOnline->receive();
$orderid					= $data['v_oid'];
if( $data['status'] == '1' ) //支付成功
{

}
else	//支付失败
{
	if( $data['info'] == 'md5ValidateFail'  ) //因为md5校检失败,造成支付失败
	{

	}
	else //其他原因造成的支付失败
	{
	
	}
}
?>