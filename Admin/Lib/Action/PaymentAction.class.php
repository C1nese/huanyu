<?php
/**
 * 名称：处理支付结果 接受服务器和服务器之间的通知
 * 版本：1.0v
 * 修档：2015/08/02
 * 开发者：0025
 * 验收人：冯露露
 * 开发信息：临沂市新商网络技术有限公司
 */

class PaymentAction extends Action{
    
     // 处理支付运营商返回信息
    public function receive(){
         if(!empty($_REQUEST)){
             M() -> startTrans();
             $lista = F('interface_data'); //所有支付接口列表
             $listb = F('banklist'); //已安装支付接口列表
             if(isset($lista) && isset($listb) && is_array($listb)){
                $listc = array_intersect_key ($lista, $listb); //获取可能产生支付订单的列表（包含支付接口的订单KEY）
             }else{
                $listc = $lista;
             }
             foreach($listc as $key => $value){
                 if(!empty($_REQUEST[$value['order_key']])){
                     $where['orderId'] = $_REQUEST[$value['order_key']];
                     }
                 }
             if(!empty($where['orderId'])){
                 import("Admin.Pay.Pay");
                 $PayOrder = M('PayOrder');
                 $info = $PayOrder -> where($where) -> find(); //根据订单号查询订单库
                 if(!empty($info)){
                     $payment = $info['payment_class']; //获取支付接口名
                     $pay = new Pay($payment, false); //这里交给核心类处理
                     $pay -> receive($where['orderId']); //支付接口判断支付成功还是失败
                     }
                 }
             unset($lista, $listb, $listc, $where, $PayOrder, $info, $payment, $pay);
             M() -> commit();
             }
         $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
         echo "<script language='javascript'>location.href='" . $http_type . $_SERVER['HTTP_HOST'] . "';</script>";
         }
    
     }
?>