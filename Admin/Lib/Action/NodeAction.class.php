<?php
// 节点模块
class NodeAction extends CommonAction 
{
	public $sortBy	= 'sort';
	public $asc		= true;

	function _filter(&$map)
	{
		$pid			= '';
		$level			= 1;
		$map['level']	= $level; //默认只查应用
	
	
		if( isset($_REQUEST['level']) && $_REQUEST['level']!='' )
		{
			$level			= $_REQUEST['level'];
			$map['level']	= $level; //默认只查应用
		}

		if( isset($_REQUEST['pid']) && $_REQUEST['pid']!='' )
		{
			$pid	= $_REQUEST['pid'];
			unset($map['level']); //取消级别查询
		}

		//查找上级的上级
		if( isset($_REQUEST['parent'])  )
		{
			$model			= M('Node');
			$where['id']	= intval($_REQUEST['parent']);
			$info			= $model->field('pid,level')->where($where)->find();
			
			$pid			= $info['pid'];
			$map['pid']		= $pid;
			if( $info['level'] == 1 )
			{
				$pid		= '';
			}
			unset($map['level']); //取消级别查询
		}



		$this->assign('pid',$pid);
		$this->assign('level',$level);
	}

	/*
	* 删除前置方法
	*/
	public function _filter_delete_before ( $model , $id)
	{
		//找出删除的节点的level
		$where['id']	= $id;
		$info			= $model->where($where)->find();
		if( $info['level']=='1' )
		{
			$this->deleteModuleByApp($id);
		}
		else if( $info['level']=='2' )
		{
			$this->deleteActionByModule($id);
		}
	}


	//修改的前置方法
	public function _before_edit()
    {
	   //找出
		$Model			= M('Node');
		$where['id']	= $_REQUEST['id'];
		$info			= $Model->where($where)->find();

		$pid			= '';
		$args_json		= '';

		//还原args
		if( $info['args'] != '' )
		{
			$args_json		= '{';
			$args			= base64_decode($info['args']);
			$args			= json_decode($args);
			
			foreach( $args as $key=>$val )
			{
				$args_json	.= "\"$key\":\"$val\"";
			}
			$args_json		.= "}";
		}

		

		if( isset($_REQUEST['pid']) && $_REQUEST['pid'] !='' )
		{
			$pid		= $_REQUEST['pid'];
		}

		if( $info['level'] == 3 )
		{
			//如果是操作节点,这里要找出他的应用的id
			$where2['id']	= $info['pid'];
			$app_id			= $Model->where($where2)->getField('pid');
			$this->assign('app_id',$app_id);
		}

		$this->assign('pid',$pid);
		$this->assign('args_json',$args_json);
	}


	/*
	* 修改保存前置方法
	*/
	function _filter_update_before(&$model)
	{
		if( $model->level == 2 )
		{
			if( !isset($_POST['pid2']) || $_POST['pid2'] == '' )
			{
				$this->error("未选择上级");
			}
			$model->pid = $_POST['pid2'];
		}
		else if( $model->level == 3 )
		{
			if( !isset($_POST['pid3']) || $_POST['pid3'] == '' )
			{
				$this->error("未选择上级");
			}
			$model->pid = $_POST['pid3'];
		}

		//取消括号转义
		$model->args	= $_REQUEST['_args']!='' ? base64_encode( stripcslashes( $_REQUEST['_args'] ) ):'';
	}

	/*
	* 插入前置方法
	*/
	function _filter_insert_before(&$model)
	{
		if( $model->level == 2 )
		{
			if( !isset($_POST['pid2']) || $_POST['pid2'] == '' )
			{
				$this->error("未选择上级");
			}
			$model->pid = $_POST['pid2'];
		}
		else if( $model->level == 3 )
		{
			if( !isset($_POST['pid3']) || $_POST['pid3'] == '' )
			{
				$this->error("未选择上级");
			}
			$model->pid = $_POST['pid3'];
		}
		$model->args	= isset($_REQUEST['_args'])?base64_encode(json_encode($_REQUEST['_args'])):'';
	}

	/*
	* 加载模块节点
	*/
	public function ajaxLoadModule()
	{
		$Model			= M('Node');
		$where['level'] = 2;
		$where['pid']	= intval($_REQUEST['id']);
		$list			= $Model->field('id,title')->where($where)->select();
		if( !$list ) $list = array();
		$this->ajaxReturn($list,'ok',1);
	}

	/*
	* 删除应用下的模块、和操作
	*/
	private function deleteModuleByApp($id)
	{
		//找出该应用下的所有模块
		$model				= M('Node');
		$where['pid']		= $id;
		$where['level']		= 2;
		$moduleList			= $model->field('id')->where($where)->select();
		foreach( $moduleList as $module )
		{
			M()->startTrans();
			$this->deleteActionByModule( $module['id'] );

			//删除模块
			$model->where($where)->delete();
			M()->commit();
		}
	}


	/*
	* 删除模块下的操作
	*/
	private function deleteActionByModule($id)
	{
		$where['pid']		= $id;
		$where['level']		= 3;
		$model				= M('Node');
		M()->startTrans();
		$model->where($where)->delete();
		M()->commit();
	}

}
?>