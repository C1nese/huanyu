<?php
defined('APP_NAME') || die('不要非法操作哦!');
class ReasonAction extends CommonAction {
	//密保管理
    function index(){
        $setButton=array(
			'添加'=>array("class"=>"add","href"=>"__URL__/addsecret","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"240"),
			'修改'=>array("class"=>"edit","href"=>"__URL__/editsecret/id/{tl_id}","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"240"),
            '删除'=>array("class"=>"delete","href"=>"__URL__/delsecret/id/{tl_id}","target"=>"ajaxTodo" ,"title"=>"确定要删除吗?"),
        );
        $setShow = array(
			'编号'=>array('row'=>'[id]'),
            '理由'=>array('row'=>'[理由]'),
            '管理员'=>array('row'=>'[管理员]'),
            '添加时间'=>array('row'=>'[添加时间]',"format"=>"time"),
        );
        $list=new TableListAction("申请奖金理由");
        $list->setShow = $setShow;         // 定义列表显示
        $list->setButton = $setButton;     // 定义按钮显示
        $list->title="理由列表";       // 列表标题
        $list->order("id desc"); 
        $this->assign('list',$list->getHtml()); 
        $this->display();
    }
	
    function addsecret(){
        $this->display();
    }
	
    function savesecret(){
        $secret = M('申请奖金理由');
        if(empty($_POST['理由'])){
        	$this->error("请输入理由");
        }else{
        	$rs=$secret->where(array("理由"=>trim($_POST['理由'])))->find();
        	if($rs){
        		$this->error("理由已存在");
        	}
        }
        $data['理由'] = $_POST['理由'];
        $data['管理员']=$_SESSION['loginAdminAccount'];
        $data['添加时间']=systemTime();
        if($secret ->add($data)){
            $this->success("添加理由成功",'__URL__/index');
        }else{
            $this->error("添加失败");
        }  
    }
	
	function editsecret(){
		$secretinfo = M('申请奖金理由')->find($_REQUEST['id']);
		$this->assign('secretinfo',$secretinfo);
		$this->display();
	}
	function saveEditsecret(){
		$secret = M('申请奖金理由');
		if(empty($_POST['理由'])){
        	$this->error("请输入理由");
        }else{
        	$rs=$secret->where(array("理由"=>trim($_POST['理由']),"id"=>array("neq",$_POST['id'])))->find();
        	if($rs){
        		$this->error("理由已存在");
        	}
        }
		$data['id'] = $_POST['id'];
        $data['理由'] = $_POST['理由'];
        $data['管理员']=$_SESSION['loginAdminAccount'];
        $data['添加时间']=systemTime();
        M()->startTrans();
        if($secret ->save($data)){
        	M()->commit();
            $this->success("修改成功",'__URL__/index');
        }else{
        	M()->rollback();
            $this->error("修改失败");
        }
	}
	
    function delsecret(){
        $secret   = M('申请奖金理由');
        M()->startTrans();
		$list	= $secret ->where("id={$_REQUEST['id']}")->delete();
		if($list){
			$this->saveAdminLog('','',"删除理由");
			M()->commit();
			$this->success("删除成功！","__URL__/index");
		}else{
			M()->rollback();
			$this->error("删除失败！");
		}
        
    }    
}
?>