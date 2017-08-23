<?php
import("COM.Interface.DepotInterface");

/*
* 成品报废库
*
*/
class ScrappedProductDepot implements DepotInterface
{
	private $error_info		= '';  //错误信息
	private $in_table		= 'erp_scrapped_product_depot_in';	//入库表名
	private $out_table		= 'erp_scrapped_product_depot_out';	//出库表名
	private $depot_table	= 'erp_scrapped_product_depot';		//库存表名

	/**
	*
	*	废品入库
	*	$id					 = '要入库的成品表主键'
	*	$data['number']		 = '入库数量'
	*	$data['remarks']	 = '备注'
	*
	*/
	public function in($id,$data)
	{
		//检查是否有库存
		$data['endproduct_id']		= $id;		//成品表id
		$data['create_time']		= time();	//入库时间
		$data['admin_id']			= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//入库操作员
		$data['seria_number']		= $this->make_in_seria_number(4); //流水号
		$result						= M()->table($this->in_table)->add($data);
		if( $result )
		{
			//如果入库成功,则更新库存数
			$number = floatval($data['number']);
			return $this->update_stock($id,$number);
		}
		$this->error_info = '入库失败!';
		return false;
	}

	/**
	*
	*	废品出库
	*	$id					 = '要出库的成品表主键'
	*	$data['number']		 = '出库数量'
	*	$data['remarks']	 = '备注'
	*
	*/
	public function out($id,$data)
	{
		
		$where['endproduct_id']	= $id;

		//检查是否有库存
		$inventory						= $this->get_stock($id);
		$number							= floatval($data['number']);
		if( $inventory <= 0 || $inventory < $number )
		{
			$this->error_info = '库存不足!';
			return false;
		}
		$data['endproduct_id']			= $id;		//成品表主键
		$data['create_time']			= time();	//出库时间
		$data['admin_id']				= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//出库操作员
		$data['seria_number']			= $this->make_out_seria_number(4); //流水号
		$result							= M()->table($this->out_table)->add($data);
		if( $result ) 
		{
			//如果出库成功,则更新库存数
			$number = -$number;
			return $this->update_stock($id,$number);
		}
		$this->error_info = '出库失败!';
		return false;
	}

	/**
	*
	*	更新库存数
	*
	*	$id					 = '成品表主键'
	*	$number				 = '要更新的库存数'
	*
	*/
	public function update_stock($id,$number)
	{
		$where['endproduct_id']	= $id;

		//更新库存之前先检查是否已记录该物品
		$finded		= M()->table($this->depot_table)->where($where)->find();
		if( !$finded )
		{
			$data['endproduct_id']		= $id;
			$data['number']				= $number;
			return M()->table($this->depot_table)->add($data);
		}
		else
		{
			if( $number > 0 )
			{
				$data['number'] = array( 'exp' , 'number+'.floatval($number) ); //增加库存
				return M()->table($this->depot_table)->where($where)->save($data);
			}
			else if( $number < 0 )
			{
				$data['number'] = array( 'exp' , 'number'.floatval($number) ); //减少库存
				return M()->table($this->depot_table)->where($where)->save($data);
			}
			$this->error_info = '未更新库存数!';
			return false;
		}
	}

	/*
	*	获取库存数
	*
	*	$id					 = '生产任务单表主键'
	*/
	public function get_stock($id)
	{
		$where['endproduct_id']	= $id;
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
		//格式 年月日 + 4位流水号 如  GMI1312010001

		//流水号前缀
		$seria_number_prefix	= 'SPI'.date('ymd');

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
		//格式 年月日 + 4位流水号 如  GMI1312010001

		//流水号前缀
		$seria_number_prefix	= 'SPO'.date('ymd');

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