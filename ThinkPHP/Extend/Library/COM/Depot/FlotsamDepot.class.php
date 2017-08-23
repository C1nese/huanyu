<?php
import("COM.Interface.DepotInterface");

/*
* 废料仓库类
*
*/
class FlotsamDepot implements DepotInterface
{
	private $error_info		= '';  //错误信息
	private $in_table		= 'erp_flotsam_depot_in';		//入库表名
	private $out_table		= 'erp_flotsam_depot_out';	//出库表名
	private $depot_table	= 'erp_flotsam_depot';	//库存表名

	/**
	*
	*	废料入库
	*	$id					 = '登记表'
	*	$data['mixing_id'] = '拌料码' 一定要是数组
	*	$data['number']		 = '入库数量'
	*	$data['remarks']	 = '备注'
	*
	*/
	public function in($id,$data)
	{	
		$seria_number				= $this->make_in_seria_number(4); //流水号
		$val['seria_number']		= $seria_number;
		$number 	= $data['number'];
		$remarks	= $data['remarks'];
		
		foreach($data['mixing_id'] as $k=>$_id)
		{
			
			if(is_array($data['number'])){
				$val['number']		= $data['number'][$k];
			}
			
			if(is_array($remarks)){
				$val['remarks']	= $remarks[$k];
			}
			if($val['number']>0){
				//检查是否有库存
				$val['mixing_id']		= $_id;
				$val['register_id']		= $id;		//剩余表id
				$val['create_time']		= time();	//入库时间
				$val['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//入库操作员
				
		
				//根据登记id查出来,
				/*
				formula_id				关联订单配方表主键
				*/
				$mixing_reg				= M()->table('erp_flotsam_register')->find($id);
				if( $mixing_reg )
				{
					$val['product_task_id']	= $mixing_reg['product_task_id'];
				}
				$result					= M()->table($this->in_table)->add($val);
				//echo M()->getLastSql();
				if( $result )
				{
					//如果入库成功,则更新库存数
					$number = floatval($val['number']);
					$id1['id']			= $id;
					$id1['mixing_id']	= $_id;
					$false=$this->update_stock($id1,$number);
				}
			}
		}
		if($false){
			return $seria_number;
		}
		
		
		$this->error_info = '入库失败!';
		return false;
	}

	/**
	*	
	*/
	public function out($id,$data)
	{
		
		
	}

	/**
	*
	*	更新库存数
	*
	*	$id					 = '要更新的拌料表的主键id'
	*	$number				 = '要更新的库存数'
	*
	*/
	public function update_stock($id,$number)
	{

		
			$data['register_id']	= $id['id'];
			$data['mixing_id']		= $id['mixing_id'];
			$info	= M()->table('erp_flotsam_register')->find($id['id']);
			
			if( $info )
			{
				
				$data['product_task_id']	= $info['product_task_id'];
			}
			
			
			$data['number']			= $number;
			
			return M()->table($this->depot_table)->add($data);
			
	}

	//获取库存数
	public function get_stock($id)
	{
		$infoid=M()->table('erp_flotsam_register')->where(array('id'=>$id))->getField('product_task_id');
		
		$where['product_task_id']	= $infoid;
		$result	= M()->table($this->depot_table)->where($where)->find();
		if( $result )
		{
			return $result['number'];
		}
		else
		{
			return 0;
		}
	}

	/**
	*	生成入库流水号
	*
	*	$seria_number_suffix_length		= 流水号后缀的长度
	*
	*/
	public function make_in_seria_number($seria_number_suffix_length=4)
	{
		//格式 年月日 + 4位流水号 如  SI1312010001

		//流水号前缀
 		$seria_number_prefix	= 'FI'.date('ymd');

		$where['seria_number']	= array('like',"{$seria_number_prefix}%");
		$result					= M()->table($this->in_table)->where($where)->order('id desc')->find();

		//流水号后缀
		$seria_number_suffix = 0;

		//如果存在
		if( $result )
		{
			$existed_seria_number		= $result['seria_number'];
			//抽离出后面的流水号
			$seria_number_prefix_length	= strlen($seria_number_prefix);

			$seria_number_suffix		= substr($existed_seria_number,$seria_number_prefix_length);
			
			$seria_number_suffix_new	= intval($seria_number_suffix) + 1;

			$seria_number_suffix		= str_pad($seria_number_suffix_new, $seria_number_suffix_length, '0', STR_PAD_LEFT);
		}
		else
		{
			$seria_number_suffix		= str_pad('1', $seria_number_suffix_length, '0', STR_PAD_LEFT);
		}

		return $seria_number_prefix.$seria_number_suffix;
	}
	
	/**
	*	生成出库流水号
	*
	*	$seria_number_suffix_length		= 流水号后缀的长度
	*
	*/
	public function make_out_seria_number($seria_number_suffix_length=4)
	{
		//格式 年月日 + 4位流水号 如  SI1312010001

		//流水号前缀
		$seria_number_prefix	= 'FO'.date('ymd');

		$where['seria_number']	= array('like',"{$seria_number_prefix}%");
		$result					= M()->table($this->out_table)->where($where)->order('id desc')->find();

		//流水号后缀
		$seria_number_suffix = 0;

		//如果存在
		if( $result )
		{
			$existed_seria_number		= $result['seria_number'];
			//抽离出后面的流水号
			$seria_number_prefix_length	= strlen($seria_number_prefix);

			$seria_number_suffix		= substr($existed_seria_number,$seria_number_prefix_length);
			
			$seria_number_suffix_new	= intval($seria_number_suffix) + 1;

			$seria_number_suffix		= str_pad($seria_number_suffix_new, $seria_number_suffix_length, '0', STR_PAD_LEFT);
		}
		else
		{
			$seria_number_suffix		= str_pad('1', $seria_number_suffix_length, '0', STR_PAD_LEFT);
		}

		return $seria_number_prefix.$seria_number_suffix;
	}

	//返回错误信息
	public function get_error()
	{
		return $this->error_info;
	}
}
?>