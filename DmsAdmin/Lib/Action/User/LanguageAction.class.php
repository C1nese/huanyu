<?php
//defined('APP_NAME') || die(L('not_allow'));
class LanguageAction extends CommonAction {
	 //切换简繁
	 public function index(){	 
		//干嘛用的？？？？？
		//$this->assign('languageSwitch',$languageSwitch);
		$this->display();
	 }
}
?>