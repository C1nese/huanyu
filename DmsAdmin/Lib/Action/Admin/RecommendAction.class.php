<?php
//产品模块(产品列表---产品出入库)
class RecommendAction extends CommonAction {
    /**
    +----------------------------------------------------------
    * 列表
    +----------------------------------------------------------
    */
	public function index(){
		
		$list = new TableListAction('推荐');
		$setButton=array(
			"添加"=>array("class"=>"add"   ,"href"=>__APP__."/Admin/Recommend/add","target"=>"dialog"  ,"mask"=>"true",'width'=>'600','height'=>'350'),
			"修改"=>array("class"=>"edit"  ,"href"=>__APP__."/Admin/Recommend/edit/id/{tl_id}"  ,"target"=>"dialog"  ,"mask"=>"true",'width'=>'600','height'=>'550'),
			"删除"=>array("class"=>"delete","href"=>__APP__."/Admin/Recommend/delete/id/{tl_id}","target"=>"ajaxTodo","mask"=>"true","title"=>"确定要删除该产品吗？"),	
		);
		
		$list ->setButton = $setButton;
		$list->addshow('ID',array('row'=>'[id]',"searchMode"=>"text",'searchPosition'=>'top'));
		//$list->addshow('产品编码',array('row'=>'[产品编码]',"searchMode"=>"text",'searchPosition'=>'top'));
		$list->addshow('名称',array('row'=>'[名称]',"searchMode"=>"text",'searchPosition'=>'top'));
		$list->addshow('分类',array("row"=>array(array($this,'getcate'),'[分类]'),"searchMode"=>"text","searchSelect"=>array("商城产品"=>"1","联盟任务"=>"2"),"searchPosition"=>"top",'searchRow'=>'分类'));
		
		$list->addshow('图片',array('row'=>array(array($this,'getimg'),'[图片]'),"searchMode"=>"text"));
		
		$list->addshow('添加时间',array('row'=>'[添加时间]','format'=>'time',"searchMode"=>"num","order"=>"[添加时间]"));
        $list->addshow('积分',array('row'=>'[score]',"searchMode"=>"num","order"=>"[score]"));
		
		$list->addshow("状态",array("row"=>array(array($this,'getstatus'),'[状态]'),"searchMode"=>"text","searchSelect"=>array("显示"=>"0","不显示"=>"1"),"searchPosition"=>"top",'searchRow'=>'状态'));
		print_r($list->getHtml());
		$this->assign('list',$list->getHtml());
		$this->display();
	}
	//获得图片
	public function getimg($imgstr){
		if($imgstr == ''){
			return '无';
		}else{
			return '<img src='.$imgstr.' width="120" />';
		}
	}
	public function getcate($cate){
		$ary=array("1"=>'商城产品',"2"=>"联盟任务");
		return $ary[$cate];
	}
	//状态
	public function getstatus($status){
		$ary=array("0"=>'显示',"1"=>"不显示");
		return $ary[$status];
	}
	
	//产品添加
	public function add($product){
		
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
		$upload ->upload();
	}
	//产品添加保存
	public function addSave()
	{
		$model  = M('推荐');
		$data = $this -> getData();
		$data['添加时间'] = time(); 
		
		$pid=$model->add($data);
		if($pid){
			
			if(isset($_POST['submitnext']))
			{
				$this->success('添加成功!','',array('next'=>true));
			}
			else
			{
				$this->success('添加成功!','',array('next'=>false));
			}
		}else{
			$this->error('添加失败!');
		}
	}
	
	// 表单数据
	private function getData($action='add'){
		$data = array();		
		
		isset($_POST['category']) && $data['分类'] = $_POST['category'];
		isset($_POST['name']) && $data['名称'] = trim($_POST['name']);
		isset($_POST['image']) && $data['图片'] = $_POST['image'];
		isset($_POST['score']) && $data['score'] = $_POST['score'];
		isset($_POST['price']) && $data['价格'] = abs(floatval($_POST['price']));
		isset($_POST['url']) && $data['url'] = urlencode($_POST['url']);
		isset($_POST['description']) && $data['描述'] = $_POST['description'];
		isset($_POST['status']) && $data['状态'] = $_POST['status'];//修改页面
		
		
		if($data['名称'] ===''){
			$this->error('名称必填!');
		}else{
			$where=array();
			$where['名称']=$data['名称'];
			if($action=='edit') $where['id']=array("neq",$_POST['id']);
			$have=M('推荐')->where($where)->find();
			if($have){
				$this->error("同名的产品已经存在");
			}
		}
		
		return $data;
	}

	//产品修改
	public function edit($product){
		if(strpos($_GET['id'],',') !== false){
			$this->error('参数错误!');
		}
		$id = $_GET['id'];
		$productInfo = M('推荐')->find($id);
		if(!$productInfo){
			$this->error('该产品不存在!');
		}
		
		$this->assign('productInfo',$productInfo);
		$this->display();
	}
	// 产品修改保存
	public function editSave($product)
	{
		$model  = M('推荐');
		$data = $this -> getData('edit');
		$data['修改时间'] = time();
		$where['id'] = $_POST['id'];
		M()->startTrans();
		if($model->where($where)->save($data)){
			M()->commit();
			$this->success('修改成功!');
		}else{
			M()->rollback();
			$this->error('修改失败!');
		}
	}
	//产品删除
	public function delete($product){
		$model  = M('推荐');
		$succNum = 0;
		$errNum = 0;
		if(isset($_GET['id']))
		foreach(explode(',',$_GET['id']) as $id){
			if($id == '') continue;
			$where['id'] = $id;
			M()->startTrans();
			if($model->where($where)->delete()){
				$succNum++;
				M()->commit();
			}else{
				$errNum++;
				M()->rollback();
			}
		}
		if($errNum !=0){
			$this->error("删除成功：".$succNum .'条记录；删除失败：'.$errNum .'条记录；');
		}else{
			$this->success("删除成功：".$succNum .'条记录；');
		}
	}
}
?>