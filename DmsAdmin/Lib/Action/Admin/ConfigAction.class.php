<?php
defined('APP_NAME') || die('不要非法操作哦!');
class ConfigAction extends CommonAction{
	//奖金参数设置
	public function tleedit(){
		$xml=file_get_contents(ROOT_PATH."/DmsAdmin/config.xml");
		$xml=substr($xml,strpos($xml,"<tleset"));
		$xml=substr($xml,strpos($xml,">")+1);
		$xml=substr($xml,0,strpos($xml,"</tleset>"));
		$content=$xml;
		//增加标题行
		preg_match_all("/<table[^>]*\>/",$content,$outs);
		foreach($outs[0] as $out)
		{
			$tablename=$this->gettag($out,'title');
			if($tablename)
			{
				$content=str_replace($out,$out.'<thead><th colspan="10" style="text-align:left;padding-left:10px;color:#153989"><img src="__PUBLIC__/Images/cog.png" style="vertical-align:middle;padding-right:5px;" >'.$tablename.'</th></thead>',$content);
			}
			else
			{
				$content=str_replace($out,$out.'<thead><th colspan="10">&nbsp;</th></thead>',$content);
			}
		}
		//增加表格样式
		$content=str_replace('<table','<table class="list" ',$content);
		//读取配置值（可计算）
		preg_match_all("/{[^}]+}/",$content,$outs);
		foreach($outs[0] as $out)
		{
			$str=str_replace("{",'',$out);
			$str=str_replace("}",'',$str);
				 preg_match_all('/#(.*)#/U',$str,$truevals,PREG_SET_ORDER);
				 if(count($truevals)>0){
				  foreach($truevals as $trueval)
				  {
		            if($trueval[1] !=''){
					   $str=str_replace("#".$trueval[1]."#",CONFIG($trueval[1]),$str);
			        }
			      }
				 }
			$val=transform($str);
			$content = str_replace($out,$val,$content);
		}
		//默认值赋值处理
		//--查询所有表单
		preg_match_all("/<input[^>]*\>/",$content,$outs);
		//循环所有表单
		foreach($outs[0] as $input)
		{
			//得到表单名
			$name=$this->gettag($input,'name');
			//得到表单类型
			$type=$this->gettag($input,'type');
			//得到默认值value
			$value=$this->gettag($input,'value');
			//如果类型为null
			if($type==null)
			{
				$type='text';
			}
			
			if(CONFIG('',$name))
			{
				$nowval=CONFIG($name);
				if($type=='date')
				{
					$nowval=date('Y-m-d',$nowval);
				}
			}
			else
			{
				$nowval = $value;
			}
			if($type=='text')
			{
				//对表单原有的value进行删除
				$newinput = $this->settag($input,'value',$nowval);
				if($this->gettag($input,'size') == null)
				{
					$newinput = $this->settag($newinput,'size',8);
				}
				$content = str_replace($input,$newinput,$content);
			}
			if($type=='date')
			{
				$newinput = $this->settag($input,'value',$nowval);
				$newinput = $this->settag($newinput,'class','date textInput');
				$content = str_replace($input,$newinput,$content);
			}
			if($type=='checkbox')
			{
				//如果设置过值.则需要处理.否则不需要动
				if(CONFIG('',$name))
				{
					if(CONFIG($name)==$value)
					{
						$newinput = $this->settag($input,'checked','checked');
					}
					else
					{
						$newinput = $input;
						preg_match("/checked\s*=\s*['\"]?([^\s\>'\"]+)['\"].*?/",$newinput,$ifchecked);
						//dump($ifchecked);
						if($ifchecked)
						{
							$newinput = str_replace($ifchecked[0],'',$newinput);
						}
						
					}
					//dump($newinput);
					$content = str_replace($input,$newinput,$content);
				}
			}
		}
		//对select循环进行处理
		preg_match_all('/<select[^>]*[^>]*>[^<]*(<option[^>]*>[^<]*<\/option>[^<]*)*<\/select>/i',$content,$outs2);
		foreach($outs2[0] as $out2)
		{
			preg_match("/name\s*=\s*['\"]?([^\s\>'\"]+)['\"]?/",$out2,$out22);
			$name=$out22[1];
			if(CONFIG('',$name))
			{
				$nowval=CONFIG($name);
				preg_match_all('/<option[^>]*>[^<]*<\/option>/i',$out2,$uee);
				foreach($uee[0] as $uees){
					$valuesel=$this->gettag($uees,'value');
					if($nowval==$valuesel){	
						$b2=str_replace('<option','<option selected=selected ',$uees);
						$contentss=str_replace($uees,$b2,$out2);
						$content=str_replace($out2,$contentss,$content);
					}
				}
			}
		}
		$this->assign('content',$content);
		$this->display();
	}
	
	public function settag($html,$tagname,$val){
		preg_match("/".$tagname."\s*=\s*['\"]?([^\s\>'\"]+)['\"].*?/",$html,$out2);
		if(count($out2)==0)
		{
			return str_replace('/>'," ".$tagname."='".$val."'/>",$html);
		}
		else
		{
			return str_replace($out2[0],"".$tagname."='".$val."'",$html);
		}
	}
	public function gettag($html,$tagname){
		preg_match("/$tagname\s*=\s*['\"]?([^\s\>'\"]+)['\"].*?/",$html,$out2);
		if(count($out2)==0)
		{
			return null;
		}
		return $out2[1];
	}
	
	public function getcons(){
		$ret=array();
		$content=file_get_contents(ROOT_PATH."/DmsAdmin/config.xml");
		$content=substr($content,strpos($content,"<tleset"));
		$content=substr($content,strpos($content,">")+1);
		$content=substr($content,0,strpos($content,"</tleset>"));
		$content=$content;
		preg_match_all("/<input[^>]*\>|<select[^>]*\>/",$content,$inputs);
		foreach($inputs[0] as $input)
		{
			$name    =$this->gettag($input,'name');
			$value   =$this->gettag($input,'value');
			$type    =$this->gettag($input,'type');
			$offvalue=$this->gettag($input,'offvalue');
			$isnum	 =$this->gettag($input,'isnum');
			//对比较特殊的情况进行处理,如果是checkbox的话.没有设置checked,则要使用offvalue作为value值
			if($type == 'checkbox')
			{
				if($this->gettag($input,'checked') == null)
				{
					$value=$offvalue;
				}
			}
			$ret[]=array('name'=>$name,'value'=>$value,'offvalue'=>$offvalue,'type'=>$type,'isnum'=>$isnum!=''?$isnum:true);
		}
		return $ret;
	}
	//系统设置
	public function sysedit1009(){
		//获得时间信息设置数据
		if(!CONFIG('SYSTEM_STATE'))
		{
			M()->startTrans();
			CONFIG('SYSTEM_STATE',1);
			M()->commit();
		}
		if(CONFIG('USER_LOGIN_VERIFY') === null){
			M()->startTrans();
			CONFIG('USER_LOGIN_VERIFY',1);
			M()->commit();
		}
		$this->assign('SYSTEM_STATE',CONFIG('SYSTEM_STATE'));
		//编号生成 需判断是豪华版还是简化版
		if(C('VERSION_SWITCH') == '0'){
			$this->assign('Complete',true);
		}else{
			$this->assign('Complete',false);
		}
		$this->assign('SYSTEM_TITLE',CONFIG('SYSTEM_TITLE'));
		$this->assign('SYSTEM_COMPANY',CONFIG('SYSTEM_COMPANY'));
		$this->assign('SYSTEM_MEMO',CONFIG('SYSTEM_MEMO'));
		$this->assign('USER_LOGIN_VERIFY',CONFIG('USER_LOGIN_VERIFY'));
		$this->assign('DEFAULT_USER_PASS1',CONFIG('DEFAULT_USER_PASS1'));
		$this->assign('DEFAULT_USER_PASS2',CONFIG('DEFAULT_USER_PASS2'));
		$this->assign('DEFAULT_USER_PASS3',CONFIG('DEFAULT_USER_PASS3'));
		$this->assign('pwd3Switch',adminshow('pwd3Switch'));
		//客服qq的设置
		$this->assign('TYPE_QQ',CONFIG('TYPE_QQ'));
		$this->assign('SERVICE_QQ_0',CONFIG('SERVICE_QQ_0'));//普通qq
		$this->assign('SERVICE_QQ_1',CONFIG('SERVICE_QQ_1'));//营销qq
		
		$this->assign('SYSTEM_CLOSE_TITLE',CONFIG('SYSTEM_CLOSE_TITLE'));
        //获得用户数据设置信息
		$showarr   =explode(',',CONFIG('USER_REG_SHOW'));
		$editarr   =explode(',',CONFIG('USER_EDIT_SHOW'));
		$viewarr   =explode(',',CONFIG('USER_VIEW_SHOW'));
		$trutharr  =explode(',',CONFIG('USER_TRUTH'));
		$requirearr=explode(',',CONFIG('USER_REG_REQUIRED'));
		
		$idEdit=$this->userobj->getatt('idEdit');
		$idAutoEdit=$this->userobj->getatt('idAutoEdit');
		$idRand=$this->userobj->getatt('idRand');
		$idInDate=$this->userobj->getatt('idInDate');
		$idSerial=$this->userobj->getatt('idSerial');
		$idPrefix=$this->userobj->getatt('idPrefix');
		$idLength=$this->userobj->getatt('idLength');
		$onlyMobile=$this->userobj->getatt('onlyMobile');
		$onlyIdCard=$this->userobj->getatt('onlyIdCard');
		$onlyBankCard=$this->userobj->getatt('onlyBankCard');
		$user=array(
			'show'=>$showarr,
			'edit'=>$editarr,
			'view'=>$viewarr,
			'truth'=>$trutharr,
			'require'=>$requirearr,
			'idEdit'=>$idEdit,
			'idRand'=>$idRand,
			'idAutoEdit'=>$idAutoEdit,
			'idInDate'=>$idInDate,
			'idSerial'=>$idSerial,
			'idPrefix'=>$idPrefix,
			'idLength'=>$idLength,
			'onlyMobile'=>$onlyMobile,
			'onlyIdCard'=>$onlyIdCard,
			'onlyBankCard'=>$onlyBankCard,
		);
		
		
		$this->assign('user',$user);
		//$this->assign('startOpenTime',F('startOpenTime'));//开放起始时间
		//$this->assign('endOpenTime',F('endOpenTime'));	 //开放结束时间
		$this->assign('shop',X('user')->shopWhere != '');
		
		//系统开放时间
		$dateValue = CONFIG('SYSTEM_OpenDateRange');
        $dateValue1 = explode('|',$dateValue);
        $data = array();
        foreach($dateValue1 as $val){
            if($val != ""){
                $v = explode(';',$val);
                if($v[0]!="" || $v[1]!=""){
                    $vd = $v[0] .'至'. $v[1];                    
                    $vdw = $vd.'   '.$v[2];                    
                }else{
                    $vdw = $v[2];
                }
                $data[] = array($val,$vdw);
            }
        }
        $this->assign('opendata',$data);
        $this->assign('opendateValue',$dateValue);
	    
		$this->display();
	}
    
    
    	public function sysedit(){
		//获得时间信息设置数据
		if(!CONFIG('SYSTEM_STATE'))
		{
			M()->startTrans();
			CONFIG('SYSTEM_STATE',1);
			M()->commit();
		}
		if(CONFIG('USER_LOGIN_VERIFY') === null){
			M()->startTrans();
			CONFIG('USER_LOGIN_VERIFY',1);
			M()->commit();
		}
		$this->assign('SYSTEM_STATE',CONFIG('SYSTEM_STATE'));
		//编号生成 需判断是豪华版还是简化版
		if(C('VERSION_SWITCH') == '0'){
			$this->assign('Complete',true);
		}else{
			$this->assign('Complete',false);
		}
		$this->assign('SYSTEM_TITLE',CONFIG('SYSTEM_TITLE'));
		$this->assign('SYSTEM_COMPANY',CONFIG('SYSTEM_COMPANY'));
		$this->assign('SYSTEM_MEMO',CONFIG('SYSTEM_MEMO'));
		$this->assign('USER_LOGIN_VERIFY',CONFIG('USER_LOGIN_VERIFY'));
		$this->assign('DEFAULT_USER_PASS1',CONFIG('DEFAULT_USER_PASS1'));
		$this->assign('DEFAULT_USER_PASS2',CONFIG('DEFAULT_USER_PASS2'));
		$this->assign('DEFAULT_USER_PASS3',CONFIG('DEFAULT_USER_PASS3'));
		$this->assign('pwd3Switch',adminshow('pwd3Switch'));
		//客服qq的设置
		$this->assign('TYPE_QQ',CONFIG('TYPE_QQ'));
		$this->assign('SERVICE_QQ_0',CONFIG('SERVICE_QQ_0'));//普通qq
		$this->assign('SERVICE_QQ_1',CONFIG('SERVICE_QQ_1'));//营销qq
		
		$this->assign('SYSTEM_CLOSE_TITLE',CONFIG('SYSTEM_CLOSE_TITLE'));
        //获得用户数据设置信息
		$showarr   =explode(',',CONFIG('USER_REG_SHOW'));
		$editarr   =explode(',',CONFIG('USER_EDIT_SHOW'));
		$viewarr   =explode(',',CONFIG('USER_VIEW_SHOW'));
		$trutharr  =explode(',',CONFIG('USER_TRUTH'));
		$requirearr=explode(',',CONFIG('USER_REG_REQUIRED'));
		
		$idEdit=$this->userobj->getatt('idEdit');
		$idAutoEdit=$this->userobj->getatt('idAutoEdit');
		$idRand=$this->userobj->getatt('idRand');
		$idInDate=$this->userobj->getatt('idInDate');
		$idSerial=$this->userobj->getatt('idSerial');
		$idPrefix=$this->userobj->getatt('idPrefix');
		$idLength=$this->userobj->getatt('idLength');
		$onlyMobile=$this->userobj->getatt('onlyMobile');
		$onlyIdCard=$this->userobj->getatt('onlyIdCard');
		$onlyBankCard=$this->userobj->getatt('onlyBankCard');
		$user=array(
			'show'=>$showarr,
			'edit'=>$editarr,
			'view'=>$viewarr,
			'truth'=>$trutharr,
			'require'=>$requirearr,
			'idEdit'=>$idEdit,
			'idRand'=>$idRand,
			'idAutoEdit'=>$idAutoEdit,
			'idInDate'=>$idInDate,
			'idSerial'=>$idSerial,
			'idPrefix'=>$idPrefix,
			'idLength'=>$idLength,
			'onlyMobile'=>$onlyMobile,
			'onlyIdCard'=>$onlyIdCard,
			'onlyBankCard'=>$onlyBankCard,
		);
		
		
		$this->assign('user',$user);
		//$this->assign('startOpenTime',F('startOpenTime'));//开放起始时间
		//$this->assign('endOpenTime',F('endOpenTime'));	 //开放结束时间
		$this->assign('shop',X('user')->shopWhere != '');
		
		//系统开放时间
		$dateValue = CONFIG('SYSTEM_OpenDateRange');
        $dateValue1 = explode('|',$dateValue);
        $data = array();
        foreach($dateValue1 as $val){
            if($val != ""){
                $v = explode(';',$val);
                $vdw="";
                /*if($v[2]!="" || $v[3]!=""){
                	$vdw .= $v[2] .'至'. $v[3];
                }*/
                //周期week数据
                if($v[2]!=""){
                	$vdw .= "(".$v[2].")";
                }
                //时分秒数据
                if($v[0]!="" || $v[1]!=""){
                    $vdw .= "(".$v[0] .'至'. $v[1].")";
                }
                $data[] = array($val,$vdw);
            }
        }
        $this->assign('opendata',$data);
        $this->assign('opendateValue',$dateValue);
        
        $this->assign('startOpenTime',F('startOpenTime'));//开放起始时间
		$this->assign('endOpenTime',F('endOpenTime'));	 //开放结束时间
	    
		$this->display();
	}
    //系统设置更新
    public function sysupdate1009(){
		$data=array();
		M()->startTrans();
		//$settlement=strtotime($_POST['tle']);
		$SYSTEM_CLOSE_TITLE  = $_POST['SYSTEM_CLOSE_TITLE'];
		$SYSTEM_STATE        = $_POST['SYSTEM_STATE'];
		$SYSTEM_TITLE        = $_POST['SYSTEM_TITLE'];
		$SYSTEM_COMPANY      = $_POST['SYSTEM_COMPANY'];
		$SYSTEM_MEMO         = $_POST['SYSTEM_MEMO'];
		$USER_LOGIN_VERIFY   = $_POST['USER_LOGIN_VERIFY'];
		$DEFAULT_USER_PASS1  = $_POST['DEFAULT_USER_PASS1'];
		$DEFAULT_USER_PASS2  = $_POST['DEFAULT_USER_PASS2'];
		$DEFAULT_USER_PASS3  = isset($_POST['DEFAULT_USER_PASS3']) ? $_POST['DEFAULT_USER_PASS3'] : '';
        
        
	    //F('SYSTEM_CLOSE_TITLE',$SYSTEM_CLOSE_TITLE);
		//F('startOpenTime',$_POST['startOpenTime']);
		//F('endOpenTime',$_POST['endOpenTime']);
		//默认行为 1通过 2阻止
		CONFIG('SYSTEM_STATE',$SYSTEM_STATE);
		//日期范围 通过状态下为不能登录的时间范围，阻止状态下为可登录的时间范围
		CONFIG("SYSTEM_OpenDateRange",trim($_POST["opendateValue"],'|'));
		CONFIG('SYSTEM_CLOSE_TITLE',$SYSTEM_CLOSE_TITLE);
		
		CONFIG('SYSTEM_TITLE',$SYSTEM_TITLE);
		CONFIG('SYSTEM_COMPANY',$SYSTEM_COMPANY);
		CONFIG('SYSTEM_MEMO',$SYSTEM_MEMO);
		CONFIG('USER_LOGIN_VERIFY',$USER_LOGIN_VERIFY);
		//客服qq的设置
		CONFIG('TYPE_QQ',$_POST['TYPE_QQ']);
		//字段名
		$SERVICE_QQ='SERVICE_QQ_'.$_POST['TYPE_QQ'];
		$SERVICE_NO='SERVICE_QQ_'.abs(1-$_POST['TYPE_QQ']);
		CONFIG($SERVICE_QQ,$_POST[$SERVICE_QQ]);
		//清除为选择的设置
		CONFIG($SERVICE_NO,'');
		
		CONFIG('DEFAULT_USER_PASS1',$DEFAULT_USER_PASS1);
		CONFIG('DEFAULT_USER_PASS2',$DEFAULT_USER_PASS2);
		CONFIG('DEFAULT_USER_PASS3',$DEFAULT_USER_PASS3);
		//注册可见 注册必填 可修改项
		$infoarr=array();
		$showstr='';
		$editstr='';
		$requirestr='';
		$viewstr='';
		$truthstr='';
		foreach($_POST as $k=>$v)
		{
			if(strpos($k,'show_') !== false)
			{
			  $showstr.=",".$v;
			}
			if(strpos($k,'edit_') !== false)
			{
				$editstr.=",".$v;
			}
			if(strpos($k,'view_') !== false)
			{
				$viewstr.=",".$v;
			}
			if(strpos($k,'require_') !== false)
			{
				$requirestr.=",".$v;
			}
			if(strpos($k,'truth_') !== false)
			{
				$truthstr.=",".$v;
			}
		}
		if($showstr !='')
		{
		  $showstr=substr($showstr,1);
		}
		if($editstr !='')
		{
		  $editstr=substr($editstr,1);
		}
		if($viewstr !='')
		{
		  $viewstr=substr($viewstr,1);
		}
		if($requirestr !='')
		{
		  $requirestr=substr($requirestr,1);
		}
		$user=X('user');
		CONFIG('USER_REG_SHOW' ,$showstr);
		CONFIG('USER_EDIT_SHOW',$editstr);
		CONFIG('USER_VIEW_SHOW',$viewstr);
		CONFIG('USER_REG_REQUIRED',$requirestr);
		CONFIG('USER_TRUTH',trim($truthstr,","));
		$user->setatt('idEdit'  ,(isset($_POST['idEdit']) && $_POST['idEdit']=='1'));
		$user->setatt('idRand'  ,(isset($_POST['idRand']) && $_POST['idRand']=='1'));
		$user->setatt('idInDate',(isset($_POST['idInDate']) && $_POST['idInDate']=='1'));
		$user->setatt('idAutoEdit',(isset($_POST['idAutoEdit']) && $_POST['idAutoEdit']=='1'));
		//唯一设置
		$user->setatt('onlyMobile'  ,$_POST['only_mobile']);
		$user->setatt('onlyIdCard'  ,$_POST['only_id_card']);
		$user->setatt('onlyBankCard',$_POST['only_bank_card']);
		
		//注册编号生成设置
		$user->setatt('idSerial',$_POST['idSerial']);
		$user->setatt('idPrefix',$_POST['idPrefix']);
		$user->setatt('idLength',$_POST['idLength']);
		$this->saveAdminLog('',$_POST,"系统设置","系统参数设置");
		M()->commit();
        $this->success('修改完成',__URL__.'/sysedit');
	}
    public function sysupdate(){
		$data=array();
		M()->startTrans();
		//$settlement=strtotime($_POST['tle']);
		$SYSTEM_CLOSE_TITLE  = $_POST['SYSTEM_CLOSE_TITLE'];
		$SYSTEM_STATE        = $_POST['SYSTEM_STATE'];
		$SYSTEM_TITLE        = $_POST['SYSTEM_TITLE'];
		$SYSTEM_COMPANY      = $_POST['SYSTEM_COMPANY'];
		$SYSTEM_MEMO         = $_POST['SYSTEM_MEMO'];
		$USER_LOGIN_VERIFY   = $_POST['USER_LOGIN_VERIFY'];
		$DEFAULT_USER_PASS1  = $_POST['DEFAULT_USER_PASS1'];
		$DEFAULT_USER_PASS2  = $_POST['DEFAULT_USER_PASS2'];
		$DEFAULT_USER_PASS3  = isset($_POST['DEFAULT_USER_PASS3']) ? $_POST['DEFAULT_USER_PASS3'] : '';
        
        F('systemState',$SYSTEM_STATE);
		F('startOpenTime',$_POST['startOpenTime']);
		F('endOpenTime',$_POST['endOpenTime']);
        
        
	    //F('SYSTEM_CLOSE_TITLE',$SYSTEM_CLOSE_TITLE);
		//F('startOpenTime',$_POST['startOpenTime']);
		//F('endOpenTime',$_POST['endOpenTime']);
		//默认行为 1通过 2阻止
		CONFIG('SYSTEM_STATE',$SYSTEM_STATE);
		//日期范围 通过状态下为不能登录的时间范围，阻止状态下为可登录的时间范围
		//CONFIG("SYSTEM_OpenDateRange",trim($_POST["opendateValue"],'|'));
		CONFIG('SYSTEM_CLOSE_TITLE',$SYSTEM_CLOSE_TITLE);
		
		CONFIG('SYSTEM_TITLE',$SYSTEM_TITLE);
		CONFIG('SYSTEM_COMPANY',$SYSTEM_COMPANY);
		CONFIG('SYSTEM_MEMO',$SYSTEM_MEMO);
		CONFIG('USER_LOGIN_VERIFY',$USER_LOGIN_VERIFY);
		//客服qq的设置
		CONFIG('TYPE_QQ',$_POST['TYPE_QQ']);
		//字段名
		$SERVICE_QQ='SERVICE_QQ_'.$_POST['TYPE_QQ'];
		$SERVICE_NO='SERVICE_QQ_'.abs(1-$_POST['TYPE_QQ']);
		CONFIG($SERVICE_QQ,$_POST[$SERVICE_QQ]);
		//清除为选择的设置
		CONFIG($SERVICE_NO,'');
		
		CONFIG('DEFAULT_USER_PASS1',$DEFAULT_USER_PASS1);
		CONFIG('DEFAULT_USER_PASS2',$DEFAULT_USER_PASS2);
		CONFIG('DEFAULT_USER_PASS3',$DEFAULT_USER_PASS3);
		//注册可见 注册必填 可修改项
		$infoarr=array();
		$showstr='';
		$editstr='';
		$requirestr='';
		$viewstr='';
		$truthstr='';
		foreach($_POST as $k=>$v)
		{
			if(strpos($k,'show_') !== false)
			{
			  $showstr.=",".$v;
			}
			if(strpos($k,'edit_') !== false)
			{
				$editstr.=",".$v;
			}
			if(strpos($k,'view_') !== false)
			{
				$viewstr.=",".$v;
			}
			if(strpos($k,'require_') !== false)
			{
				$requirestr.=",".$v;
			}
			if(strpos($k,'truth_') !== false)
			{
				$truthstr.=",".$v;
			}
		}
		if($showstr !='')
		{
		  $showstr=substr($showstr,1);
		}
		if($editstr !='')
		{
		  $editstr=substr($editstr,1);
		}
		if($viewstr !='')
		{
		  $viewstr=substr($viewstr,1);
		}
		if($requirestr !='')
		{
		  $requirestr=substr($requirestr,1);
		}
		$user=X('user');
		CONFIG('USER_REG_SHOW' ,$showstr);
		CONFIG('USER_EDIT_SHOW',$editstr);
		CONFIG('USER_VIEW_SHOW',$viewstr);
		CONFIG('USER_REG_REQUIRED',$requirestr);
		CONFIG('USER_TRUTH',trim($truthstr,","));
		$user->setatt('idEdit'  ,(isset($_POST['idEdit']) && $_POST['idEdit']=='1'));
		$user->setatt('idRand'  ,(isset($_POST['idRand']) && $_POST['idRand']=='1'));
		$user->setatt('idInDate',(isset($_POST['idInDate']) && $_POST['idInDate']=='1'));
		$user->setatt('idAutoEdit',(isset($_POST['idAutoEdit']) && $_POST['idAutoEdit']=='1'));
		//唯一设置
		$user->setatt('onlyMobile'  ,$_POST['only_mobile']);
		$user->setatt('onlyIdCard'  ,$_POST['only_id_card']);
		$user->setatt('onlyBankCard',$_POST['only_bank_card']);
		
		//注册编号生成设置
		$user->setatt('idSerial',$_POST['idSerial']);
		$user->setatt('idPrefix',$_POST['idPrefix']);
		$user->setatt('idLength',$_POST['idLength']);
		$this->saveAdminLog('',$_POST,"系统设置","系统参数设置");
		M()->commit();
        $this->success('修改完成',__URL__.'/sysedit');
	}
    
    
    
	//前台菜单设置
	public function userMenuEdit(){
		$menu=R("User/Menu/getmenudata",array($this->userobj));
        		foreach($menu as $k => $v){
            		$menus[$k] = $v['menus'];
        		}
		$this->assign('menu',$menus);
		$userMenuPower = $this->userobj->getatt('userMenuPower');
		$userNoSecPwd = $this->userobj->getatt('userNoSecPwd');
		$userNoSecPwd3 = $this->userobj->getatt('userNoSecPwd3');
		$this->assign('NoSecnum',count($userNoSecPwd));
		$userShortcutMenu = $this->userobj->getatt('userShortcutMenu');
		$this->assign('userMenuPower',$userMenuPower);
		$this->assign('userNoSecPwd',$userNoSecPwd);
		$this->assign('userNoSecPwd3',$userNoSecPwd3);
		$this->assign('userShortcutMenu',$userShortcutMenu);
		$this->assign('USER_PRIZE_SWITCH',CONFIG('USER_PRIZE_SWITCH'));
		$this->assign('USER_SHOP_SALEONLY',CONFIG('USER_SHOP_SALEONLY'));

		//二级密码超时时间
		$this->assign('USER_PASS_TIMEOUT',CONFIG('USER_PASS_TIMEOUT'));
		$this->assign('pwd3Switch',adminshow('pwd3Switch'));//判断是否开启了三级密码
		$this->assign('shop',X('user')->shopWhere != '');
		$this->assign('SHOW_SHOPSET',CONFIG('SHOW_SHOPSET'));
		$this->display();
	}
	//前台菜单设置更新
	public function userMenuUpdate(){
		M()->startTrans();
		if(!isset($_POST['shortcut'])){
		 $this->userobj->setatt('userShortcutMenu',array());
		}else{
		 $this->userobj->setatt('userShortcutMenu',$_POST['shortcut']);
		}
		//用户前台菜单权限 二级密码验证
		$this->userobj->setatt('userMenuPower',$_POST['level']);
		
		$menu=R("User/Menu/getmenudata",array($this->userobj));
		$menuArr = array();
		$menuNode = array();
		foreach($menu as $mk=>$mv){
			foreach($mv['menus'] as $v){
				$menuArr[]  = $v['model'].'-'.$v['action'].(isset($v['xpath'])?'-'.$v['xpath']:'');
				$menuNode[] = $v['model'].'-'.$v['action'];
			}
		}
		if(!isset($_POST['secPwd'])){
			$_POST['secPwd'] = array();
		}
		 if(!isset($_POST['secPwd3'])){
			$_POST['secPwd3'] = array();
		}
		
		$userNoSecPwd = array_diff($menuArr,$_POST['secPwd']);
		$userNoSecPwd3 = array_diff($menuArr,$_POST['secPwd3']);
		$this->userobj->setatt('userNoSecPwd',$userNoSecPwd);
		$this->userobj->setatt('userNoSecPwd3',$userNoSecPwd3);
		$this->userobj->setatt('userMenu',$menuNode);
		
		CONFIG('USER_PRIZE_SWITCH',$_POST['USER_PRIZE_SWITCH']);
		CONFIG('USER_PASS_TIMEOUT' ,intval($_POST['USER_PASS_TIMEOUT']));
		CONFIG('USER_SHOP_SALEONLY',isset($_POST['USER_SHOP_SALEONLY']) ? $_POST['USER_SHOP_SALEONLY'] : 0);
		M()->commit();
		$this->saveAdminLog('','',"前台菜单设置");
        		$this->success('修改完成',__URL__.'/sysedit');
	}
	//奖金参数设置更新
	public function tleupdate(){
		$cons = $this->getcons();
		M()->startTrans();
		foreach($cons as $con)
		{
			if(isset($_POST[$con['name']]))
			{
				if($con['type']=='date')
				{
					CONFIG($con['name'],strtotime($_POST[$con['name']]));
				}else if($con['type']=='select'){
					CONFIG($con['name'],$_POST[$con['name']]);
				}else{
					//判断提交的数据为数字 非数字则会变成0
					if(!is_numeric($_POST[$con['name']]) && $con['isnum']===true)
						$_POST[$con['name']]=0;
					CONFIG($con['name'],$_POST[$con['name']]);
				}
			}
			else
			{
				CONFIG($con['name'],$con['offvalue']);
			}
		}
		M()->commit();
		$this->saveAdminLog('','',"奖金参数设置");
		$this->success('奖金参数设置完成！');
	}

	//自动设置
	function autoSet($obj,$option=array()){
		M()->startTrans();
		foreach($obj as $k=>$v)
		{
			$newval=isset($_POST[$k])?$_POST[$k]:null;
			if($newval !== NULL)
			{
				if(gettype($v)=='string' || ((gettype($v)=='integer' || gettype($v)=='double') && is_numeric($newval)))
			   	{
			   		
			   		settype($newval,gettype($v));
			   		$obj->setatt($k,$newval);
			   	}
			   	
			   	if(gettype($v)=='boolean' && (strtolower($newval)=='true' || strtolower($newval) == 'false'))
			   	{
			   		if($newval=='true')
			   			$newval=true;
			   		else
			   			$newval=false;
			   		$obj->setatt($k,$newval);
			   	}
			}
		}
		M()->commit();
	}
	//登录口设置
	public function LoginTempSetup(){
		$template	= array();
		$path= ROOT_PATH.'DmsAdmin/Tpl/User/login/';
		if(!is_dir($path)) return;
		
		$handle		= opendir($path);
		$nowNum = CONFIG('DEFAULT_LOGIN_THEME')?CONFIG('DEFAULT_LOGIN_THEME'):"2";
		while(false!==($file = readdir($handle)))
		{
			$fileTime = date('Y-m-d H:i:s', filemtime($path . $file));
			if(is_dir($path . $file) && $file !="."&& $file !=".." && $file != '.svn'){
				$num	= $file;
                if($nowNum == $num){
                    $template['status'] = "1";
                }else{
                    $template['status'] = "0";
                }
				$path1	= APP_PATH."Tpl/User/login/".$num.'/preview.jpg';
				$template['path']	= $path1;
				$template['number'] = $num;
				$template['fileTime'] = $fileTime;
				$template['description'] = '[暂无]';
				//$template['catalog'] = $path.$num."/";
				$template['catalog'] = $num;
				$info[]	= $template;
			}
		}
		$this->assign('USER_LOGIN_URL',CONFIG('USER_LOGIN_URL'));
		
		$info = $this->tsort($info);
		$this->assign('info',$info);
		$this->display();
	}
	//登录口预览
	public function viewLoginTemp(){
		if(isset($_SESSION[C('USER_AUTH_KEY')])) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION[C('USER_AUTH_NUM')]);
            unset($_SESSION[C('PWD_SAFE')]);
			unset($_SESSION[C('SAFE_PWD')]);
			unset($_SESSION['logintype']);
			unset($_SESSION['username']);
			unset($_SESSION['ip']);
        }
		$this->redirect('User/Public/login',array('loginTempNumber'=>$_GET['number']));
	}
	//模版主题设置
	public function ThemeTempSetup(){		
		$themePath = TMPL_PATH.'User/';
		$nowTheme = CONFIG('DEFAULT_THEME')?CONFIG('DEFAULT_THEME'):'default_sj';
		if(is_dir($themePath)){
			$themeName = array();
			$handle1		= opendir($themePath);
			while(false!==($filename = readdir($handle1))){
				if(is_dir($themePath.$filename) && $filename!='.' && $filename!='..' && $filename!='login' && $filename != '.svn' && $filename != 'core'){
					$themeTime = date('Y-m-d H:i:s', filemtime($themePath . $filename));
					if($nowTheme==$filename){
						$themeName['status'] = "1";
					}else{
						$themeName['status'] = "0";
					}
					$themeName['name'] = $filename;
					$themeName['path']	= $themePath;
					$themeName['themeTime'] = $themeTime;
					$themeName['description'] = '[暂无]';
					//$themeName['catalog'] = ROOT_PATH.'DmsAdmin/Tpl/User/'.$filename."/";
					$themeName['catalog'] = $filename;
					$themeInfo[]	= $themeName;
				}
			}
		}
		$this->assign('theme',$themeInfo);
        $this->assign('nowTheme',$nowTheme);
		$this->display();
	}
	
	// 排序
	private function tsort($ary){
        for($i=0; $i<count($ary) ;$i++){
            for($j=0; $j<$i; $j++){
                if($ary[$i]['number'] < $ary[$j]['number']){
                    $temp = $ary[$i];
                    $ary[$i] = $ary[$j];
                    $ary[$j] = $temp;
                }
	        }
        }
        return $ary;
    }
	//更换登陆入口
	public function tempChange()
	{
		if(isset($_REQUEST['number']) && $_REQUEST['number']!='')
		{
			$number	= $_REQUEST['number'];
			M()->startTrans();
			CONFIG('DEFAULT_LOGIN_THEME',$number);
			M()->commit();
			$this->ajaxReturn('0','设置成功！',1);
		}
		else
		{
			$this->ajaxReturn('0','设置失败！',0);
		}
	}
	//设置登录口地址
	public function loginUrl(){
		if($_POST['USER_LOGIN_URL']=='')
		{
			$this->error('请输入指定登录的地址!');
		}else{
			//判断是否输入正确 正则
			$result=preg_match("/^(https|http):\/\//",$_POST['USER_LOGIN_URL']);
			if($result==0){
				$this->error("请填写正确地址");
			}
			M()->startTrans();
			CONFIG('USER_LOGIN_URL',$_POST['USER_LOGIN_URL']);
			M()->commit();
			$this->success('修改成功!');
		}
	}
	// 更换主题
	public function themeChange(){
		if(isset($_REQUEST['themename']) && $_REQUEST['themename']!='')
		{
			$themename	= $_REQUEST['themename'];
			M()->startTrans();
			CONFIG('DEFAULT_THEME',$themename);
			M()->commit();
			$this->ajaxReturn('1','设置成功！',1);
		}
		else
		{
			$this->ajaxReturn('1','设置失败！',0);
		}
	}

	public function prizeEdit(){
		$userdata=array();
		$prizedata=array();
		foreach(X('tle') as $tleobj)
		{
			foreach(X('prize_*',$tleobj) as $p)
			{
				if(get_class($p)!='prize_sql')
				{
					$prizedata[]=array('name'=>$p->byname,'class'=>get_class($p),'xpathmd5'=>md5($p->objPath()),'use'=>$p->use,'startDate'=>$p->startDate,'endDate'=>$p->endDate);
				}
			}
		}
		if(count($prizedata)>0)
		$userdata[$this->userobj->name]=$prizedata;
		
		$this->assign('data',$userdata);
		$this->display();
	}
	public function prizeEditSave(){
		M()->startTrans();
		foreach(X('tle') as $tleobj)
		{
			foreach(X('prize_*',$tleobj) as $p)
			{
				/*
					没有开放prize_layer的原因是
					本来能产生层碰的用户因关闭后没执行.层碰数据没生成
					后期开放本奖金.就会导致补发.
					但是一般客户的意思为.关闭期间碰过的..视为碰过
				*/
				if(get_class($p)!='prize_sql' && get_class($p)!='prize_layer'){
					$pmd5=md5($p->objPath());
					if(isset($_POST[$pmd5.'_use']))
					{
						$p->setatt('use',$_POST[$pmd5.'_use']=='true');
						if($_POST[$pmd5.'_use']=='true')
							$edit_data[$p->byname.'开启']="开启";
						else
							$edit_data[$p->byname.'开启']="关闭";
						//设置开始日期
						if($_POST[$pmd5.'_start']=='')
						{
							$p->setatt('startDate',0);
							$edit_data[$p->byname.'开始']=0;
						}
						else
						{
							$p->setatt('startDate',strtotime($_POST[$pmd5.'_start']));
							$edit_data[$p->byname.'开始']=strtotime($_POST[$pmd5.'_start']);
						}
						//设置结束日期
						if($_POST[$pmd5.'_end']=='')
						{
							$p->setatt('endDate',0);
							$edit_data[$p->byname.'结束']=0;
						}
						else
						{
							$p->setatt('endDate',strtotime($_POST[$pmd5.'_end']));
							$edit_data[$p->byname.'结束']=strtotime($_POST[$pmd5.'_start']);
						}
					}
				}
			}
		}
		M()->commit();
		$this->saveAdminLog('',$edit_data,"奖金开关设置");
		$this->success('设置完成');
	}
	//系统使用书名书下载
	function system_do_info(){
	   $this->display();
	}
	function doaddfile(){
        $filename = '管理系统使用说明书.pdf';
        $pathfile = $_SERVER['DOCUMENT_ROOT'].'/Public/shiyongshuoming.pdf';
		
        $file = fopen($pathfile, "r"); // 打开文件
        // 输入文件标签
		if(Extension_Loaded('zlib')){Ob_Start('ob_gzhandler');}
        header('Content-Encoding: none');
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($pathfile));
        header('Content-Transfer-Encoding: binary');
        header("Content-Disposition: attachment; filename=" . $filename);  //以真实文件名提供给浏览器下载
        header('Pragma: no-cache');
        header('Expires: 0');
        //输出文件内容
        echo fread($file,filesize($pathfile));
        fclose($file);
		if(Extension_Loaded('zlib')) Ob_End_Flush(); 
	}
}
?>