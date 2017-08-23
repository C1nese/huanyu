<?
import("COM.Interface.PayInterface");
/*
* 网银在线支付类
*/
class BankOnline implements PayInterface
{
	public $Gateway_URL		= 'https://pay3.chinabank.com.cn/PayGate';		// 支付网关地址
	public $sendMsg			= '在线充值的时候请不要关闭页面！充值成功后页面自动跳转..';	//发送充值时的提示
	public $v_mid			= '';		// 商户号，这里为测试商户号1001，替换为自己的商户号(老版商户号为4位或5位,新版为8位)即可
	public $v_url			= '';		// 请填写返回url,地址应为绝对路径,带有http协议
	public $key				= '';		// 商户号的 MD5密钥
	public $v_oid			= 0;		//订单编号
	public $v_amount		= 0;		//支付金额
    public $v_moneytype		= "CNY";	//币种

    public $v_md5info		= '';       //md5函数加密并转化成大写字母
	public $remark1			= '';		//备注字段1
	public $remark2			= '';       //备注字段2

	public $v_rcvname		= '';		// 收货人
	public $v_rcvaddr		= '';		// 收货地址
	public $v_rcvtel		= '';		// 收货人电话
	public $v_rcvpost		= '';		// 收货人邮编
	public $v_rcvemail		= '' ;		// 收货人邮件
	public $v_rcvmobile		= '';		// 收货人手机号

	public $v_ordername		= '';		// 订货人姓名
	public $v_orderaddr		= '';		// 订货人地址
	public $v_ordertel		= '';		// 订货人电话
	public $v_orderpost		= '';		// 订货人邮编
	public $v_orderemail	= '';		// 订货人邮件
	public $v_ordermobile	= '';		// 订货人手机号

	/*
	* 构造函数
	*/
	function __construct() 
	{
		//读取数据库中的设置
		$Model			= M('config');
		$account		= $Model->where("name='wangyinzaixian_account'")->getField('data');
		$key			= $Model->where("name='wangyinzaixian_key'")->getField('data');
		$this->v_mid	= $account?$account:'';
		$this->key		= $key?$key:'';
	}
	
	//设置支付返回地址
	public function setServerurl($url)
	{
		$this->v_url = $url;
	}

	//设置订单id
	public function setOrderId($id)
	{
		$this->v_oid = $id;
	}

	//获取订单id
	public function getOrderId()
	{
		return $this->v_oid;
	}

	//设置支付金额
	public function setMoney($money)
	{
		$this->v_amount = $money;
	}

	//获取支付金额
	public function getMoney()
	{
		return $this->v_amount;
	}

	//返回接口名称
	public static function getName()
	{
		return '网银在线';
	}	

	//返回接口描述
	public static function getMemo()
	{
		return '网银在线（www.chinabank.com.cn）与中国工商银行、招商银行、中国建设银行、农业银行、民生银行等数十家金融机构达成协议。全面支持全国19家银行的信用卡及借记卡实现网上支付。';
	}

	/*
	* 返回配置信息
	*/
	public static function getConfigInfo()
	{
		return array(
			array('name'=>'支付方式名称','config_name'=>'wangyinzaixian_name','config_value'=> '网银在线支付'),
			array('name'=>'商户号','config_name'=>'wangyinzaixian_account','config_value'=> ''),
			array('name'=>'MD5密钥','config_name'=>'wangyinzaixian_key','config_value'=> ''),
		);
	}

	/*
	* 摘要发送的数据
	*/
	private function getSendMD5()
	{
		//md5加密拼凑串并转化成大写字母,注意顺序不能变
		return strtoupper( md5( $this->v_amount.$this->v_moneytype.$this->v_oid.$this->v_mid.$this->v_url.$this->key ) );
	}

	/*
	* 发送充值请求
	*/
	public function getFormHtml()
	{
		$this->v_md5info	= $this->getSendMD5();
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
		<form action="{$this->Gateway_URL}" method="post" id="frm1">
		<input type="hidden" name="v_mid"         value="{$this->v_mid}">
		<input type="hidden" name="v_oid"         value="{$this->v_oid}">
		<input type="hidden" name="v_amount"      value="{$this->v_amount}">
		<input type="hidden" name="v_moneytype"   value="{$this->v_moneytype}">
		<input type="hidden" name="v_url"         value="{$this->v_url}">
		<input type="hidden" name="v_md5info"     value="{$this->v_md5info}">

		<!--以下几项项为网上支付完成后，随支付反馈信息一同传给信息接收页 -->
		<input type="hidden" name="remark1"       value="{$this->remark1}">
		<input type="hidden" name="remark2"       value="{$this->remark2}">

		<!--以下几项只是用来记录客户信息，可以不用，不影响支付 -->
		<input type="hidden" name="v_rcvname"      value="{$this->v_rcvname}">
		<input type="hidden" name="v_rcvtel"       value="{$this->v_rcvtel}">
		<input type="hidden" name="v_rcvpost"      value="{$this->v_rcvpost}">
		<input type="hidden" name="v_rcvaddr"      value="{$this->v_rcvaddr}">
		<input type="hidden" name="v_rcvemail"     value="{$this->v_rcvemail}">
		<input type="hidden" name="v_rcvmobile"    value="{$this->v_rcvmobile}">

		<input type="hidden" name="v_ordername"    value="{$this->v_ordername}">
		<input type="hidden" name="v_ordertel"     value="{$this->v_ordertel}">
		<input type="hidden" name="v_orderpost"    value="{$this->v_orderpost}">
		<input type="hidden" name="v_orderaddr"    value="{$this->v_orderaddr}">
		<input type="hidden" name="v_ordermobile"  value="{$this->v_ordermobile}">
		<input type="hidden" name="v_orderemail"   value="{$this->v_orderemail}">
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
		$data['v_oid']			= trim($_POST['v_oid']);       // 商户发送的v_oid定单编号
		$data['v_pmode']		= trim($_POST['v_pmode']);    // 支付方式（字符串）
		$data['v_pstatus']		= trim($_POST['v_pstatus']);   //  支付状态 ：20（支付成功）；30（支付失败）

		// 支付结果信息 ： 支付完成（当v_pstatus=20时）；失败原因（当v_pstatus=30时,字符串）；
		$data['v_pstring']		= trim($_POST['v_pstring']);   
		$data['v_amount']		= trim($_POST['v_amount']);     // 订单实际支付金额
		$data['v_moneytype']	= trim($_POST['v_moneytype']); //订单实际支付币种
		$data['remark1']		= trim($_POST['remark1' ]);      //备注字段1
		$data['remark2']		= trim($_POST['remark2' ]);     //备注字段2
		$server_md5				= trim($_POST['v_md5str' ]);	 //服务器发回的MD5值

		//md5摘要不一样
		if( $server_md5 != $this->getReceiveMD5($data) )
		{
			$data['status']		= 0;
			$data['info']		= 'md5ValidateFail';
		}
		//md5摘要一样
		else
		{
			if( $data['v_pstatus'] == "20" )	//支付成功
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
	* 摘要收到的数据
	*/
	private function getReceiveMD5($data)
	{
		return strtoupper( md5( $data['v_oid'].$data['v_pstatus'].$data['v_amount'].$data['v_moneytype'].$this->key ) );
	}
}
?>