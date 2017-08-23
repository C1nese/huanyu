<?php
/*

用法:
import("COM.Depot.Depot");

$EndProductDepot = new Depot("EndProduct");
$EndProductDepot->in();
$EndProductDepot->out();
*/

/**
 * 仓库实用类
 */
class Depot extends Model
{
	private $deport_class = null;  //实例化的仓库类

	public function __construct($depot_name = '') 
	{
		if( $depot_name ) 
		{
			$this->load($depot_name);
		}
    }

	/*
	* 加载仓库
	*/
	public function load($depot_name)
	{
		$import = import("COM.Depot.{$depot_name}");
		if( !$import )
		{
			exit('无法加载仓库类:'.$depot_name);
		}
		
		$this->deport_class  = new $depot_name();
	}

	/*
	* 检查仓库是否设置
	*/
	public function check()
	{
		if( !$this->deport_class  )
		{
			exit('未加载仓库! 用法: 对象->load("仓库类") ');
		}
	}
	
	/*
	* 入库
	*/
	public function in($id,$data)
	{
		$this->check();
		return $this->deport_class->in($id,$data);
	}

	/*
	* 出库
	*/
	public function out($id,&$data)
	{
		$this->check();
		return $this->deport_class->out($id,$data);
	}

	//获取库存数
	public function get_stock($where)
	{
		$this->check();
		return $this->deport_class->get_stock($data);
	}

	//获取错误提示
	public function get_error()
	{
		$this->check();
		return $this->deport_class->get_error();
	}
}
?>
