<?php
/*
* 支付通知接口
*
* 用为 支付网关
* 暂定, 做为二期开发实现
*/
interface PayNotifyInterface
{
	/*
	* status = 1 时,支付成功
	* status = 0 时,支付失败. info 可以提取支付失败的原因
	*/
	public function receive($status,$info)
	{
	
	}
}
?>