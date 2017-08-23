<?php
import("COM.Interface.PayInterface");
/*
* 智付
*
*/
class ZhiFu implements PayInterface{
	
	//接口对接静态信息
	public static $pay_interface=array(
								//支付接口中文名
								'pay_cname'=>'智付',
								//支付接口英文名
								'pay_ename'=>'ZhiFu',
								//支付接口简介
								'synopsis'=>'智付是智付电子支付有限公司旗下专门处理线上和线下支付业务的一个品牌，智付系统平台是连结银行、银联、发卡组织、移动营运商、城市小额支付的一个支付系统。它专门负责处理来自消费者在商家购买产品时的支付请求信息处理。还为商家与消费者开展电子商务与传统商务进行支付、清算、交易的一个多元化支付平台。',
								//支付接口版本
								'version'=>'1.0v',
								//所有支付接口，统一使用的异步接口(网站根目录)
								'return_url'=>'/Pay_return.php',
								//支付接口接收服务器返回值的订单KEY名
								'order_key'=>'order_no'
								);	
	//支付网关地址
	public $gateway_url			= 'https://pay.dinpay.com/gateway?input_charset=UTF-8';	
	
	//接口版本	
	public $interface_version = 'V3.0'; 
		
	//业务类型	
    public $service_type = 'direct_pay';      
    
    //商家号
    public $merchant_code = '';
    
    //参数编码字符集
    public $input_charset =   'UTF-8';

    //服务器异步通知地址
    public $notify_url = '';
    
    //页面跳转同步通知地址
	public $return_url	= '';
	
	//签名方式
	public  $sign_type = 'MD5';		
	
	//签名
	public $sign = '';
		
    //订单号
    public  $order_no='';
    
    //商户订单的时间
    public  $order_time = '';
     
    //商户订单总金额
    public $order_amount = 0;

    //商品名称
    public $product_name = '智付在线支付';
    
    	//是否支持银行直连
    public $isSupportCredit		= false;		
    
    //加入银行通道
    public $bank            ='';                 

	
	//转发URL
	public $proxy = '';			

	/*
	* 构造函数
	*/
	function __construct()  
	{
	  //读取接口表中的pay_type
		$arr = M('pay_onlineaccount',' ')->where(array('pay_type'=>'ZhiFu'))->order("pay_amount asc,id desc")->find();
		if($arr){
		//将$arr['pay_attr'] 返序列化
	    $data_arr = unserialize($arr['pay_attr']);//是一个二维数组 
	    //查询金额最小的金额的记录
	      $data = array();
		   foreach($data_arr as $key=>$v)
		   {

		         $data[$key] = $v;

		   }
		//读取数据库中的设置
		$Model						= M();
		$MerNo				        = $data[self::$pay_interface['pay_ename'].'_account'];
		$this->merchant_code		= $MerNo?$MerNo:'';
		$SignInfo					=$data[self::$pay_interface['pay_ename'].'_key'];

		$this->sign					= $SignInfo?$SignInfo:'';
		$Hui_proxy					= $data[self::$pay_interface['pay_ename'].'_proxy'];

		$this->proxy				= $Hui_proxy?$Hui_proxy:'';
		
		
        $credit						= $data[self::$pay_interface['pay_ename'].'_credit'];
        $this->isSupportCredit		= $credit=='1'?true:false;
		$this->order_time           = date('Y-m-d H:i:s',systemTime());
		}
	}

	//返回支付接口中文名称
	public static function getName()
	{
		return '智付';
	}

	//返回接口中文介绍
	public static function getMemo()
	{
		return '智付 （Dinpay）是中国领先的独立第三方支付公司';
	}

	//返回需要配置的项
	//返回需要配置的项

	public static function getConfigInfo()

	{

		return array(

			array(

				'config_name'=>self::$pay_interface['pay_ename'].'_name',

				'config_value'=> '智付',

				'name'=>'支付方式名称',

				'type'=>'text',

				'style'=>'width:100px',

			),

			array(

				'config_name'=>self::$pay_interface['pay_ename'].'_account',

				'config_value'=> '',

				'name'=>'商户号',

				'type'=>'text',

				'style'=>'width:100px',

			),

			array(

				'config_name'=>self::$pay_interface['pay_ename'].'_key',

				'config_value'=> '',

				'style'=>'width:430px',

				'name'=>'商户签名',

				'type'=>'text',

			),

			array(

				'config_name'=>self::$pay_interface['pay_ename'].'_proxy',

				'config_value'=> '',

				'name'=>'php转发Url',

				'type'=>'text',

				'style'=>'width:350px',

				'memo'=>'<a href="/Admin/Common/pay_location.php.txt" target="_blank">下载php转发文件</a>',

			),

			array(
				'config_name'=>self::$pay_interface['pay_ename'].'_credit',
				'config_value'=> '0',
				'name'=>'银行直连',
				'type'=>'radio',
				'options'=>array(
					'Yes'=>'使用',
					'No'=>'不使用'
				)
			),

		);

	}


	//提交表单
	public function submit()

	{
		//提交的地址 支付地址

		$_action_url			= $this->gateway_url;

		$_location_url			= '';

		//得到要支付的货币金额

		$Amount                 = $this->order_amount;
		//是否使用代理跳转

		if(	$this->proxy != '' )

		{

			$_action_url	= $this->proxy;

			$_location_url	= base64_encode($this->gateway_url);

		}
				//参数编码字符集(必选)
		$input_charset = $this->input_charset;

		//接口版本(必选)固定值:V3.0
		$interface_version = $this->interface_version;

		//商家号（必填）
		$merchant_code = $this->merchant_code;
		//后台通知地址(必填)
		$notify_url = $this->notify_url;

		//定单金额（必填）
		$order_amount = $this->order_amount;

		//商家定单号(必填)
		$order_no = $this->order_no;

		//商家定单时间(必填)
		$order_time = $this->order_time;

		//签名方式(必填)
		$sign_type = $this->sign_type;


		//商品编号(选填)
		$product_code = '';

		//商品描述（选填）
		$product_desc = '';

		//商品名称（必填）
		$product_name = 'ZhiFu payment';

		//端口数量(选填)
		$product_num ='';

		//页面跳转同步通知地址(选填)
		$return_url = '';

		//业务类型(必填)
		$service_type = $this->service_type;

		//商品展示地址(选填)
		$show_url = '';

		//公用业务扩展参数（选填）
		$extend_param = '';

		//公用业务回传参数（选填）
		$extra_return_param = '';

		// 直联通道代码（选填）
		$bank_code = $this->bank;

		//客户端IP（选填）
		$client_ip = '';

		/* 注  new String(参数.getBytes("UTF-8"),"此页面编码格式"); 若为GBK编码 则替换UTF-8 为GBK*/
		if($product_name != "") {
		  $product_name = mb_convert_encoding($product_name, "UTF-8", "UTF-8");
		}
		if($product_desc != "") {
		  $product_desc = mb_convert_encoding($product_desc, "UTF-8", "UTF-8");
		}
		if($extend_param != "") {
		  $extend_param = mb_convert_encoding($extend_param, "UTF-8", "UTF-8");
		}
		if($extra_return_param != "") {
		  $extra_return_param = mb_convert_encoding($extra_return_param, "UTF-8", "UTF-8");
		}
		if($product_code != "") {
		  $product_code = mb_convert_encoding($product_code, "UTF-8", "UTF-8");
		}
		if($return_url != "") {
		  $return_url = mb_convert_encoding($return_url, "UTF-8", "UTF-8");
		}
		if($show_url != "") {
		  $show_url = mb_convert_encoding($show_url, "UTF-8", "UTF-8");
		}
		
		$signSrc= "";

		//组织订单信息
		if($bank_code != "") {
			$signSrc = $signSrc."bank_code=".$bank_code."&";
		}
		if($client_ip != "") {
	                $signSrc = $signSrc."client_ip=".$client_ip."&";
		}
		if($extend_param != "") {
			$signSrc = $signSrc."extend_param=".$extend_param."&";
		}
		if($extra_return_param != "") {
			$signSrc = $signSrc."extra_return_param=".$extra_return_param."&";
		}
		if($input_charset != "") {
			$signSrc = $signSrc."input_charset=".$input_charset."&";
		}
		if($interface_version != "") {
			$signSrc = $signSrc."interface_version=".$interface_version."&";
		}
		if($merchant_code != "") {
			$signSrc = $signSrc."merchant_code=".$merchant_code."&";
		}
		if($notify_url != "") {
			$signSrc = $signSrc."notify_url=".$notify_url."&";
		}
		if($order_amount != "") {
			$signSrc = $signSrc."order_amount=".$order_amount."&";
		}
		if($order_no != "") {
			$signSrc = $signSrc."order_no=".$order_no."&";
		}
		if($order_time != "") {
			$signSrc = $signSrc."order_time=".$order_time."&";
		}
		if($product_code != "") {
			$signSrc = $signSrc."product_code=".$product_code."&";
		}
		if($product_desc != "") {
			$signSrc = $signSrc."product_desc=".$product_desc."&";
		}
		if($product_name != "") {
			$signSrc = $signSrc."product_name=".$product_name."&";
		}
		if($product_num != "") {
			$signSrc = $signSrc."product_num=".$product_num."&";
		}
		if($return_url != "") {
			$signSrc = $signSrc."return_url=".$return_url."&";
		}
		if($service_type != "") {
			$signSrc = $signSrc."service_type=".$service_type."&";
		}
		if($show_url != "") {
			$signSrc = $signSrc."show_url=".$show_url."&";
		}
		     //设置密钥
		$signSrc = $signSrc."key=".$this->sign;
		$singInfo =  $signSrc;
		$sign = md5($singInfo);

		print <<<EOF

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body >
	<div style="display:none">
<form name="dinpayForm" method="post" id="frm1" action="{$_action_url}">
	<input type="hidden" name="sign" value="{$sign}" />
	<input type="hidden" name="location_url" value="{$_location_url}" />
	<input type="hidden" name="merchant_code" value="{$merchant_code}" />
	<input type="hidden" name="bank_code"  value="{$bank_code}" />
	<input type="hidden" name="order_no"  value="{$order_no}" />
	<input type="hidden" name="order_amount"  value="{$order_amount}" />
	<input type="hidden" name="service_type"  value="{$service_type}" />
	<input type="hidden" name="input_charset"  value="{$input_charset}" />
	<input type="hidden" name="notify_url"  value="{$notify_url}" />
	<input type="hidden" name="interface_version"  value="{$interface_version}"/>
	<input type="hidden" name="sign_type"  value="{$sign_type}" />
	<input type="hidden" name="order_time"  value="{$order_time}" />
	<input type="hidden" name="product_name"  value="{$product_name}" />
	<input Type="hidden" Name="client_ip"  value="{$client_ip}" />
	<input Type="hidden" Name="extend_param"  value="{$extend_param}" />
	<input Type="hidden" Name="extra_return_param"  value="{$extra_return_param}" />
	<input Type="hidden" Name="product_code"  value="{$product_code}" />
	<input Type="hidden" Name="product_desc"  value="{$product_desc}" />
	<input Type="hidden" Name="product_num"  value="{$product_num}" />
	<input Type="hidden" Name="return_url"  value="{$return_url}" />
	<input Type="hidden" Name="show_url"  value="{$show_url}" />
	</form>
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
	* 处理收到的数据
	*/
	public function receive()

	{


		   //商户号
			$merchant_code	= $_REQUEST["merchant_code"];

			//通知类型
			$notify_type = $_REQUEST["notify_type"];

			//通知校验ID
			$notify_id = $_REQUEST["notify_id"];

			//接口版本
			$interface_version = $_REQUEST["interface_version"];

			//签名方式
			$sign_type = $_REQUEST["sign_type"];

			//签名
			$dinpaySign = $_REQUEST["sign"];

			//商家订单号
			$order_no = $_REQUEST["order_no"];

			//商家订单时间
			$order_time = $_REQUEST["order_time"];

			//商家订单金额
			$order_amount = $_REQUEST["order_amount"];

			//回传参数
			$extra_return_param = $_REQUEST["extra_return_param"];

			//智付交易定单号
			$trade_no = $_REQUEST["trade_no"];

			//智付交易时间
			$trade_time = $_REQUEST["trade_time"];

			//交易状态 SUCCESS 成功  FAILED 失败
			$trade_status = $_REQUEST["trade_status"];

			//银行交易流水号
			$bank_seq_no = $_REQUEST["bank_seq_no"];

	      /**
			*签名顺序按照参数名a到z的顺序排序，若遇到相同首字母，则看第二个字母，以此类推，
			*同时将商家支付密钥key放在最后参与签名，组成规则如下：
			*参数名1=参数值1&参数名2=参数值2&……&参数名n=参数值n&key=key值
			**/


			//组织订单信息
			$signStr = "";
			if($bank_seq_no != "") {
				$signStr = $signStr."bank_seq_no=".$bank_seq_no."&";
			}
			if($extra_return_param != "") {
			    $signStr = $signStr."extra_return_param=".$extra_return_param."&";
			}
			$signStr = $signStr."interface_version=V3.0&";
			$signStr = $signStr."merchant_code=".$merchant_code."&";
			if($notify_id != "") {
			    $signStr = $signStr."notify_id=".$notify_id."&notify_type=".$notify_type."&";
			}

		        $signStr = $signStr."order_amount=".$order_amount."&";
		        $signStr = $signStr."order_no=".$order_no."&";
		        $signStr = $signStr."order_time=".$order_time."&";
		        $signStr = $signStr."trade_no=".$trade_no."&";
		        $signStr = $signStr."trade_status=".$trade_status."&";

			if($trade_time != "") {
			     $signStr = $signStr."trade_time=".$trade_time."&";
			}
			$key=$this->sign;
			$signStr = $signStr."key=".$key;
			$signInfo = $signStr;
			//将组装好的信息MD5签名
			$sign = md5($signInfo);

		if($dinpaySign==$sign) //签名验证通过

		{
        	echo "SUCCESS";
        	return true;

		}

		else

		{

			$this->message = "签名不正确！";

			return false;

		}

	}


	//设置支付金额

	public function setMoney($money)

	{

		$this->order_amount = number_format($money,2,'.','');

	}



	//获取支付金额

	public function getMoney()

	{

		return $this->order_amount;

	}



	//设置订单id

	public function setOrderId($id)

	{

		$this->order_no = $id;

	}



	//获取订单id

	public function getOrderId()

	{

		return $this->order_no;

	}



	//设置支付返回地址

	public function setServerurl($url)

	{

		$this->notify_url  = $url;

	}



	//设置浏览器跳转地址

	public function setLocationUrl($url)

	{

	 	$this->return_url  = $url;

	}

	

	//是否支持银行直连

	public function isSupportCredit()

	{

		return $this->isSupportCredit;

	}



	//返回支付失败的提示信息

	public function getMessage()

	{

		return '支付失败';

	}

	//提供的直连银行的列表

	public static function getBankList(){

	   return array(
	   	
	    'ABC'=>'中国农业银行',
	    'ICBC'=>'中国工商银行',
        'CCB'=>'中国建设银行',
	    'BOCOM'=>'交通银行',
        'BOC'=>'中国银行',
	    'CMB'=>'招商银行',
        'CMBC'=>'民生银行',
	    'CEBB'=>'光大银行', 
        'CIB'=>'兴业银行', 
	    'PSBC'=>'中国邮政', 
        'SPABANK'=>'平安银行',
        'ECITIC'=>'中信银行', 
        'GDB'=>'广东发展银行',	 
	    'SPDB'=>'浦发银行',
        'HXB'=>'华夏银行',
        'BEA'=>'东亚银行',
        'CMPAY'=>'中国移动手机支付',
	    'ZYC'=>'代金券支付',

	   );

	}



	//设置直连的银行

	public function setCreditBank($bank){

	    $this->bank = $bank;

	}



	//返回当前直连银行的中文名称

	public function getCreditBankName(){

	    $bankList = ZhiFu::getBankList();

		return $bankList[$this->bank];

	}



}
?>