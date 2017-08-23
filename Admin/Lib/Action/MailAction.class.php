<?php
defined('APP_NAME') || die('不要非法操作哦!');
class MailAction extends CommonAction {

	public function index(){
		
		$MAIL_ADDRESS  =CONFIG('MAIL_ADDRESS');  // 邮箱地址
		$MAIL_SMTP     =CONFIG('MAIL_SMTP');     //邮箱SMTP服务器
		$MAIL_LOGINNAME=CONFIG('MAIL_LOGINNAME');//邮箱登录帐号
		$MAIL_PASSWORD =CONFIG('MAIL_PASSWORD'); //邮箱密码
		$MAIL_FROMNAME =CONFIG('MAIL_FROMNAME'); //发件人名字
		$this->assign('add',$MAIL_ADDRESS);
		$this->assign('smpt',$MAIL_SMTP);
		$this->assign('log',$MAIL_LOGINNAME);
		$this->assign('pas',$MAIL_PASSWORD);
		$this->assign('fname',$MAIL_FROMNAME);
		
		//保存的数据
        $vo = array();
        //默认发送的内容模板
		$vo['regmailContent'] = CONFIG("regmailContent");
		$vo['exammailContent'] = CONFIG("exammailContent");
		$vo['changePwdmailContent'] = CONFIG("changePwdmailContent");
		$vo['changePwdmailContentyanzheng'] = CONFIG("changePwdmailContentyanzheng");
		$vo['zhzhmmailContent'] = CONFIG("zhzhmmailContent");
		$vo['txmmailContent'] = CONFIG("txmmailContent");
		//发送开关 以及管理员监听
		$vo['regmailSwitch'] = CONFIG("regmailSwitch") == 1 ? 1 : 0;
		
		$vo['exammailSwitch'] = CONFIG("exammailSwitch") == 1 ? 1 : 0;
		
		$vo['changePwdmailSwitch'] = CONFIG("changePwdmailSwitch") == 1 ? 1 : 0;
		
		$vo['changePwdmailSwitchyanzheng'] = CONFIG("changePwdmailSwitchyanzheng") == 1 ? 1 : 0;//验证码发送
		
		$vo['zhzhmmailSwitch'] = CONFIG("zhzhmmailSwitch") == 1 ? 1 : 0;
		
		$vo['txmmailSwitch'] = CONFIG("txmmailSwitch") == 1 ? 1 : 0;
		
		$this->assign("vo",$vo);
			
		$this->display();
	}

	public function mailupdate(){
		$MAIL_ADDRESS=$_POST['add'];
		$MAIL_SMTP=$_POST['smpt'];
		$MAIL_LOGINNAME=$_POST['log'];
		$MAIL_PASSWORD=$_POST['pas']; 
		$MAIL_FROMNAME=$_POST['fname']; 
		M()->startTrans();
		CONFIG('MAIL_ADDRESS',$MAIL_ADDRESS);
		CONFIG('MAIL_SMTP',$MAIL_SMTP);
		CONFIG('MAIL_LOGINNAME',$MAIL_LOGINNAME);
		CONFIG('MAIL_PASSWORD',$MAIL_PASSWORD);
		CONFIG('MAIL_FROMNAME',$MAIL_FROMNAME);
		
		CONFIG('regmailContent',$_POST['regmailContent']);
		CONFIG('exammailContent',$_POST['exammailContent']);
		CONFIG('changePwdmailContent',$_POST['changePwdmailContent']);
		CONFIG('changePwdmailContentyanzheng',$_POST['changePwdmailContentyanzheng']);
	    CONFIG("zhzhmmailContent",$_POST['zhzhmmailContent']);
		CONFIG("txmmailContent",$_POST['txmmailContent']);
		
		CONFIG('regmailSwitch',0);
		
		CONFIG('exammailSwitch',0);
		
		CONFIG('changePwdmailSwitchyanzheng',0);
		
		CONFIG('changePwdmailSwitch',0);
		
		CONFIG('zhzhmmailSwitch',0);
		
		CONFIG('txmmailSwitch',0);
		if(isset($_POST['Switch']))
		foreach($_POST['Switch'] as $k=>$val){
			CONFIG($val,1);
		}
		
		
		M()->commit();
		$this->saveAdminLog('','','邮件设置');
		$this->success('邮件设置完成！');
	}
	
	public function testinputmail()
	{
		$msg='';
		if(isset($_GET['msg'])) $msg=$_GET['msg'];
		$this->assign("msg" ,$msg);
		$this->display();
	}
	/*
	**发送测试邮件的文件
	*/
	public function testsendmail()
	{
		$email = $_POST['email'];
		$subject = $_POST['subject'];
		$content = $_POST['content'];
		 import("COM.Mail.PHPMailer");
		 import("COM.Mail.SMTP");
		 import("COM.Mail.POP3");
	
		 //$mail=new Email();
		 $Mail = new PHPMailer;
         $Mail->SMTPDebug = 0; //Full debug output
         $Mail->Priority = 3;
         $Mail->Encoding = '8bit';
         $Mail->CharSet = 'utf-8';
         //发件人
		 $Mail->From     = CONFIG('MAIL_ADDRESS');
		 //发件名
		 $Mail->FromName = CONFIG('MAIL_FROMNAME');
		 //服务器地址
		 $Mail->Host     = CONFIG('MAIL_SMTP');
		 //
		 $Mail->Port     =25;
         $Mail->SMTPAuth = true;
         $Mail->Username = CONFIG('MAIL_LOGINNAME');
         $Mail->Password = CONFIG('MAIL_PASSWORD');
		 $Mail->Mailer = 'smtp';
		//$this->setAddress($_REQUEST['mail_to'], 'Test User', 'to');
	 	 $Mail->Subject = $subject;
		 $Mail->Body = $content;
		 $Mail->addAddress($email, '');
		 $re = $Mail->send();
		 if($re){
		 	 $this->success('邮件发送成功！');
		 }else{
		 	 $this->error('邮件错误信息：'.$Mail->ErrorInfo);
		 }
		 //die();
		// 发件人的邮箱地址
		//$mail->from= CONFIG('MAIL_ADDRESS');
		// 设置发件人名字
		//$mail->loc_host= CONFIG('MAIL_FROMNAME');
		// 设置SMTP服务器。
		//$mail->smtp_host= CONFIG('MAIL_SMTP');
		// 设置用户名和密码。
		//$mail->smtp_acc= CONFIG('MAIL_LOGINNAME');
	//	$mail->smtp_pass= CONFIG('MAIL_PASSWORD');
		// 发送邮件。
	//	return($mail->send_mail($email,$subject,$content));
	}
}
?>