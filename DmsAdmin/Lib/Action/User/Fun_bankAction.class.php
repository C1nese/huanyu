<?php
defined('APP_NAME') || die('不要非法操作哦');
class Fun_bankAction extends CommonAction {
	// 电子货币明细
	public function index(fun_bank $fun_bank)
	{
        $list = new TableListAction($fun_bank->name.'明细');
        //$list ->field('时间,来源,金额,余额,类型,备注')->where("编号=$this->userinfo['编号']"));
        $list ->where(array('编号'=>$this->userinfo['编号']))->order("时间 desc,id desc");
        //$list ->setSearch = array('time'=>array('row'=>'时间','exp'=>'gt'),'leavemoney'=>array('row'=>'余额'));
        $list ->setShow = array(
            L('时间')=>array("row"=>"[时间]","format"=>"time"),
            L('来源')=>array("row"=>"[来源]"),
            L('金额')=>array("row"=>"[金额]"),
            L('余额')=>array("row"=>"[余额]"),
            L('类型')=>array("row"=>"[类型]"),
            L('备注')=>array("row"=>"[备注]"),
        );
        $list->pagenum = 15;
        $this ->assign('name',$fun_bank->byname);
        $this ->assign('list',$list);
        $data = $list->getData();
        $this->assign('data',$data);
		$this->display();
	}
	// 现金提现
    public function get(fun_bank $fun_bank){
        if(!$fun_bank->getMoney || !$fun_bank->use){
            $this->error(L($fun_bank->byname).L('未开启'));
        }else{
        	//判断是否可以提现
        	if(in_array(date('w',systemTime()),$fun_bank->getMoneyWeek)){
        		$this->error("周".str_replace("0","7",implode(",",$fun_bank->getMoneyWeek))."不能提现".$fun_bank->byname);
        	}
        	if($fun_bank->getMoneyMday!='' && in_array(date('j',systemTime()),explode(",",$fun_bank->getMoneyMday))){
        		$this->error("每月的".$fun_bank->getMoneyMday."号不能提现".$fun_bank->byname);
        	}
            $list = new TableListAction("提现");
            $list ->where(array('编号'=>$this->userinfo['编号'],'类型'=>$fun_bank->name))->order("id desc");
            $list ->setShow = array(
                L('时间')=>array("row"=>"[操作时间]","format"=>"time"),
                L('开户行')=>array("row"=>"[开户行]"),
                L('开户名')=>array("row"=>"[开户名]"),
                L('银行卡号')=>array("row"=>"[银行卡号]"),
            );
			$list ->addShow(L('提现额'),array("row"=>"[提现额]"));
			if($fun_bank->isShowRadio && $fun_bank->getMoneyRatio!=1){//这里isShowRadio没用到
				$list ->addShow(L('原始提现额'),array("row"=>"[实发]"));
				$list ->addShow(L('实发额'),array("row"=>"[换算后实发]"));
			}else{
				$list ->addShow(L('实发'),array("row"=>"[实发]"));
			}
			$list ->addShow(L('状态'),array("row"=>array(array(&$this,"dofun1"),"[状态]",'[撤销理由]')));
			if($fun_bank->allowBack_apply){
			$list ->addShow(L('撤销状态'),array("row"=>array(array(&$this,"dofun11"),"[撤销申请]")));
			}
			$list ->addShow(L('操作'),array("row"=>array(array(&$this,"dofun"),"[状态]","[id]",$fun_bank->objPath(),$fun_bank,"[撤销理由]")));
            $data = $list->getData();
            /*对getOnly的处理，如果已存在未审核提现，则不能继续提现*/
            if($fun_bank->getOnly && M('提现')->where(array('编号'=>$this->userinfo['编号'],'状态'=>0))->find())
            {
            	$this->assign('onlyLock',true);
            }
            $this->assign('data',$data);
            $this->assign('user',$this->userobj->byname);
            $this->assign('name',$fun_bank->byname."提现");  // 货币名称
            $this->assign('bank',$fun_bank);
            $this->display();
        }
    }
    //显示状态
    function dofun1($status,$memo)
    {
        if($status == 0){
            return L('未审核');
        }elseif($status == 1){
            return "<a href='javascript:alert(\"撤销理由：".$memo."\")'>".L('已撤销').'</a>';
        }elseif($status == 3){
            return L('已发放');
        }else{
            return L('已审核');
        }
    }
     function dofun11($status)
    {
    	
        if($status == 1){
            return L('申请中');
        }if($status == 2){
            return L('已同意撤销');
        }if($status == 3){
            return L('拒绝撤销');
        }else{
           return L('未进行撤销申请');
        }
        
           }
    function dofun($str,$str1,$str2,$bank,$memo){
		
        if($str == 0){
            return '<a href="__URL__/getcancel:__XPATH__/id/'.$str1.'"  callback="delete_done">'.L('revoke').'</a>';
        }elseif($str == 1){
        	if($memo != '')
        	{
            	return '<a href="javascript:alert(\'撤销理由：'.$memo.'\')"  callback="delete_done">查看理由</a>';
            }
        }
    }
    // 提现撤销
    public function getcancel(){
        M()->startTrans();
        $getModel = M("提现");
        $where['id'] = $_GET["id"];
        $where['编号'] = $this->userinfo['编号'];
        $re = $getModel -> where($where)->find();
        $bank	= X('fun_bank@'.$re['类型']);
		if(!$bank->allowBack){
			$this->error(L('此货币不允许撤销!'));
		}
	   if($bank->allowBack_apply){
			//增加货币提现的是否被允许撤销的申请
			if($re && $re["撤销申请"] == "0"){
	            $data["id"] =$_GET["id"];
	            $data["撤销申请"] = '1';
	            $data["操作时间"] = systemTime();
	            $res = $getModel->save($data);
	            if($res){
	                $this->success(L('已进入撤销申请,会尽快回复'),__URL__.'/get:__XPATH__');
	            } else{
	                $this->error(L('审核失败'));
	            }
	        }
	       if($re && $re["撤销申请"] == "1"){
	          $this->error(L('撤销申请已提交过,请耐心等待'));
	       }
	       if($re && $re["撤销申请"] == "3"){
	          $this->error(L('已拒绝您的撤销申请,不能再进行撤销'));
	       }
		}
		else{
        if($re && $re["状态"] == "0"){
            $data["id"] =$_GET["id"];
            $data["状态"] = '1';
            $data["审核时间"] = systemTime();          
            $res = $getModel->save($data);
            if($res){
            	$bank->set($this->userinfo['编号'],$this->userinfo['编号'],$re["提现额"],'撤销提现','撤销提现返还：'.$re["提现额"]);
            	M()->Commit();
                $this->success(L('撤销成功'),__URL__.'/get:__XPATH__');
            } else{
                $this->error(L('撤销失败'));
            }
        }
       }
    }
    // 保存提现信息
    public function getSave(fun_bank $fun_bank){
    	//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
		B('XSS');
		M()->startTrans();
		if(!$fun_bank->getMoneyBank){
			if($this->userinfo['银行卡号']=="" || $this->userinfo['开户银行']=="" || $this->userinfo['开户名']==""){
				$this->error('请完善你的银行卡信息');
			}
		}else{
			if($_POST['cardnumble']=="" || $_POST['bankname']=="" || $_POST['cardname']==""){
				$this->error('请完善你的银行卡信息');
			}
		}
		
        $mess = "";
		if($fun_bank->getMoneyPass2){
		    if(!chkpass($_POST["pass2"],$this->userinfo["pass2"])){
		        $mess = L('交易密码错误');
		    }
		}
        if($fun_bank->getMoneyPass3){
            if(!chkpass($_POST["pass3"],$this->userinfo["pass3"])){
		        $mess .= L('三级密码错误');
		    }
        }
		if($fun_bank->getMoneySmsSwitch){
			$verify = S($this->userinfo['编号'].'_'.$bank->name.'提现');
			if(!$verify || $verify != intval($_POST['getSmsVerfy']) || !$_POST['getSmsVerfy']){
				$this->error(L('短信验证码错误或已过期!'));
			}
		}
		if($fun_bank->getSecretSafe){
            if($this->userinfo["密保答案"] != $_POST["getsafeanswer"]){
		        $this->error(L('密保答案有误'));
		    }
        }
        //如果被锁定
        if($this->userinfo[$fun_bank->name.'锁定']==1)
        {
        	$mess=L('您的账户处于锁定状态.不能操作');
        }
        $getsum = $_POST["getsum"];
        if(!is_numeric($getsum)|| $getsum <= 0){
            $mess .=L('金额不能为空');
        }
        if(!transform($fun_bank->getMoneyWhere,$this->userinfo))
        {
        	$mess=$fun_bank->getMoneyMsg;
        }
        if($mess != ""){
            $this->error($mess);
		}
		if(!M()->autoCheckToken($_POST))
		{
			$this->error('您已经提交过提现申请,如继续操作,请从新点击提现功能');
		}
        $re = $this->setGet($fun_bank,$this->userinfo);
        if($re == ""){	
	        //写入用户操作日志
			$authInfo['姓名']=$this->userinfo['姓名'];
			$authInfo['编号']=$this->userinfo['编号'];
			$authInfo['id']=$this->userinfo['id'];
			$data = array();
			$datalog['user_id']=$authInfo['id'];
			$datalog['user_name']=$authInfo['姓名'];
			$datalog['user_bh']=$authInfo['编号'];
			$datalog['ip']=$_SESSION['ip'];
			$datalog['content']='用户提现';
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
			// 防止点击多次提交按钮，重复提交
			$checks = M('用户');
			M()->commit();
			//发送的验证码注销
			S($this->userinfo['编号'].'_'.$fun_bank->name.'提现',null,300);
			//添加用户提现邮件提醒
            if(CONFIG('txmmailSwitch')){
				sendMail($this->userinfo,$this->userobj->byname.'提现',CONFIG('txmmailContent'));
            }
            $this->success('操作成功');
        }else{// 错误信息
            M()->rollback();
            $this->error($re);
        }
    }
    // 转账
    public function give(fun_bank $fun_bank){
		$name	= L($fun_bank->byname);
        if(!$fun_bank->giveMoney || !$fun_bank->use){		//如果转账功能未开启,拒绝访问
            $this->error(L($fun_bank->byname).L('transfer_to_not_open'));
        }else{
        	$firstto=false;
        	foreach($fun_bank->giveCon as $key=>$bval){
        		if($bval->isme==1 && $key==0){
        			$firstto=true;break;
        		}
        	}
            $this->assign('givecon',$fun_bank->giveCon);
            $this->assign('bank',$fun_bank);
			$this->assign('name',$name);
            $this->assign('user_bank',$this->userinfo[$fun_bank->name]);
            $this->display();
        }
    }
    // 转账完成
    public function giveSave(fun_bank $fun_bank){
		//对转账设置做判断
		if($_POST["giveTo"]=='' || !isset($fun_bank->giveCon[$_POST["giveTo"]])){
			$this->error(L('参数错误'));
		}
		$givecon = $fun_bank->giveCon[$_POST["giveTo"]];
		
        M()->startTrans();
		//如果需要验证二级密码
		if($fun_bank->giveMoneyPass2 && !chkpass($_POST["pass2"],$this->userinfo["pass2"])){
		    $mess = L('交易密码错误');
		}
        //如果需要验证三级密码
        if($fun_bank->giveMoneyPass3 && !chkpass($_POST["pass3"],$this->userinfo["pass3"])){
	        $mess .= L('三级密码错误');
        }
        //如果被锁定
        if($this->userinfo[$fun_bank->name.'锁定']==1)
        {
        	$mess=L('您的账户处于锁定状态.不能操作');
        }
        if(!transform($fun_bank->giveMoneyWhere,$this->userinfo))
        {
        	$mess=$fun_bank->giveMoneyMsg;
        }
        if($mess != ""){				//如果有验证没通过,报告错误信息
            $this->error($mess);
            exit();
        }

		if($fun_bank->giveMoneySmsSwitch){
			$verify = S($this->userinfo['编号'].'_'.$fun_bank->name.'转账');
			if(!$verify || $verify != intval($_POST['giveSmsVerfy']) || !$_POST['giveSmsVerfy']){
				$this->error('短信验证码错误或已过期!');
			}
		}
        
		$tobank = X('>',$givecon['obj']);
        $message = "";$userid = "";
        if($givecon["isme"] == "0"){			//如果不是转到自己的账户
            $userid = trim($_POST["userid"]);
            if($userid =="" || !$this->userobj->have($userid)){
                $message .= L('转入账户不存在')."<br/>";     //输出用户不存在提示
    		}
            if(strtolower($userid) == strtolower($this->userinfo["编号"])){
                $message .= L('转入账户不能为自己')."<br/>";
            }
            // 网体判断
            if($givecon["leastNet"]!="无"){
                foreach(X('net_rec,net_place') as $net){
                    if($net->name == $givecon["leastNet"]){
                        $up = $net->getups($this->userinfo,0,0,"编号='$userid'");
                        $down = $net->getdown($this->userinfo,0,0,"编号='$userid'");
                        if(!$up && !$down){
                            $message .= L('只能转入'.$net->byname.'网体')."<br/>";
                        }
                    }elseif($net->name.'上级' == $givecon["leastNet"]){
						$up = $net->getups($this->userinfo,0,0,"编号='$userid'");
						if(!$up){
                            $message .= L('只能转入'.$net->byname.'上级')."<br/>";
                        }
					}elseif($net->name.'下级' == $givecon["leastNet"]){
						$up = $net->getdown($this->userinfo,0,0,"编号='$userid'");
						if(!$up){
                            $message .= L('只能转入'.$net->byname.'下级')."<br/>";
                        }
					}
                }
            }
            //默认编号和数据库一致
            if($message==''){
            	$userid=M('用户')->where(array("编号"=>$userid))->getField('编号');
            }
        }
        
        $givesum = $_POST["givesum"];
        if($givesum=='')//如果转账金额为空,报错
		{
			 $message .= L('转账金额不能为空')."<br/>";
		}
        if(!is_numeric($givesum)|| $givesum <= 0){		//如果转账金额不是大于0的数字,报错
            $message .= L('转账金额不是大于0的数字')."<br/>";
        }else if($givecon["leastMoney"] > $givesum){	//如果转账金额小于最小转账金额限定,报错
            $message .= L('转账金额小于最小转账金额').$givecon['leastMoney']."<br/>";
        }
        if($givecon['giveMoneyInt'] !="0" && $givesum % $givecon['giveMoneyInt'] != 0){//如果转账金额不符合设定的整数倍限定,报错
            $message .= L('转账金额必须为').$givecon['giveMoneyInt'].L('的整数倍')."<br/>";
        }
        if($message != ""){		//输出错误信息
            $this->error($message);
        }
        //$m_user = M('用户');
        $m_user = M('货币');//货币分离
        $re = $m_user->where("编号='".$this->userinfo["编号"]."'")->lock(true)->find();
        $givesum = floatval($givesum);
        
        //做上限判断,账户金额不能大于多少
        if($givecon["isme"]=='0')
        {
        	$touser=$m_user->where(array('编号'=>$userid))->lock(true)->find();
        }
        else
        {
        	$touser=$re;
        }
        $tops = $tobank->getcon('top',array('where'=>'','val'=>0,'msg'=>''));
        foreach($tops as $top)
        {
        	if(transform($top['where'],$touser) && $touser[$tobank->name] + $givesum > $top['val'])
        	{
        		if($top['msg'] == '')
        		{
        			$this->error('要转入的账户超过限额');
        		}
        		else
        		{
        			$this->error($top['msg']);
        		}
        	}
        }
        //判断完成
        
        if($re[$bank->name] < $givesum){  //如果余额不足,输出错误信息
			$m_user->query('unlock tables');
            $this->error(L('余额不足'));
        }else{
            $givesum2 = $givesum * ($givecon["scale"]/100);
            //计算手续费
            $tax = ($givecon['comCharge']/100) * $givesum2;
            if($tax < $givecon["leastComCharge"]){
                $tax = $givecon["leastComCharge"];
            }else if($tax > $givecon["limitComCharge"] && $givecon["limitComCharge"]!=0){
                $tax = $givecon["limitComCharge"];
            }
			$givesum1  = $givesum2 - $tax;		//扣除手续费后的金额
            //转账成功
            if($userid !=""){	//如果是转入其他人的账户, 转账处理,当前用户扣除货币,转入人增加货币
                $fun_bank->set($this->userinfo["编号"],$userid,-$givesum,'转账转出',$_POST["memo"]."(转给[{$userid}]的{$tobank->byname})");
                $tobank->set($userid,$this->userinfo["编号"],$givesum1,'转账转入',$_POST["memo"]."(转自[".$this->userinfo["编号"]."]的{$fun_bank->byname})");
            }else{			//如果转入自己的其他货币账户, 扣除转出账户金额,增加转入账户金额
                $fun_bank->set($this->userinfo["编号"],$this->userinfo["编号"],-$givesum,'转账转出',$_POST["memo"]."(转给自己的{$tobank->byname})");
                $tobank->set($this->userinfo["编号"],$this->userinfo["编号"],$givesum1,'转账转入',$_POST["memo"]."(转自自己的{$fun_bank->byname})");
            }
            //写入用户操作日志
			$data = array();
			$datalog['user_id']  =$this->userinfo['id'];
			$datalog['user_name']=$this->userinfo['姓名'];
			$datalog['user_bh']  =$this->userinfo['编号'];
			$datalog['ip']=$_SESSION['ip'];
			$datalog['content']='用户转账';
			$datalog['create_time']=time();
			//获取用户的IP地址
			import("ORG.Net.IpLocation");
			$IpLocation				= new IpLocation("qqwry.dat");
			$loc					= $IpLocation->getlocation();
			$country				= mb_convert_encoding ($loc['country'] , 'UTF-8','GBK' );
			$area					= mb_convert_encoding ($loc['area'] , 'UTF-8','GBK' );
			$datalog['address']		= $country.$area;
			M('log_user')->add($datalog);
			//发送的验证码注销
			S($this->userinfo['编号'].'_'.$bank->name.'转账',null,300);
			//写入用户操作日志结束
			$smsdata=array("转出编号"=>$this->userinfo["编号"],"转入编号"=>$userid!=""?$userid:$this->userinfo["编号"],"转账金额"=>$givesum);
			sendSms("changePwd",$this->userinfo['编号'],'用户修改密码',$smsdata);
			if($userid!=""){
				$smsdata["转入金额"]=$givesum1;
				sendSms("changePwd",$this->userinfo['编号'],'用户修改密码',$smsdata);
			}
			
            //添加结束
           	M()->commit();
            $this->success(L('转账成功'),"__URL__/give:__XPATH__");
        }
    }

	//转账验证
	public function giveAjax()
	{
		$user = $this->userobj->getuser($_POST['userid']);
		if($user && $_POST['userid']!= '')
		{
			$this->ajaxReturn(array('姓名'=>$user['姓名']),'成功',1);
		}
		else
		{
			$this->ajaxReturn('','失败',0);
		}
	}
    //  提现  添加用户编号,提现金额,开户行,银行卡号,开户地址,开户名
	public function setGet($bank,$user){
        $getsum = floatval($_POST["getsum"]);
        if($bank->getMoneyBank){
            $bankname    = $_POST["bankname"];
            $cardnumble  = $_POST["cardnumble"];
            $cardaddress = $_POST["cardaddress"];
            $cardname    = $_POST["cardname"];
            $cardtel     = empty($_POST["cardtel"]) ? $user["移动电话"] : $_POST["cardtel"] ;
        }else{
            $bankname    = $user["开户银行"];
            $cardnumble  = $user["银行卡号"];
            $cardaddress = $user["开户地址"];
            $cardname    = $user["开户名"];
            $cardtel     = $user["移动电话"];
        }
		$data=array(
			"编号"=>$this->userinfo['编号'],
			"提现额"=>$getsum,
			"开户行"=>$bankname,
			"银行卡号"=>$cardnumble,
			"开户地址"=>$cardaddress,
			"开户名"=>$cardname,
			"联系电话"=>$cardtel,
			"操作时间"=>systemTime(),
		);
		if($getsum < $bank->getMoneyMin ){
		    return L('不能少于最小提现额')."{$bank->getMoneyMin}！";
		}else if($user[$bank->name] - $getsum < 0){
			return L('余额不足');
		}else if($bank->getMoneyInt != 0 && fmod($getsum,$bank->getMoneyInt)!=0){
			return L('提现金额需为{$bank->getMoneyInt}的倍数！');
		}else{
			//M()->startTrans();
            $m_bank=M("提现");
            $data['类型']=$bank->name;
			$data["手续费"] = ($bank->getMoneyTax/100) * $getsum;
            if($data["手续费"] < $bank->getMoneyTaxMin){
                $data["手续费"] = $bank->getMoneyTaxMin;
            }else if($bank->getMoneyTaxMax != 0 && $data["手续费"] > $bank->getMoneyTaxMax){
                $data["手续费"] = $bank->getMoneyTaxMax;
            }
			$data["实发"] = $getsum - $data["手续费"];
			//提现汇率换算
			if($bank->getMoneyRatio){
				$data["换算后实发"]=$data["实发"]*$bank->getMoneyRatio;
			}
            $data["状态"] = "0";
			$re2=$m_bank->add($data);
			if($re2){
			    $res = $bank->set( $user["编号"], $user["编号"],-$getsum,$this->userobj->byname.'提现','申请提现扣除：'.$getsum);
                if(!is_numeric($res)){//btx增加返回结果判断
                    return $res;
                }
			    //M()->commit();
				return "";
			}else{
				//M()->rollback();
				return L('error_title');
			}
		}
	}
	//汇款通知列表
	public function rem()
	{
        $list = new TableListAction('汇款通知');
        $list ->where(array('编号'=>"{$this->userinfo['编号']}"))->order("id desc");
       
        $data = $list->getData();
         if(adminshow('huikuan')){
	         foreach($data['list'] as $key=>$v){
	           //查询汇款方式
	           $huikuan = M('汇款方式')->where(array('id'=>$v['汇款方式']))->find();
	           $data['list'][$key]['汇款方式'] = $huikuan['方式名称'];
	         }
         }
        $bank = M("银行卡");
        $banks = $bank ->where(array('卡号'=>array('neq',''),'状态'=>'有效'))->select();
        $this->assign('bank',$banks);
        $this->assign('is_huikuanimg',CONFIG('bankset'));
        $this->assign('hk_type',adminshow('huikuan'));
        $this->assign('data',$data);
		$this->display();
	}
	//不带图片的添加汇款
	function add_rem_two(){
		$bank = M("银行卡");
		$data = $bank ->where(array('卡号'=>array('neq',''),'状态'=>'有效'))->select();
		$this->assign('bank',$data);
		$this->assign('hkzhxz',CONFIG('hk_hkzhxz'));
		$this->assign('USER_REMIT_MIN',CONFIG('USER_REMIT_MIN'));
		$this->assign('USER_REMIT_MAX',CONFIG('USER_REMIT_MAX'));
		$this->display();
	}
	//不带图片的汇款保存
	function rem_save_two(){
	  	$m = M('汇款通知');
		//判断未审核
		$unAudit=$m->where(array('编号'=>$_SESSION[C('USER_AUTH_NUM')],'状态'=>0))->find();
		if($unAudit){
			$this->error(L('您已经有未审核的记录存在，请等待审核后再提交'));
		}
		if($_POST['汇入账户']==''){
			$this->error(L('汇入账户不能为空'));
		}
		if(floatval($_POST['金额'])<=0){
			$this->error(L('汇款金额输入有误'));
		}
		if(floatval($_POST['汇款时间'])==''){
			$this->error(L('请输入汇款时间'));
		}
		
		$USER_REMIT_MIN=CONFIG('USER_REMIT_MIN');
		$USER_REMIT_MAX=CONFIG('USER_REMIT_MAX');
		if($USER_REMIT_MIN != ''){
			if($_POST['金额']<$USER_REMIT_MIN){
				$this->error('填写金额小于最低汇款限制'.$USER_REMIT_MIN);
			}
		}
		if($USER_REMIT_MAX>0){
			if($_POST['金额']>$USER_REMIT_MAX){
				$this->error('填写金额大于最高汇款限制'.$USER_REMIT_MAX);
			}
		}
		$data	= $m->create();
		if($data===false){
			$this->error();
		}else{	
			$data['汇款时间']	= strtotime($_POST['汇款时间']);
			if(CONFIG("USER_REMIT_RATIO_USE")=="true"){
				$data['换算后金额'] = $data['金额']/CONFIG("USER_REMIT_RATIO");
			}
			if($m->add($data)){
				$this->success(L('操作成功'),__URL__.'/rem');
			}else{
				$this->error(L('操作失败'));
			}
		}
	
	}
	public function dispFunction($status)
	{
		if($status==0){
			return "未审核";
		}else{
			return "已审核";
		}
	}
	//添加汇款通知
		public function add_rem()
	{
		$bank = M("银行卡");	
		$data = $bank ->where("状态!='无效' and 卡号!='' and 户名!=''")->select();
		$this->assign('tp',$data);
		$this->assign('bank',$data);
		//$this->assign('hkzhxz',CONFIG('hk_hkzhxz'));
		$this->display();
	}
	public function add_rem1()
	{
		$id=$_GET['id'];
		$bank = M("银行卡");
		$data = $bank ->where("id=$id")->select();
		$this->assign('bank',$data);
		//$this->assign('hkzhxz',CONFIG('hk_hkzhxz'));
		$this->assign('hk_type',adminshow('huikuan'));
		if(adminshow('huikuan')){
			//查询所有的汇款方式
			$huikuans  = M('汇款方式')->select();
			$this->assign('huikuans',$huikuans);
		}
		$this->display();
	}
	//添加汇款通知保存
	public function rem_save()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
		B('XSS');
		$m = M('汇款通知');
		M()->startTrans();
		//判断未审核
		$unAudit=$m->where(array('编号'=>$_SESSION[C('USER_AUTH_NUM')],'状态'=>0))->find();
		if($unAudit){
			$this->error(L('您已经有未审核的记录存在，请等待审核后再提交'));
		}
		/*
		if($_POST['汇入账户']==''){
			$this->error(L('汇入账户不能为空'));
		}*/
		if(floatval($_POST['金额'])<=0){
			$this->error(L('汇款金额输入有误'));
		}
		if(floatval($_POST['汇款时间'])==''){
			$this->error(L('请输入汇款时间'));
		}
		if(adminshow('huikuan')){
			//判断是否选择汇款方式
			if(empty($_POST['汇款方式'])){
			   	$this->error(L('请选择汇款方式'));
			}
		}
		$data	= $m->create();
		if($data===false){
			$this->error();
		}else{	
			//dump($data);exit;
			$data['汇款时间']	= strtotime($_POST['汇款时间']);
			if(CONFIG("USER_REMIT_RATIO_USE")=="true"){
				$data['换算后金额'] = $data['金额']/CONFIG("USER_REMIT_RATIO");
			}
			
			if($m->add($data)){
				M()->commit();
				$this->success(L('操作成功'),__URL__.'/rem');
			}else{
				M()->rollback();
				$this->error(L('操作失败'));
			}
		}
	}
	//删除汇款通知
	public function rem_delete()
	{
		$m = M('汇款通知');
		$where['id']	= $_GET['id'];
		M()->startTrans();
		$m->where($where)->delete();
		M()->commit();
		$this->success(L('操作成功'));
	}

		//检验汇款通知银行卡卡号
	public function checkBank()
	{
		
		$cardid=$_POST['cardid'];
		$bank = M("银行卡");
		$data = $bank ->where(array('卡号'=>$cardid))->find();
		if($data)
		{
			echo "$('#state_incardid').html('您输入的卡号正确');$('#submit').removeAttr('disabled');";
		}
		else
		{
			echo "$('#state_incardid').html('未找到银行卡信息');$('#submit').attr('disabled','true');";
		}
		//$this->assign('bank',$data);
	}
}
?>