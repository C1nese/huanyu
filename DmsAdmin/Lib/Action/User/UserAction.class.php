<?php
defined('APP_NAME') || die(L('not_allow'));
class UserAction extends CommonAction {

	//修改密码
	public function setPass()
	{		
		$this->assign('changePwdsmsSwitch',CONFIG('changePwdsmsSwitch'));
		$this->assign('changePwdsmsContent',CONFIG('changePwdsmsContent'));
		$this->assign('verificateSwitch',CONFIG('verificatesmsSwitch'));
		$this->assign('verificatesmsContent',CONFIG('verificatesmsContent'));
		$this->assign('changePwdmailSwitchyanzheng',CONFIG('changePwdmailSwitchyanzheng'));
		$this->assign('changePwdmailContentyanzheng',CONFIG('changePwdmailContentyanzheng'));
		$this->assign('pwd3Switch',adminshow('pwd3Switch'));
		$this->display();
	}

	public function passSave()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
		B('XSS');
		$pass1			= trim($_POST['pwd1']);
		$pass2			= trim($_POST['pwd2']);
		$pass3			= isset($_POST['pwd3'])?trim($_POST['pwd3']):'';
		$repass1		= trim($_POST['repwd1']);
		$repass2		= trim($_POST['repwd2']);
		$repass3		= isset($_POST['repwd3'])?trim($_POST['repwd3']):'';
		//一级密码输入验证
		if(strlen($pass1)>0){
			if($pass1 !== $repass1){
				$this->error(L('两次输入的一级密码不一致！'));
			}elseif(strlen($pass1)<6){
				$this->error(L('登录密码长度不能小于6位！'));
			}else{
				$map['pass1']  =md100($pass1);
			}
		}
		$pwd3Switch=adminshow('pwd3Switch');
		//二级密码输入验证
        if(strlen($pass2)>0){
            if($pass2 !== $repass2){
                $this->error(L('两次输入的二级密码不一致！'));
            }elseif(strlen($pass2)<6){
                $this->error(L('交易密码长度不能小于6位！'));
            }else{
                $map['pass2']  = md100($pass2);
            }
        }
		//三级密码输入验证
		if($pwd3Switch && $pass3 !== $repass3){
			$this->error(L('两次输入的三级密码不一致！'));
		}elseif($pwd3Switch && strlen($pass3)<6){
			$this->error(L('三级密码长度不能小于6位！'));
		}elseif($pwd3Switch){
			$map['pass3']  = md100($pass3);
		}
		
        if(empty($map)){
            $this->error(L('您未输入任何信息！'));die;
        }
		$isyanzheng = CONFIG('verificatesmsSwitch');
		if($isyanzheng){
			$verify = S($this->userinfo['编号'].'_修改密码');
			if(!$verify || $verify != intval($_POST['repwdSms']) || !$_POST['repwdSms']){
				$this->error(L('短信验证码错误或已过期!'));
			}
		}
		$isyanzhengmail = CONFIG('changePwdmailSwitchyanzheng');
		if($isyanzhengmail){
			$verify = S($this->userinfo['编号'].'_修改密码');
			if(!$verify || $verify != intval($_POST['repwdMail']) || !$_POST['repwdMail']){
				$this->error(L('短信验证码错误或已过期!'));
			}
		}
		M()->startTrans();
		$where['id']	= $this->userinfo['id'];
		$rs	= M('用户')->where($where)->save($map);
		if($rs === false){
			M()->rollback();
			$this->error(L('修改失败！'));
		}elseif($rs===0){
			M()->rollback();
			$this->error(L('密码没有发生变化！'));
		}else{
			//写入用户操作日志
			$authInfo['姓名']=$this->userinfo['姓名'];
			$authInfo['编号']=$this->userinfo['编号'];
			$authInfo['id']=$this->userinfo['id'];
			$data = array();
			$datalog['user_id']=$authInfo['id'];
			$datalog['user_name']=$authInfo['姓名'];
			$datalog['user_bh']=$authInfo['编号'];
			$datalog['ip']=$_SESSION['ip'];
			$datalog['content']='用户修改密码';
			$datalog['create_time']=time();
			//获取用户的IP地址
			import("ORG.Net.IpLocation");
			$IpLocation				= new IpLocation("qqwry.dat");
			$loc					= $IpLocation->getlocation();
			$country				= mb_convert_encoding ($loc['country'] , 'UTF-8','GBK' );
			$area					= mb_convert_encoding ($loc['area'] , 'UTF-8','GBK' );
			$datalog['address']		= $country.$area;
			M('log_user')->add($datalog);
			//写入用户操作日志结束
           S($this->userinfo['编号'].'_修改密码',null,300);
			//注册短信发送
			$user_mm = $authInfo['编号'];
	        $udata = M('用户')->where("编号 = '$user_mm'")->find();
	        $udata['一级新密码']=$pass1;
	        $udata['二级新密码']=$pass2;
			sendSms("changePwd",$this->userinfo['编号'],'用户修改密码',$udata);
			//用户修改密码发送邮件
			if(CONFIG('changePwdmailSwitch')){
	           	sendMail($udata,$this->userobj->byname.'修改密码',CONFIG('changePwdmailContent'));
			}
            /*btx 同步密码到慕悦集 start*/
            $data['username'] = $udata['编号'];
            if(!empty($pass1)){
                $data['loginPwd'] = $pass1;
            }else{
            	$data['loginPwd'] = 123456;//发现同步注册不成功，修改密码失败
            }
            if(!empty($pass2)){
                $data['payPwd'] = $pass2;
            }else{
            	$data['payPwd'] = 123456;//修改密码时再把数据穿过去，重新注册
            }
            $data['tj_no'] = $udata['推荐_上级编号'];//如果慕悦集没有就重新注册
            $res = json_decode(cCurlInit(C('PASSWORD_URL'),$data));
            if($res->code != 200){
                M()->rollback();
                $this->error(L('密码修改失败！'.$res->code));
            }
            /*btx 同步密码到慕悦集 end*/
			//用户修改密码完成
			M()->commit();
			$this->success(L('修改成功！'));
		}
	}
	//资料修改
	public function edit()
	{
		//获得注册参数设置
		$require = explode(',',CONFIG('USER_REG_REQUIRED'));
		$show    = explode(',',CONFIG('USER_VIEW_SHOW'));
	    $edit    = explode(',',CONFIG('USER_EDIT_SHOW'));
		$Bank	= M('银行卡');
		$banklist	= $Bank->order('id asc')->select();
		$this->assign('banklist',$banklist);
		import("COM.Mobile.NumCheck");
		$this->assign('NumCheck',NumCheck::$data);
		$this->assign('edit',$edit);
		$this->assign('require',$require);
		$this->assign('show',$show);
		$this->display();
	}

	//资料查看
	public function view()
	{
		$show=explode(',',CONFIG('USER_VIEW_SHOW'));
		$this->assign('show',$show);
		$this->display();
	}
	
	
	    //上传产品图片
    public function UploadPhoto()
    {
    	$this->assign('id',$_GET['id']);
        $this->display();
    }
    //上传产品图片保存
	public function UploadPhotoSave(){
		$upload = A('Admin://Public');
		$result=$upload ->uploadhead();
	}
	
	public function update() 
    {
    	//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
		B('XSS');
		$model		= M('用户');
		$data		= array(); //待修改的数据
		$fieldList	= array(
			"name"				=>"姓名",
			"reciver"			=>"收货人",
			"alias"				=>"别名",
			"sex"				=>'性别',
			"id_card"			=>"证件号码",
			"bank_apply_name"	=>"开户银行",
			"bank_apply_addr"	=>"开户地址",
			"bank_card"			=>"银行卡号",
			"bank_name"			=>"开户名",
			"email"				=>"email",
			"qq"				=>"QQ",
			"mobile"			=>"移动电话",
			"country"			=>"国家",
			"province"			=>"省份",
			"city"				=>"城市",
			"county"			=>"地区",
			"town"		    	=>"街道",
			"address"			=>"地址",
			"qq"				=>'QQ',
			"pass1"				=>'pass1',
			"pass2"				=>'pass2',
			"weixin"				=>'微信账号',
			"secretsafe_name"	=>'密保问题',
			"secretanswer"		=>'密保答案',
		);
		//判断是否为必填
        $edit=explode(',',CONFIG('USER_EDIT_SHOW'));
		$requirearr=explode(',',CONFIG('USER_REG_REQUIRED'));
		//遍历fun_val得到必填的fun_val
		foreach(X('fun_val') as $funval){
			if($funval->required == "true" && $funval->regdisp){
				$requirearr[] = $funval->name;
				$edit[] = $funval->name;
			}
			$fieldList[$funval->name] = $funval->name;
		}
		foreach($requirearr as $requireinfo)
		{
            if(isset($_POST[$requireinfo]) && in_array($requireinfo,$edit) && $_POST[$requireinfo]=='')
			{
				 $this->error(L('请填写').L($fieldList[$requireinfo]).L('信息'));
			}
		}
		//处理手机号校验
		import("COM.Mobile.NumCheck");
		if(in_array('country_code',$requirearr) && NumCheck::check($_POST['mobile'],$_POST['country_code']))
		{
			$this->error('您的移动电话格式不正确');
		}
		/*btx 后台可修改项选择省市区域 start*/
		if(in_array('area',$edit)){
			$edit[] = 'country';
			$edit[] = 'province';
			$edit[] = 'city';
			$edit[] = 'county';
			$edit[] = 'town';
		}
		/*btx 后台可修改项选择省市区域 end*/
		foreach( $_POST as $key => $val )
		{
			if(!in_array($key,$edit)) continue;//防止非法添加表单
			foreach( $fieldList as $fkey=> $filed)
			{
				if( $fkey == $key )
				{
					$data[ $filed ] = safe_replace($val);
					if($filed=='pass2') unset($data[$filed]);
				}
			}
		}
		$where['id']	= $_SESSION['userid'];
		$updateuser = $model->find($_SESSION['userid']);
		if(isset($_POST['pass2'])){
			$userDate = $model->where($where)->find();
			if(!chkpass($_POST['pass2'],$userDate['pass2'])){
				$this->error(L('二级密码错误'));
			}
		}
		if (!empty($_FILES['image']['name'])){
			$upload = A('Admin://Public');
			$result=$upload ->uploadhead();
			if($result['error']==1){
				$this->error(L('头像上传失败'));
			}else{
				$data['头像']=$result['url'];
			}
		}
		if($_POST['gexingq']!=$userDate['签名']){
			$data['签名']=$_POST['gexingq'];
		}
		M()->startTrans();
		$res = $model->where($where)->save($data);
		if($res !== false){
			$updateuser['修改时间'] = systemTime();
			$updateuser['ip']   = get_client_ip();
			$updateuser['userid']   = $updateuser['id'];
			unset($updateuser['id']);
			//$updateuser['logid']   = $logid;
			M('修改日志')->add($updateuser);
			//写入用户操作日志
			$authInfo['姓名']=$this->userinfo['姓名'];
			$authInfo['编号']=$this->userinfo['编号'];
			$authInfo['id']=$this->userinfo['id'];
			$data = array();
			$datalog['user_id']=$authInfo['id'];
			$datalog['user_name']=$authInfo['姓名'];
			$datalog['user_bh']=$authInfo['编号'];
			$datalog['ip']=$_SESSION['ip'];
			$datalog['content']='用户修改资料';
			$datalog['create_time']=time();
			//获取用户的IP地址
			import("ORG.Net.IpLocation");
			$IpLocation				= new IpLocation("qqwry.dat");
			$loc					= $IpLocation->getlocation();
			$country				= mb_convert_encoding ($loc['country'] , 'UTF-8','GBK' );
			$area					= mb_convert_encoding ($loc['area'] , 'UTF-8','GBK' );
			$datalog['address']		= $country.$area;
			M('log_user')->add($datalog);
			//写入用户操作日志结束
			M()->commit();
			$this->success(L('success_edit_msg'));
		}else{
			M()->rollback();
			$this->error(L('修改失败'));
		}
	}
    // 我的用户订单
    function myreg(){
	 //循环所有的注册订单
		$baodanleibie	= array();
		foreach(X('sale_reg') as $sale){
			$baodanleibie[] =  "'".$sale->name."'";
		}
		$baodan_string = implode(',',$baodanleibie);
        $list = new TableListAction('报单');
		$list ->table('dms_报单 as a');
        //$list ->field('时间,来源,金额,余额,类型,备注')->where("编号=$this->userinfo['编号']"));
        //$list->join("dms_用户 as b on b.编号=a.编号")->where("(报单中心编号='{$this->userinfo['编号']}' or a.注册人编号='{$this->userinfo['编号']}') and a.报单类别 in ({$baodan_string})");
		$where = "(报单中心编号='{$this->userinfo['编号']}' or a.注册人编号='{$this->userinfo['编号']}') and a.报单类别 in ({$baodan_string})";
        if(!empty($_POST['name'])){
            $name = trim($_POST['name']);
            if(strlen($name) == 8){
                $where .= " AND b.编号 = '".$name."'";
            }else{
                $where .= " AND b.姓名 = '".$name."'";
            }
        }
        $list->join("dms_用户 as b on b.编号=a.编号")->where($where);
		$list->order("a.id desc");
		$fieldStr = '';
		foreach(X('net_rec') as $netRec){
			$fieldStr .= 'b.'.$netRec->name.'_上级编号,';
		}
		foreach(X('net_place') as $netPlace){
			$fieldStr .= 'b.'.$netPlace->name.'_上级编号,';
		}
		foreach(X('levels') as $level){
			$fieldStr .= 'b.'.$level->name.',';
		}

		$fieldStr = trim($fieldStr,',');
        $list->field("b.编号,b.注册日期,b.姓名,{$fieldStr},a.*");
		
        $list->addshow(L('编号'),array("row"=>'<a href="__URL__/viewMyreg/id/[id]" style="color:#EC0C0A">[编号]</a>'));
        $list->addshow(L('姓名')     ,array("row"=>"[姓名]"));
		$list->addshow(L('订单状态') ,array('row'=>'[报单状态]'));
		//$list->addshow(L('物流状态') ,array('row'=>'[物流状态]'));
		$list->addshow(L('付款日期') ,array('row'=>'[到款日期]',"format"=>"time"));
		//$list->addshow(L('报单状态') ,array('row'=>'[报单状态]'));
		$list->addshow(L('报单金额') ,array('row'=>'[报单金额]'));

        $list->addshow(L('注册日期'),array("row"=>"[注册日期]","format"=>"time"));

		foreach(X('levels') as $level){
			$list->addshow(L($level->byname),array("row"=>array(array(&$this,"printUserLevel"),"[{$level->name}]",$level->name)));
		}
        
		foreach(X('net_rec') as $netRec){
			$list->addshow(L($netRec->byname).'人', array("row"=>'['.$netRec->name.'_上级编号]'));
		}
		foreach(X('net_place') as $netPlace){
			$list->addshow(L($netPlace->byname).'人',array("row"=>'['.$netPlace->name.'_上级编号]'));
		}
		$list->addshow(L('操作'),array("row"=>array(array(&$this,'getVeiwDo'),'[物流状态]','[id]',$this->userobj->haveProduct())));
		
        $data = $list->getData();
        $this->assign('data',$data);
        
		$this->display();
    }
    
    
    	
	//商务中心订单
	public function fwsale(){
		//循环所有的注册订单
		$baodanleibie	= array();
		foreach(X('sale_reg') as $sale){
			$baodanleibie[] =  "'".$sale->name."'";
		}
		$baodan_string = implode(',',$baodanleibie);
		 $list = new TableListAction('报单');
		$list ->table('dms_报单 as a');
        //$list ->field('时间,来源,金额,余额,类型,备注')->where("编号=$this->userinfo['编号']"));
        $list->join("dms_用户 as b on b.编号=a.编号")->where("(报单中心编号='{$this->userinfo['编号']}') and a.报单类别 in ({$baodan_string})");
		$list->order("a.id desc");
		$fieldStr = '';
		foreach(X('net_rec') as $netRec){
			$fieldStr .= 'b.'.$netRec->name.'_上级编号,';
		}
		foreach(X('net_place') as $netPlace){
			$fieldStr .= 'b.'.$netPlace->name.'_上级编号,';
		}
		foreach(X('levels') as $level){
			$fieldStr .= 'b.'.$level->name.',';
		}

		$fieldStr = trim($fieldStr,',');
        $list->field("b.编号,b.注册日期,b.姓名,{$fieldStr},a.*");
		
        $list->addshow(L('编号'),array("row"=>'<a href="__URL__/viewMyreg/id/[id]" style="color:#EC0C0A">[编号]</a>'));
		$list->addshow(L('订单状态') ,array('row'=>'[报单状态]'));
		//$list->addshow(L('物流状态') ,array('row'=>'[物流状态]'));
		$list->addshow(L('付款日期') ,array('row'=>'[到款日期]',"format"=>"time"));
		//$list->addshow(L('报单状态') ,array('row'=>'[报单状态]'));
		$list->addshow(L('报单金额') ,array('row'=>'[报单金额]'));

        $list->addshow(L('注册日期'),array("row"=>"[注册日期]","format"=>"time"));
        $list->addshow(L('姓名')     ,array("row"=>"[姓名]"));

		foreach(X('levels') as $level){
			$list->addshow(L($level->byname),array("row"=>array(array(&$this,"printUserLevel"),"[{$level->name}]",$level->name)));
		}
        
		foreach(X('net_rec') as $netRec){
			$list->addshow(L($netRec->byname).'人', array("row"=>'['.$netRec->name.'_上级编号]'));
		}
		foreach(X('net_place') as $netPlace){
			$list->addshow(L($netPlace->byname).'人',array("row"=>'['.$netPlace->name.'_上级编号]'));
		}
		$list->addshow(L('操作'),array("row"=>array(array(&$this,'getVeiwDo'),'[物流状态]','[id]',$this->userobj->haveProduct())));
		
        $data = $list->getData();
        $this->assign('data',$data);
        
		$this->display();
	}
    
	public function getVeiwDo($status,$id,$haveProduct){
		if($status == '未发货' && $haveProduct){
			 return '&nbsp;<a href="__URL__/viewMyreg/id/'.$id.'" >查看</a>';//&nbsp;&nbsp;&nbsp;<a href="__URL__/sended:__XPATH__/id/'.$id.'" style="color:#e4cc9c">发货</a>&nbsp;
		 }else{
			return '&nbsp;<a href="__URL__/viewMyreg/id/'.$id.'"  >查看</a>&nbsp;';
		 }
	}

	public function sended(){
		$saledata = M("报单")->find($_GET["id"]);
		$userid =$saledata['编号'];
		$status = $saledata['物流状态'];
		if($status == "已发货"){
			$this->error("此订单已发货,不可再发货!");
		}elseif($status == '已收货'){
			$this->error('此订单已收货,不可再发货!');
		}else{
			M()->startTrans();
			$result=M("报单")->where(array('id'=>$_GET["id"]))->save(array('物流状态'=>'已发货','发货日期'=>systemTime()));
			if($result){
				M()->commit();
				$this->success("发货成功！");
			}else{
				M()->rollback();
				$this->error("发货失败！");
			}
		}
	}
	//查看我的用户订单
	public function viewMyreg(){
		$saleData = M('报单')->where("(报单中心编号='{$this->userinfo['编号']}' or 注册人编号='{$this->userinfo['编号']}') and id={$_GET['id']}")->find();
		if($saleData['产品'] == 1){
			$productData = M('产品订单')->where(array('报单id'=>$_GET['id']))->select();
			$this->assign('productData',$productData);
		}
		$this->assign('name',$this->userobj->byname);
		$this->assign('saleData' ,$saleData);
		$this->assign('adminshow',adminshow('sale_pv'));
		$this->display();
	}
    //我的订单
    function mysale(){
		
        $list=new TableListAction("报单");
        $list ->where("编号 = ".$this->userinfo["编号"]);
        $list ->order('id desc');
        $list ->setShow = array(
             L('购买日期') => array("row"=>'[购买日期]','format'=>'time'),
             L('付款日期') => array("row"=>'[到款日期]','format'=>'time'),
             L('来源编号') => array('row'=>'[来源编号]'),  
        );
        $list ->pagenum=15;        
       
        $data = $list->getData();
        $this->assign('data',$data);
        $this->display();
       
    }

	//公告列表
	public function viewNotice()
	{
		$list	= new TableListAction('公告');
		$where="语言='".C('DEFAULT_LANG')."' and (查看权限=0 or 查看权限={$this->userinfo['id']}";
		foreach(X('net_rec') as $net){
			$netshuju = $this->userinfo[$net->name.'_网体数据'];
			$where .= " or find_in_set(查看权限,'{$netshuju}')";
		}
		foreach(X('net_place') as $net){
			$netshuju = $this->userinfo[$net->name.'_网体数据'];
			foreach($net->getcon("region",array("name"=>""),false) as $region){
				$netshuju = str_replace('-'.$region['name'],'',$netshuju);
			}
			$where .= " or find_in_set(查看权限,'{$netshuju}')";
		}
		$where .=')';
		
//        $list->field('id,标题,创建时间')->where($where)->order("id desc");
//        $data = $list->getData();
        $list->field('id,标题,内容,创建时间,FROM_UNIXTIME(创建时间, "%Y") AS years')->where($where)->order("创建时间 desc");
        $data = $list->getData();
        $dalist = array();
        foreach ($data['list'] as $key=>$val){
            $dalist[$val['years']]['years'] = $val['years'];
            $val['内容'] = substr(strip_tags($val['内容']), 0, 200);
            $dalist[$val['years']]['note'][] = $val;
        }
        $data['list'] = $dalist;
        $this->assign('data',$data); 
		$this->display('viewNotice');
	}
	//公告查看
	public function showNotice()
	{
        $list = M('公告') ->where(array("id"=>$_GET['id']))->find();
		if($list['查看权限']!=0 && $list['查看权限']!=$this->userinfo['id']){
			$net = X('*@'.$list['netname']);
			$netshuju = $this->userinfo[$net->name.'_网体数据'];
			if(get_class($net) == 'net_place'){
				foreach($net->getcon("region",array("name"=>""),false) as $region){
					$netshuju = str_replace('-'.$region['name'],'',$netshuju);
				}
			}
			
			if(!in_array($list['查看权限'],explode(',',$netshuju))){
				$this->error('无权限查看');
			}
		}
		/*btx 首页联盟推荐产品数据 start*/
	           $recommend1 = M("推荐")->field('名称,描述,图片,价格,url,score')->where('分类=1 and 状态=0')->limit(10)->select();
	           $this->assign('recommend1',$recommend1);
	           /*btx 首页联盟推荐产品数据 end*/
		$this ->assign('list',$list);  
		$this->display('showNotice');
	}
	/**/
	public function getSpreadCode()
	{
		$sales=X('sale_reg');
		$map['id']=$_SESSION[C('USER_AUTH_KEY')];
		$this->userinfo=M('用户')->where($map)->find();
		//$servername=$_SERVER['SERVER_NAME'];
		//$rec=base64_encode(serialize($this->userinfo["编号"]));
		$link = U('User/Saleweb/usereg:'.$regpath.'?rec='.$rec,'',true,false,true);
		$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
		$link = $http_type.$_SERVER['HTTP_HOST'];
		$link .='/?'.$this->userinfo["编号"];
		
		$this->assign('link',$link);
		$this->display();
	}
	
}
?>