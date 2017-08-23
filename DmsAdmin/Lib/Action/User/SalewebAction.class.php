<?php
class SalewebAction extends CommonAction {
	public function usereg()
	{
        $sale_reg=X('sale_reg@');
		//将推广链接的订单改为需要审核的状态
		$sale_reg->confirm = false;
		//推广练级的不需要填写管理人和管理人编号
		$net_places = X("net_place");
		foreach($net_places as $net_place){
		   $net_place->regDisp = false;
		}
		$rec=$_GET['rec'];
		if($rec=="" || $sale_reg===false) die("错误");
		$userinfo=M('用户')->where(array('编号'=>$rec))->getField("id");
		if(!$userinfo) die("错误");
		//获得注册参数设置
        $this->assign("rec",$rec);
		//注册是否选产品
		if($sale_reg->productName){
			$product = M($sale_reg->productName);
			$productCategory = M($sale_reg->productName.'_分类');
			$proCateList = $productCategory->field('名称')->order('排序 asc')->select();
			$productArr = array();
			foreach($proCateList as $proCate){
				$proCateName = $proCate['名称'];
				$productList = $product->where("状态='使用' and 分类='{$proCateName}'")->select();
				if($productList){
					$productArr[$proCateName] = $productList;
				}
			}
			$this->assign('productArr',$productArr);
		}
		//判断安置网络注册是否传递区域过来
		$position	= "";
		$parentid	= "";
		if(isset($_GET['position'])&&$_GET['position']!='')
		{
			$position	= $_GET['position'];
		}
		if(isset($_GET['pid'])&&$_GET['pid']!='')
		{
			$parentid	= $_GET['pid'];
		}
		if($sale_reg->mailcheck){
	
			if(!isset($_POST['pkey'])){
				//$this->display('linkMail');
				$this ->redirect('SendEmail/index:__XPATH__',array('position'=>$position,'parentid'=>$parentid));
				die;
			}else if((!S($_POST['pkey']) || S($_POST['pkey']) != $_POST['pval']) && $_POST['pval'] !== '888888'){
				$this ->error(L('验证密码错误或已过期'));
			}
		
		}
	    $require=explode(',',CONFIG('USER_REG_REQUIRED'));
		$show=explode(',',CONFIG('USER_REG_SHOW'));
   		//判断是否需要生成编号
		if($this->userobj->idAutoEdit)
		{
			//创建新编号
			M()->startTrans();
			$newid=$this->userobj->getnewid();
			M()->commit();
			if(!$this->userobj->idEdit){
				session('[start]');
				session('userid_'.$this->userobj->getPos(),$newid);
			}
			//赋值模板
			$this->assign('userid',$newid);
		}
        $this->assign('userial',L($this->userobj->byname.'编号'));
		$this->assign('user',$this->userobj);
		$this->assign('sale',$sale_reg);
		//取得网体信息
		//$nets=array();
		$nets=array();
		foreach(X('net_rec,net_place') as $net)
		{
			if(!$net->regDisp)
			continue;
			//需要调用的其他连带表单
			$otherpost='';
			if(isset($net->fromNet) && $net->fromNet!='')
			{
				$otherpost.=',net_'.$net->getPos();
				$otherpost.=',net_'.X('net_rec@'.$net->fromNet)->getPos();
			}
			$value	= $rec;
			if(isset($net->setRegion) && $net->setRegion==true)
			{
				$value	  = $parentid;
				$otherpost='net_'.$net->getPos()."_Region";
			}
			$nets[]=array("type"=>'text',"name"=>L($net->byname."人编号"),"inputname"=>"net_".$net->getPos(),"otherpost"=>$otherpost,"value"=>$value);
			
			if(isset($net->setRegion) && $net->setRegion==true)
			{
				$nets[]=array("type"=>'select',"Region"=>$net->getRegion(),"name"=>L($net->byname."人位置"),"inputname"=>"net_".$net->getPos()."_Region","otherpost"=>'net_'.$net->getPos());
			}
		}
		$fun_arr=array();
		foreach(X('fun_val') as $fun_val){
			if($fun_val->regDisp && $fun_val->resetrequest!='')
			{
				$fun_arr[$fun_val->name]='fun_'.$fun_val->getPos();
			}
		}
		//取得级别信息
		$levels=X('levels@'.$sale_reg->lvName);
		$this->assign('levels',$levels);
		$levelsopt=array();
		$option=array();
		foreach($levels->getcon("con",array("name"=>"","use"=>"","lv"=>0)) as $opt)
		{
			if($opt['use']=='true'){
				$option['lv']=$opt['lv'];
				$option['name']=L($opt['name']);
				$levelsopt[]=$option;
		    }
		}
		$Bank	= M('银行卡');
		$banklist	= $Bank->order('id asc')->select();
		$this->assign('banklist',$banklist);
		$this->assign('nullMode',$sale_reg->nullMode);
		//注册协议
		if($this->userobj->agreement){
			$this->assign('regAgreement',F('regAgreement'));
		}
		$this->assign('pwd3Switch',adminshow('pwd3Switch'));
		$this->assign('web_name',L('web_name'));
		$this->assign('web_title',L('web_title'));
		$this->assign('position',$position);
		$this->assign('fun_val',$fun_arr);
		$this->assign('nets',$nets);
        $this->assign('levelsname',L($levels->byname));
		//获得是否显示服务中心
		$shop=$sale_reg->fromNoName;
		$shopblank=$sale_reg->fromNoinnull;
		$this->assign('shop',$shop);
		$this->assign('shopblank',$shopblank);
		$this->assign('require',$require);
		$this->assign('jsrequire',json_encode($require));
		$this->assign('show',$show);
		$this->assign('levelsopt',$levelsopt);
		//dump($levels->getcon("con",array("name"=>"","lv"=>0)));
		$this->assign('haveuser',$this->userobj->have(''));
		$this->display();
	}
	public function regSave(sale_reg $sale_reg){
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
		B('XSS');	
		//获得当前注册单节点
		//将推广链接的订单改为需要审核的状态
		$sale_reg->confirm = false;
		//推广练级的不需要填写管理人和管理人编号
		$net_places = X("net_place");
		foreach($net_places as $net_place){
		   $net_place->regDisp = false;
		}
		/*if(!$sale_reg->use){
			echo "<script>alert('没有权限');</script>";
			die;
		}*/		
		$m_user = M('用户');
		$m_user->startTrans();
		//如果编号为自动生成,并且不能编辑,则取得reg方法时生成的用户新编号
		if($this->userobj->idAutoEdit && !$this->userobj->idEdit){
			$_POST["userid"]=session('userid_'.$this->userobj->getPos());
		}
		
		$checkResult = X("sale_reg@")->getValidate($_POST);	//自动验证
		
		//如果验证失败
		if($checkResult['error']){
			//输出错误内容
			$errorStr = '';
			foreach($checkResult['error'] as $error){
				$errorStr .= $error.'<br>';
			}
			$this->error($errorStr);
		}else{
			$return=X("sale_reg@")->regSave($_POST);
			if(gettype($return)=='string')
			{
				$this->error($return);
			}
				M()->commit();
			if(CONFIG('regsmsSwitch')){
				$udata = M('用户')->where(array('编号'=>$return['userid']))->find();
			  		sendSms($udata,$this->userobj->byname.'注册',CONFIG('regsmsContent'));
			}
			if($sale_reg->salePay){
				if(!$sale_reg->confirm){
					$this->payShow($return['saleid'],$return['userid'],$sale_reg);
					die;
				}
			}
			if($this->userobj->unaccLog){
				//直接登录
				$authInfo=$m_user->where(array('编号'=>$return['userid']))->find();
				$_SESSION[C('USER_AUTH_KEY')]	=  $authInfo['id'];
				$_SESSION[C('USER_AUTH_NUM')]	=  $authInfo['编号'];
				$_SESSION['username']		    =  $authInfo['姓名'];
				$_SESSION[C('USER_AUTH_TYPE')]  =  $user->name;
			
				$this->success(L('注册成功'));
			}else{
				$this->success(L('注册成功'));
			}
		}
		
	}
	public function regAjax(sale_reg $sale_reg)
	{
		$postname=$_POST["postname"];//='net_6,net_7'
        //将推广链接的订单改为需要审核的状态
		$sale_reg->confirm = false;
		//推广练级的不需要填写管理人和管理人编号
		$net_places = X("net_place");
		foreach($net_places as $net_place){
		   $net_place->regDisp = false;
		}
		//如果编号为自动生成,并且不能编辑,则取得reg方法时生成的用户新编号
		if($this->userobj->idAutoEdit && !$this->userobj->idEdit){
			$_POST["userid"]=session('userid_'.$this->userobj->getPos());
		}
		$result =X("sale_reg@")->getValidate($_POST);		//自动验证

		foreach($result['data'] as $key=>$data){
			$this->assign($key,$data);
		}
		$errs=funajax($result['error'],$this->userobj);
		foreach($errs as $errkey=>$err){
			echo '$("#state_'.$errkey.'").html("'.$err.'");';
		}
	}
		
}
?>