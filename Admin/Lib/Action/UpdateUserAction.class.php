<?php
// 管理员修改密码模块
class UpdateUserAction extends CommonAction 
{
	//管理员密码修改
	public function index(){
		$adminid = $_SESSION[ C('RBAC_ADMIN_AUTH_KEY') ];
		$admin = M('admin');
		$str = array();
		$str1 = array();
		$str = $admin->where(array('id'=>$adminid))->find();
		//添加
		$yubiprefixs = M('yubicloud',null)->where(array('account_id'=>$adminid))->select();
		$this->assign('yubiprefixs',$yubiprefixs);
		$this->assign('vo',$str);
		$this->display();
	}
	public function update(){	
		$id = $_POST['id'];
		$account = $_POST['account'];
		$passwordyz = $_POST['password1'];	
		$pattern = "/^(?![^a-zA-Z]+$)(?!\D+$).{7,15}$/";
		if(!preg_match($pattern,$passwordyz)){
			$this->error("密码必须有字母和数字且字符长度在7-15之间");	
		}	
		$oldpassword = md100($_POST['oldpassword']);
		$password1 = md100($_POST['password1']);
		$password2 = md100($_POST['password2']);
		if(isset($password1) && $password1 != ""){
			if($password1 != $password2){
				$this->error("两次输入的密码不一样");	
			}
		}else{
			$this->error("密码不能为空");	
		}
		$str2 = array();
		M()->startTrans();
		$admin = M('admin');
		$str = $admin->where(array('id'=>$id))->find();
		if(isset($oldpassword) && $str['password'] == $oldpassword){
			$str2['password'] = $password1;
			$cont = $admin->where(array('id'=>$id))->data($str2)->save();
			if($cont){
				$this->success("修改成功");
			}else{
				$this->error("修改失败");
			}
		}else{
	    	$this->error("旧密码错误");
		}
		M()->commit();
	}
	//手机验证码功能
	public function gooelepass()
	{
		import("ORG.Google.Google2FA");
		//import("ORG.Google.Google2FA");
		$this->assign('user','usersystem');
		$pass = Google2FA::generate_secret_key();
		$this->assign('pass',$pass);
		$this->assign('id',$_GET['id']);
		$this->display();
	}
	public function goopassupdate()
	{
		import("ORG.Google.Google2FA");
		$TimeStamp    = Google2FA::get_timestamp();
		$secretkey    = Google2FA::base32_decode($_POST['googlepass']);    // Decode it into binary
		$otp          = Google2FA::oath_hotp($secretkey, $TimeStamp);   // Get current token
		if($_POST['rndpass'] == $otp)
		{
			//判断如果是超管，可以根据ID修改其他管理员的动态密码，如果非超管修改其他会员密码。则要提出警告、
			$admin = M('admin')->where(array('id'=>$_SESSION[ C('RBAC_ADMIN_AUTH_KEY') ]))->find();
			if(!$admin['admin_status'] && $_GET['id']!=$admin['id']){
				$this->error('非超管不能修改其他管理员手机登入信息');
			}
			//设置指定ID资料
			M('admin')->where(array('id'=>$_GET['id']))->save(array('googlepass'=>$_POST['googlepass']));
			$this->success('已设置手机动态验证');
		}
		else
		{
			$this->error('您的6位随机密码不正确，请确认手机时间是否准时');
		}
	}
	public function googlepassClear()
	{
			//判断如果是超管，可以根据ID修改其他管理员的动态密码，如果非超管修改其他会员密码。则要提出警告、
			$admin = M('admin')->where(array('id'=>$_SESSION[ C('RBAC_ADMIN_AUTH_KEY') ]))->find();
			if(!$admin['admin_status'] && $_GET['id']!=$admin['id']){
				$this->error('非超管不能修改其他管理员手机登入信息');
			}
			//设置指定ID资料
			M('admin')->where(array('id'=>$_GET['id']))->save(array('googlepass'=>''));
			$this->success('已取消设置');
	}
	public function getpassqr()
	{
		$pass=$_GET['pass'];
		import("ORG.Qr.QRtools");
		ob_start();ob_clean();
		$qr=new QRcode();
		     //VQHGE6OE3BQUTASI
		$qr->img('otpauth://totp/UserSystem?secret='.$pass);
	}
}
?>