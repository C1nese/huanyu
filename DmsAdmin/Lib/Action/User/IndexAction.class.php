<?php
defined('APP_NAME') || die('不要非法操作哦!');
class IndexAction extends CommonAction {
	
	public function index()
	{
		if(!$this->userobj->unaccLog){
			if($this->userinfo['状态']=='无效'){
				unset( $_SESSION[C('USER_AUTH_KEY')]);
				unset( $_SESSION[C('USER_AUTH_NUM')]);
				unset( $_SESSION['username']);
				$this->redirect_url('用户未审核，禁止登陆',__URL__.'/login');
			}
		}
		/* if(CONFIG('DEFAULT_THEME')=='blanc_default' || CONFIG('DEFAULT_THEME')=='xinying3'){
		  $notice	= $this->getNotice(8);
		}else{
		 $notice	= $this->getNotice();
		} */
			
		//今日公告
		$i=0;
		if($notice)
		foreach($notice as $k=>$vs){
		   if($vs['创建时间']>=$startime && $vs['创建时间']<$endtime){
		     $i++;
		   }
		}
		$notice_count = $i;
		$nownotice	= $this->getNotice('8');
	    	//轮播图片
		$this->img =M('首页图片')->select();

        /*btx 首页图表数据 start*/
        //查询结算业绩数据
        $prizes = M("销售奖金")->where(array("编号"=>$this->userinfo['编号']))->field("推广补贴,推广补贴累计,推广分红,推广分红累计,计算日期")->order("id desc")->limit(7)->select();
		$count = count($prizes);
        if($count > 0){
            $j = 0;
            for($i = $count - 1 ; $i >= 0 ; $i --){
                $intro_prize[$j]['time'] = $team_prize[$j]['time'] = date("Y-m-d",$prizes[$i]['计算日期']);
                //直推伙伴业绩
                $intro_prize[$j]['val'] = $prizes[$i]['推广补贴']*0.74;
                $intro_prize[$j]['total'] = $prizes[$i]['推广补贴累计']*0.74;
                //团队业绩
                $team_prize[$j]['val'] = $prizes[$i]['推广分红']*0.74;
                $team_prize[$j]['total'] = $prizes[$i]['推广分红累计']*0.74;
                $j ++;
            }
        }
        if($count <= 1){//没有数据或只有一条数据给定默认值，以免前台空白
            // if($count == 1){
            //     $intro_prize[1] = $intro_prize[0];
            //     $team_prize[1] = $team_prize[0];
            // }
            $intro_prize[0]['time'] = $team_prize[0]['time'] = date("Y-m-d",$this->userinfo['注册日期']);
            $intro_prize[0]['val'] = $intro_prize[0]['total'] = 0;//直推伙伴业绩
            $team_prize[0]['val'] = $team_prize[0]['total'] = 0;//团队业绩
        }
        $this->assign("intro_prize",json_encode($intro_prize));
        $this->assign("team_prize",json_encode($team_prize));
        /*btx 首页图表数据 end*/

        /*btx 首页联盟推荐产品数据 start*/
        $recommend1 = M("推荐")->field('名称,描述,图片,价格,url,score')->where('分类=1 and 状态=0')->limit(10)->select();
        $this->assign('recommend1',$recommend1);
        /*btx 首页联盟推荐产品数据 end*/

		//董事长致辞
		if($this->userinfo['登陆次数'] < 3){
			$zhici=M('致辞')->order('id desc')->limit(1)->find();
			$this->assign('zhici',$zhici);
		}
		$this->color =array('#13bf84','#217ad5','#9e088e','#e04822'); 
		
		$this->assign('notice_count',$notice_count);
		$this->assign('notice',$notice);
		$this->assign('nownotice',$nownotice);
		$this->assign('web_name',L('web_name'));
		$this->assign('web_title',L('web_title'));
		if(isset($_SESSION['isMobile']))
		{
			C('DEFAULT_THEME','wap');
		}
		if(file_exists(ROOT_PATH.'DmsAdmin/Tpl/User/'.C('DEFAULT_THEME').'/index.html')){
			$this->display('DmsAdmin/Tpl/User/'.C('DEFAULT_THEME').'/index.html');
		}else{
			$this->display();
		}
    }

	//欢迎页面
	public function welcome()
	{
		$radio_con=array();
		$lv=array();
		if(!isset($_SESSION['ip']))
		{
			import("ORG.Util.Myfun");
			$rs=Myfun::getip();
			$_SESSION['ip']=$rs;
		}
		$netPlaceName = array();
		foreach(X('net_place') as $netPlace){
			$regions=$netPlace->getcon("region",array('name'=>''));
			foreach($regions as $region){
				if($netPlace->pvFun)
				$netPlaceName[$netPlace->byname][]=$region['name'];
			}

		}
		$funbank=array();
		foreach(X('fun_bank') as $fun_bank)
		{
			if(!$fun_bank->userdisp) continue;
			$funbank[L($fun_bank->byname)]=$this->userinfo[$fun_bank->name];
		}
		$this->assign('netPlaceName',$netPlaceName);
		$this->assign('ip',$_SESSION['ip']);
		$this->assign('radio_con',$radio_con);
		$this->assign('funbank',$funbank);
		$this->display();
	}

   

	public function change_lang()
	{
		$lang	= $_REQUEST['lang'];
		cookie('think_language',$lang);
		$this->ajaxReturn(1,'成功',1);
	}
	public function change_color()
	{
		$color	= $_REQUEST['color'];
		cookie('color',$color);
		$this->ajaxReturn(1,'成功',1);
	}
	//二级密码
	public function secPwd()
	{
		$this->display();
	}
	//二级密码验证
	public function seconfirm()
	{
		if(chkpass($_POST['pwd1'],$this->userinfo['pass2']))
		{
			$_SESSION[C('SAFE_PWD')]=$this->userinfo['pass2'];
			//设置二级密码最后确认时间
			$_SESSION['DmsPass2Time']=time();
			redirect(urldecode($_GET['returnUrl']));
		}
		else
		{
			$this->error('密码不正确');
		}
	}
		//三级密码
	public function secPwd3()
	{
		$this->display();
	}
		//三级密码验证
	public function seconfirm3()
	{
		if(chkpass($_POST['pwd1'],$this->userinfo['pass3']))
		{
			$_SESSION[C('SAFE_PWD3')]=$this->userinfo['pass3'];
		      //设置二级密码最后确认时间
			$_SESSION['DmsPass3Time']=time();
			redirect(urldecode($_GET['returnUrl']));
		}
		else
		{
			$this->error('密码不正确');
		}
	}
	//关联账号登录
	public function relatedUserLogin(){
		$relatedUsers = $this->getRelatedUser();
		$relatedUsersId = array();
		$relatedUsersId[] = $relatedUsers['父账号']['编号'];
		foreach($relatedUsers['子账号'] as $childUser){
			$relatedUsersId[] = $childUser['编号'];
		}
		if(!in_array($_GET['userid'],$relatedUsersId)){
			$this->error(L('非关联账号!不能登录'));
			die;
		}
        $authInfo = M('用户')->where(array("编号"=>$_GET['userid']))->find();
        if(!$authInfo){
            $this->error(L('该用户不存在'));
        }else{
            $_SESSION[C('USER_AUTH_KEY')]	=  $authInfo['id'];
			$_SESSION[C('USER_AUTH_NUM')]	=  $authInfo['编号'];
			$_SESSION['username']		    =  $authInfo['姓名'];
			/*foreach($user->getcon('session',array('name'=>'','rename'=>'')) as $session)
			{
				if($session['rename'] !=''){
				   $_SESSION[$session['rename']]=$authInfo[$session['name']];
				}else{
					$_SESSION[$session['name']]=$authInfo[$session['name']];
				}
			}*/
			redirect(__APP__.'/User/Index/index');
        }
	}
	//添加关联账号
	public function addRelatedUser(){
		if($this->userinfo['编号'] == $_POST['relatedUser']){
			$this ->error(L('关联账号不能为自己'));
		}
		if($this->userinfo['关联账号']!='0'){
			$this->error(L('非主账号,不能设置关联账号'));
		}
		$data['编号'] = $_POST['relatedUser'];
		$data['pass1'] = md100($_POST['relatedPass']);
		$data['pass2'] = md100($_POST['relatedPass2']);
		$m = M('用户');
		$result = $m->where($data)->find();
		if($result){ 
			M()->startTrans();
			if($m->where($data)->setField('关联账号',$_SESSION[C('USER_AUTH_NUM')])){
				M()->commit();
				$this -> success(L('设置成功'));
			}else{
				M()->rollback();
				$this -> error(L('设置失败'));
			}
		}else{
			$this -> error(L('用户id或密码错误'));
		}
	}

	//获取关联账号信息
	protected function getRelatedUser(){
	  
	  $m = M('用户');
	  if($this->userinfo['关联账号'] == 0){
		 $parentUser = array('编号'=>$this->userinfo['编号']);
		 $childUsers = $m ->field('编号')->where(array('关联账号'=>$this->userinfo['编号']))->select();
	  }else{
		 $parentUser = $m ->field('编号')->where(array('编号'=>$this->userinfo['关联账号']))->find();
		 $childUsers = $m ->field('编号')->where(array('关联账号'=>$parentUser['编号']))->select();
	  }
	  $relatedUser = array('父账号'=>$parentUser,'子账号'=>$childUsers);
	  return $relatedUser;
	}
	public function getNotice($limit=20)
	{
		$where="语言='".C('DEFAULT_LANG')."' and (查看权限=0 or 查看权限={$this->userinfo['id']}";
		foreach(X('net_rec') as $net){
			$netshuju = $this->userinfo[$net->name.'_网体数据'];
			//$where .= " or find_in_set(查看权限,'{$netshuju}')";
		}
		foreach(X('net_place') as $net){
			$netshuju = $this->userinfo[$net->name.'_网体数据'];
			foreach($net->getcon("region",array("name"=>""),false) as $region){
				$netshuju = str_replace('-'.$region['name'],'',$netshuju);
			}
			//$where .= " or find_in_set(查看权限,'{$netshuju}')";
		}
		$where .=')';
		$lists=M("公告")->where($where)->order("顺序 desc,创建时间 desc")->limit($limit)->select();
		return $lists;
	}

	public function indexlist(){
		if($_GET['menu']=='zlgl')$menuid='资料管理';
		if($_GET['menu']=='ywgl')$menuid='业务管理';
		if($_GET['menu']=='cwgl')$menuid='财务管理';
		if($_GET['menu']=='xxgl')$menuid='信息管理';

		if($_GET['menu']=='jbxx')$menuid='基本信息';
		//$menu=$this->header();
		$this->assign('menuid',$menuid);
		$this->display();
	}
}
?>