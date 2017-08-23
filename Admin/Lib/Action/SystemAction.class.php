<?php
// 管理员模块
class SystemAction extends CommonAction {
	
	//系统运行设置
	public function index(){
		$viewarr=explode(',',CONFIG('ADMIN_SHOW'));
		$this->assign('viewarr',$viewarr);
		//日志
		$debugConfig = require ROOT_PATH.'Admin/Conf/debug.php';
		$logLevelArr = explode(',' ,$debugConfig['LOG_LEVEL']);
		$this->assign('logLevelArr',$logLevelArr);
		$this->assign('logRecord',$debugConfig['LOG_RECORD']);
		$this->assign('appDebug',$debugConfig['APP_DEBUG']);
		
		//客户可设置项
		$this->assign('USER_SHOP_SALEONLY',CONFIG('USER_SHOP_SALEONLY'));
		$this->assign('SHOW_SHOPSET',CONFIG('SHOW_SHOPSET'));
		$this->display();
	}
	public function save(){
		//日志
		$logRecord = isset($_POST['LOG_RECORD']) ? 'true':'false';
		$appDeubg  = isset($_POST['APP_DEBUG']) ? 'true':'false';
		$logLevel = '';
		if(isset($_POST['LOG_LEVEL'])){
			foreach($_POST['LOG_LEVEL'] as $level){
				$logLevel .= $level.',';
			}
		}
		$logLevel = trim($logLevel,',');
		$content= "<?php
			return array(
					'APP_DEBUG'					=>	".$appDeubg .",
					'LOG_RECORD'				=>	".$logRecord.",
					'LOG_LEVEL'					=>	'$logLevel',
			);
			?>";
		file_put_contents(ROOT_PATH.'Admin/Conf/debug.php',$content);
		//dms项
		//客户可设置项
		$showstrss = '';
		foreach($_POST as $k=>$v)
		{
			if($k!='LOG_RECORD' && $k!='APP_DEBUG' && $k!='LOG_LEVEL')
			{
			  $showstrss.=",".$k;
			}
		}
		M()->startTrans();
		CONFIG('ADMIN_SHOW',trim($showstrss,","));
		CONFIG("USER_SHOP_SALEONLY",isset($_POST['USER_SHOP_SALEONLY'])?(int)$_POST['USER_SHOP_SALEONLY']:0);
		CONFIG("SHOW_SHOPSET"  ,isset($_POST['SHOW_SHOPSET'])  ?(int)$_POST['SHOW_SHOPSET']  :0);
		M()->commit();
		$this->success('设置完成!');
	}
	//系统时间设置
	public function settime(){
		$settlementTime=CONFIG('CAL_START_TIME');
		$TIMEMOVE_HOUR=CONFIG('TIMEMOVE_HOUR');
		$TIMEMOVE_DAY=CONFIG('TIMEMOVE_DAY');
		$shifttime=($TIMEMOVE_HOUR+$TIMEMOVE_DAY*24)*3600*1000;
		$this->assign('shifttime',$shifttime);
		$this->assign('tle',$settlementTime);
		$this->assign('hour',$TIMEMOVE_HOUR);
		$this->assign('day',$TIMEMOVE_DAY);
		$this->assign('SHOW_TIMESET',CONFIG('SHOW_TIMESET'));
		$this->display();
	}
	//系统时间设置更新
	function timeupdate(){
		$data=array();
		$TIMEMOVE_HOUR=$_POST['hour'];
		$TIMEMOVE_DAY=$_POST['day'];
	//	dump($TIMEMOVE_HOUR);dump($TIMEMOVE_DAY);
		if(isset($_POST['tle'])){
			$settlement=strtotime($_POST['tle']);
		}
	//	dump($settlement);exit;
		//得到实际偏移的小时数
		$movehour=(int)$TIMEMOVE_DAY*24+(int)$TIMEMOVE_HOUR;
		$old_TIMEMOVE_HOUR=CONFIG('TIMEMOVE_HOUR');
		$old_TIMEMOVE_DAY =CONFIG('TIMEMOVE_DAY');
		$old_movehour=(int)$old_TIMEMOVE_DAY*24+(int)$old_TIMEMOVE_HOUR;
		
		if($old_movehour>$movehour)
		{
		//	$this->error('偏移时间不能比当前时间提前');
		}
		M()->startTrans();
		CONFIG('TIMEMOVE_DAY',(int)$TIMEMOVE_DAY);
		CONFIG('TIMEMOVE_HOUR',(int)$TIMEMOVE_HOUR);
		
		if(isset($settlement)){
			CONFIG('CAL_START_TIME',(int)$settlement);
		}
		
		if(isset($_POST['SHOW_TIMESET'])){
			CONFIG('SHOW_TIMESET',$_POST['SHOW_TIMESET']);
		}
		M()->commit();
		$this->saveAdminLog('','',"系统时间设置");
        $this->success('修改完成',__URL__.'/settime');
	}
	
}

?>