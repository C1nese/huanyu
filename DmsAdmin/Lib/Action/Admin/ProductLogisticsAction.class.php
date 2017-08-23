<?php
//产品快递--产品物流费
class ProductLogisticsAction extends CommonAction {
	/**
    +----------------------------------------------------------
    * 产品快递
    +----------------------------------------------------------
    */
    function express(){
        $setButton=array(
			'添加'=>array("class"=>"add","href"=>"__URL__/addexpress","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"340"),
			'修改'=>array("class"=>"edit","href"=>"__URL__/editexpress/id/{tl_id}","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"340"),
            '删除'=>array("class"=>"delete","href"=>"__URL__/delexpress"."/id/{tl_id}","target"=>"ajaxTodo" ,"title"=>"确定要删除吗?"),
        );
        $setShow = array(
            '公司名称'=>array('row'=>'[company]'),
            '联系人'=>array('row'=>'[contact]'),
            '联系电话'=>array('row'=>'[tel]'),
            '网址'=>array('row'=>'[url]'),
            '地址'=>array('row'=>'[address]'),
            '添加时间'=>array('row'=>'[addtime]','format'=>'date'),
			'是否可用'=>array('row'=>'[state]'),
        );
        $list=new TableListAction("快递");
        $list->setShow = $setShow;         // 定义列表显示
        $list->setButton = $setButton;     // 定义按钮显示
        $list->order("id asc"); 
        $this->assign('list',$list->getHtml()); 
        $this->display();
    }
    // 添加
    function addexpress(){
       $this->display();
    }
	 //保存
    function saveExpress(){
        $express = M('快递');
        $data['company'] = $_POST['company'];
        $data['contact'] = $_POST['contact'];
        $data['tel'] = $_POST['tel'];
   	  	$data['address'] = $_POST['address'];
   	  	$data['url'] = $_POST['url'];
   	  	$data['state'] = $_POST['state'];
   	  	$data['addtime'] = systemTime();
        
        if($express->add($data)){
          	$this->success("添加成功",'__URL__/express');
        }else{
            $this->error("添加失败");
        }  
    }
	//修改
	function editexpress(){
		if(strpos($_GET['id'],',') !== false){
			$this->error('参数错误!');
		}
		$express = M('快递')->find($_REQUEST['id']);
		$this->assign('express',$express);
		$this->display();
	}
	function saveEditexpress(){
		$express = M('快递');
		$data['id'] = $_POST['id'];
        $data['company'] = $_POST['company'];
        $data['contact'] = $_POST['contact'];
        $data['tel'] = $_POST['tel'];
   	  	$data['address'] = $_POST['address'];
   	  	$data['url'] = $_POST['url'];
   	  	$data['state'] = $_POST['state'];
   	  	$data['addtime'] = systemTime();
        M()->startTrans();
        if($express->save($data)){
        	M()->commit();
            $this->success("修改成功",'__URL__/express');
        }else{
        	M()->rollback();
            $this->error("修改失败");
        }
	}	
	//删除
	function delexpress(){
		if(strpos($_GET['id'],',') !== false){
			$this->error('参数错误!');
		}
		M()->startTrans();
		$express = M('快递')->delete($_REQUEST['id']);
		M()->commit();
		$this->success("删除成功",'__URL__/express');
	}	
	
	/**
    +----------------------------------------------------------
    * 产品物流费
    +----------------------------------------------------------
    */
	public function index(){
		$list = new TableListAction("产品物流管理");
		$list ->setButton = array(
			"添加"=>array("class"=>"add"   ,"href"=>__APP__."/Admin/ProductLogistics/add"              ,"target"=>"dialog"  ,"mask"=>"true",'width'=>'600','height'=>'400'),
			"修改"=>array("class"=>"edit"  ,"href"=>__APP__."/Admin/ProductLogistics/edit/id/{tl_id}"  ,"target"=>"dialog"  ,"mask"=>"true",'width'=>'600','height'=>'400'),
			"删除"=>array("class"=>"delete","href"=>__APP__."/Admin/ProductLogistics/delete/id/{tl_id}","target"=>"ajaxTodo","mask"=>"true","title"=>"确定要删除该数据吗？"),	
		);	
		$list->addshow('发往省份',array('row'=>'[发往省份]',"searchMode"=>"text",'searchPosition'=>'top'));
		$list->addshow("全国标准",array("row"=>"[是否全国标准]","searchMode"=>"text",'searchRow'=>'是否全国标准','format'=>'bool','order'=>'是否全国标准',"searchSelect"=>array("是"=>"1","否"=>"0"),));
		$list->addshow('产品首重(kg)',array('row'=>'[首重]',"searchMode"=>"num",'searchPosition'=>'top'));
		$list->addshow('产品首重价格',array('row'=>'[首重价格]',"searchMode"=>"num"));
		$list->addshow('产品续重价格（每kg）',array('row'=>'[续重价格]',"searchMode"=>"num"));
	
		$this->assign('list',$list->getHtml());
		$this->display ();
	}
	// 表单数据
	private function getData(){
		$data = array();
		$data['国家'] = $_POST['country'];
		$data['发往省份'] = $_POST['country_sheng'];
		$data['首重'] = $_POST['first_zhong'];
		$data['首重价格'] = $_POST['first_price'];
		$data['续重价格'] = $_POST['more_price'];
		$data['是否全国标准'] = $_POST['is_biaozhun'];
		return $data;
	}
	//添加
	public function add(){
		$this->display();
	}
	//添加保存
	public function addSave()
	{
		$model  = M("产品物流管理");
		$data = $this -> getData();
		if($model->add($data)){
			$this->success('添加成功!');
		}else{
			$this->error('添加失败!');
		}
	}
	//修改
	public function edit(){
		if(strpos($_GET['id'],',') !== false){
			$this->error('参数错误!');
		}
		$id = $_GET['id'];	
		$logistics =  M("产品物流管理");
		$logisticsList = $logistics->find($id);
		$this->assign('productInfo',$logisticsList);
		$this->display();
	}

	//修改保存
	public function editSave()
	{
		$model  = M("产品物流管理");
		$data = $this -> getData();
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
	//删除
	public function delete(){
		$model  = M("产品物流管理");
		$succNum = 0;
		$errNum = 0; 
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