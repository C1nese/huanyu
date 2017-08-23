<?php
import("COM.Interface.PayInterface");
/*
* 汇潮云宝支付 支付类
* 本支付有提交域名限制，非商户注册域名提交数据，均视为非法钓鱼操作
*/
class ChangJie implements PayInterface{

    //接口对接静态信息
    public static $pay_interface=array(
        //支付接口中文名
        'pay_cname'=>'在线支付',
        //支付接口英文名
        'pay_ename'=>'ChangJie',
        //支付接口简介
        'synopsis'=>'畅捷支付有限公司,简称：畅捷支付，专注于网上支付服务的第三方支付平台， 全力为国内外接受网银，信用卡支付的商家提供世界一流的收单服务。',
        //支付接口版本
        'version'=>'1.0',
        //所有支付接口，统一使用的异步接口(网站根目录)
        'return_url'=>'/pay_return.php',
        //支付接口接收服务器返回值的订单KEY名
        'order_key'=>'outer_trade_no'
    );

    public $gateway_url			= 'https://pay.chanpay.com/mag/gateway/receiveOrder.do';	//支付网关地址

    public $BillNo				= 0;				//订单号

    public $Amount				= 0;				//金额

    public $rate                = 1;

    public $MerNo			    = '';				//商户ID

    public $SignInfo		    = '';				//商户签名

    public $orderTime           = '';               //加入订单时间

    public $cmd					= "Buy";			//业务类型 默认Buy

    public $products			= 'ChangJie online payment';			//商品信息

    public $Remark              ='';                 //添加备注信息

    public $bank            	='';                 //加入银行通道

    public $custom				= '';				//定制信息,支付成功时将原样返回.

    public $bank_type			= '';				//支付通道类型, 可直连到各大银行

    public $need_response		= 1;				//默认为"1": 需要应答机制;

    public $record_address		= 0;				//为"1": 需要用户将送货地址留在易宝支付系统;为"0": 不需要，默认为 "0".

    public $AdviceURL			= '';				//支付结果浏览器通知URL,完整路径带http

    public $Hui_proxy			= '';				//代理转发地址,针对于域名绑定的情况

    public $return_url			= "";	//同步返回URL

    public $message				= '';

    public $sendMsg				= '在线充值的时候请不要关闭页面！充值成功后页面自动跳转..';	//发送充值时的提示





    /*

    * 构造函数

    */

    function __construct()
    {
        //读取接口表中的pay_type

        $arr = M('pay_onlineaccount',' ')->where(array('pay_type'=>self::$pay_interface['pay_ename']))->order("pay_amount asc,id desc")->find();
        if($arr){
            $data_arr = unserialize($arr['pay_attr']);
            $data = array();
            foreach($data_arr as $key=>$v)
            {
                $data[$key] = $v;
            }

            //读取数据库中的设置

            $Model					= M();

            $MerNo				    = $data[self::$pay_interface['pay_ename'].'_account'];

            $this->MerNo			= $MerNo?$MerNo:'';

            $SignInfo				= $data['HuiSignInfo'];

            $this->SignInfo			= $SignInfo?$SignInfo:'';

            $Hui_proxy				= $data[self::$pay_interface['pay_ename'].'_proxy'];

            $this->Hui_proxy		= $Hui_proxy?$Hui_proxy:'';

            $rate                   = $data[self::$pay_interface['pay_ename'].'_merchant_rate'];

            $this->rate			    = $rate ? (float)$rate : 1;

            $this->orderTime        = date('Ymd',systemTime());

        }
    }




    //返回支付接口中文名称

    public static function getName()

    {

        return '在线支付';

    }



    //返回接口中文介绍

    public static function getMemo()

    {

        return '畅捷支付有限公司，是提供国内人民币卡收单服务的第三方支付平台， 全力为国内互联网支付的商家提供国内一流的收单服务。';

    }



    //返回需要配置的项
    //返回需要配置的项

    public static function getConfigInfo()

    {

        return array(

            array(

                'config_name'=>self::$pay_interface['pay_ename'].'_name',

                'config_value'=> '在线支付',

                'name'=>'支付方式名称',

                'type'=>'text',

                'style'=>'width:100px',

            ),
//账号
            array(

                'config_name'=>self::$pay_interface['pay_ename'].'_account',

                'config_value'=> '',

                'name'=>'商户ID',

                'type'=>'text',

                'style'=>'width:100px',

            ),

            array(

                'config_name'=>'HuiSignInfo',

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

                'config_name'=>self::$pay_interface['pay_ename'].'_merchant_rate',

                'config_value'=> '1',

                'name'=>'实付倍数',

                'type'=>'text',

                'style'=>'width:50px',

                'memo'=>'如设置为6倍，则表示支付1元电子币，实际要支付6元人民币',

            ),

        );

    }



    /*
    * 功能： 畅捷支付  收银台
    * 官方说明： http://dev.chanpay.com/doku.php/sdwg:%E6%94%B6%E5%8D%95%E7%BD%91%E5%85%B3%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3#%E6%95%B0%E6%8D%AE%E
    4%BA%A4%E4%BA%92%E6%B5%81%E7%A8%8B%E8%AF%B4%E6%98%8E
    **************************************************************
    * author:chiefyang
    * date:2016/5/19
    * 参数：
    * return:
    */

    //提交表单

    public function submit(){
        $postData   =   array();
        $postData['service']   =   'cjt_create_instant_trade';
        $postData['version']   =   '1.0';
        $postData['partner_id']=   200000780134;  //合作者id
        $postData['_input_charset']=   'UTF-8';
        $postData['sign_type']  =   'RSA'; //签名类型
        $postData['return_url'] =   "http://bod.looksr.com/index.php?s=/User/Fun_bank/index:fun_bank[1]";  //前端回调地址
        //$postData['return_url'] = "http://pcc.mzooe.com".self::$pay_interface['return_url']."?BillNo=".$this->BillNo;
		$postData['out_trade_no']   =   $this->BillNo; //商户唯一订单id
        $postData['trade_amount']   =   $this->Amount;
		//$postData['trade_amount']   =   0.1;
        //$postData['product_name']   =   'aaa';
        $postData['notify_url']     =   "http://bod.looksr.com".self::$pay_interface['return_url'];//通知回调地址
        //$postData['buyer_id']       =   1; //用户id
        $postData['buyer_id_type']  =   'MEMBER_ID';
        $postData['pay_method']     =   '2';
        $postData['is_anonymous']   =   'Y';
		if ( strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') !== false ) {
            $postData['ext1']= '[{webChatOfficialAccounts=true}]';
        }
        $postData['sign']=   $this->rsaSign($postData);
        $query  =   http_build_query($postData);
        $url     = 'https://pay.chanpay.com/mag/gateway/receiveOrder.do?'.$query;  //该url为测试环境url
        header('Location: '.$url);
    }

    /*

    * 处理收到的数据

    */

    //public function receive()

    //{

        //$BillNo			= $_REQUEST['BillNo'];		//订单编号

        //$Amount	 	    = $_REQUEST['Amount'];		// 订单金额

        //$Succeed		= $_REQUEST['Succeed'];	    // 支付状态

        //$Result			= $_REQUEST['Result'];		// 支付结果

        //$MD5info		= $_REQUEST['MD5info'];		// 取得的MD5校验信息

        //$Remark			= $_REQUEST['Remark'];		// 备注

        //$SignInfo       = $this->SignInfo ;        //密钥



        //校验源字符串

        //$md5src = $BillNo.$Amount.$Succeed.$SignInfo;

        //MD5检验结果

        //$md5sign = strtoupper(md5($md5src));


        //if($MD5info==$md5sign) //签名验证通过

        //{

            //if($Succeed=="88") //支付成功

            //{

                //$Model				= M();

                //	$where['orderId']	= $BillNo;

                //	$info				= $Model->table('pay_order')->where($where)->find();

                //$this->message = "success";

                //return true;

            //}

            //else

            //{

               // $this->message = "支付失败";

                //return false;

            //}

        //}

        //else

        //{

            //$this->message = "签名不正确！";

            //return false;

        //}

    //}



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



    //设置订单id

    public function setOrderId($id)

    {

        $this->BillNo = $id;

    }



    //获取订单id

    public function getOrderId()

    {

        return $this->BillNo;

    }



    //设置支付返回地址

    public function setServerurl($url)

    {

        $this->AdviceURL  = $url;

    }



    //设置浏览器跳转地址

    public function setLocationUrl($url)

    {

        $this->return_url  = $url;

    }



    //是否支持银行直连

    public function isSupportCredit()

    {
        return false;

    }



    //返回支付失败的提示信息

    public function getMessage()

    {

        return $this->message;

    }





    //提供的直连银行的列表

    public static function getBankList(){

        return array(

            'ICBC'=>'中国工商银行',

            'CCB'=>'中国建设银行',

            'ABC'=>'中国农业银行',

            'BOCSH'=>'中国银行',

            'SPDB'=>'浦发银行',

            'CMB'=>'招商银行',

            'BOCOM'=>'交通银行',

            'PSBC'=>'邮政储蓄',

            'GDB'=>'广发银行',

            'CMBC'=>'中信银行',

            'CEB'=>'光大银行',

            'HXB'=>'华夏银行',

            'CIB'=>'兴业银行',

            'BOS'=>'上海银行',

            'SRCB'=>'上海农商',

            'PAB'=>'平安银行',

            'BCCB'=>'北京银行',

            'BOC'=>'中行（大额）',

        );

    }



    //设置直连的银行

    public function setCreditBank($bank){

        $this->bank = $bank;

    }



    //返回当前直连银行的中文名称

    public function getCreditBankName(){

        $bankList = ChangJie::getBankList();

        return $bankList[$this->bank];

    }

    /*
 * 功能： 畅捷支付  回调
 * 官方说明： http://dev.chanpay.com/doku.php/sdwg:%E6%94%B6%E5%8D%95%E7%BD%91%E5%85%B3%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3#%E6%95%B0%E6%8D%AE%E
 4%BA%A4%E4%BA%92%E6%B5%81%E7%A8%8B%E8%AF%B4%E6%98%8E
 **************************************************************
 * author:chiefyang
 * date:2016/5/19
 * 参数：
 * params {"notify_time":"20160519202857","sign_type":"RSA","notify_type":"trade_status_sync","trade_status":"TRADE_SUCCESS","gmt_payment":"201605
 19202857","version":"1.0","sign":"oEQbJA9kGz3j ZdSWqobS6bKCB\/OB28LEqqbj6NbAGrN7mVcrXonscLskJ5rFafxQz5dOD5LHx BNvFOHcCoVs6y1xVaodsz FalRAABSm4WIcLXR
 1Lnsq9cBYn0u0MuoQnVzud6j9kH 1gOQMeTouGS\/l4j5GxYNS5l4Z2l6lQ=","extension":"{}","gmt_create":"20160519202857","_input_charset":"UTF-8","outer_trade_n
 o":"150","trade_amount":"9.99","inner_trade_no":"101146366086633549464","notify_id":"14fb4632c5264dd1b50a537c61c8bbc4"}
 * return:
 */
    public function receive() {
		//var_dump($_POST['trade_status']);
		//echo "</br>";
		if($_POST['trade_status'] === 'TRADE_SUCCESS'){
			$params = $_POST;
			$sign   =   $params['sign'];
			//var_dump($sign );
			//echo "</br>";
			unset($params['sign']);
			unset($params['sign_type']);
			$flag   =   $this->rsaVerify($params,$sign);
			//var_dump($flag);die;
			//echo "</br>";
			if($flag){
				$this->message = '支付成功!';
				return true;//签名验证通过  处理商户业务
			}else{
				$this->message = '支付失败!201';
				return false;//签名验证失败
			}
		}else{
			$this->message = '支付失败!202';
			return false;
		}
    }
    /**
     * 功能： 签名
     * 官方说明： http://dev.chanpay.com/doku.php/sdwg:%E6%94%B6%E5%8D%95%E7%BD%91%E5%85%B3%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3#%E6%95%B0%E6%8D%AE%E4%BA%A4%E4%BA%92%E6%B5%81%E7%A8%8B%E8%AF%B4%E6%98%8E
     **************************************************************
     * author:chiefyang
     * date:2016/5/19
     * 参数：
     * $args 签名字符串数组
     * return:
     *
     * return 签名结果
     *
     */
    public function rsaSign($args) {
        $args=array_filter($args);//过滤掉空值
        ksort($args);
        $query  =   '';
        foreach($args as $k=>$v){
            if($k=='sign_type'){
                continue;
            }
            if($query){
                $query  .=  '&'.$k.'='.$v;
            }else{
                $query  =  $k.'='.$v;
            }
        }
        //这地方不能用 http_build_query  否则会urlencode
        //$query=http_build_query($args);
        $path = "/data/web/yxjifen/rsa_private_key.pem";  //私钥地址
        $private_key= file_get_contents($path);
        $pkeyid = openssl_get_privatekey($private_key);
        openssl_sign($query, $sign, $pkeyid);
        openssl_free_key($pkeyid);
        $sign = base64_encode($sign);
        return $sign;
    }
    /**
     * 功能： 验证签名
     * 官方说明： http://dev.chanpay.com/doku.php/sdwg:%E6%94%B6%E5%8D%95%E7%BD%91%E5%85%B3%E6%8E%A5%E5%8F%A3%E6%96%87%E6%A1%A3#%E6%95%B0%E6%8D%AE%E4%BA%A4%E4%BA%92%E6%B5%81%E7%A8%8B%E8%AF%B4%E6%98%8E
     **************************************************************
     * author:chiefyang
     * date:2016/5/19
     * 参数：
     * @param $args 需要签名的数组
     * @param $sign 签名结果
     * return 验证是否成功
     */
    public function rsaVerify($args, $sign)
    {
        //dump($sign);
		//echo "111</br>";
		$args = array_filter($args);//过滤掉空值
        ksort($args);
        $query = '';
        foreach ($args as $k => $v) {
			//dump($k);
			//echo "222</br>";
            if ($k == 'sign_type' || $k == 'sign') {
                continue;
            }
            if ($query) {
                $query .= '&' . $k . '=' . $v;
            } else {
                $query = $k . '=' . $v;
            }
        }
		//dump($query);
		//echo "333</br>";
        //这地方不能用 http_build_query  否则会urlencode
        $sign = base64_decode($sign);
		//dump($sign);
		//echo "444</br>";
        $path = "/data/web/yxjifen/rsa_public_key.pem";  //公钥地址
        $public_key = file_get_contents($path);
		//dump($public_key);
		//echo "555</br>";
        $pkeyid = openssl_get_publickey($public_key);
		//dump($pkeyid);
		//echo "666</br>";
        if ($pkeyid) {
            $verify = openssl_verify($query, $sign, $pkeyid);
            openssl_free_key($pkeyid);
        }
		//dump($verify);
		//echo "777</br>";die;
        if ($verify == 1) {
            return true;
        } else {
            return false;
        }
    }
}

?>