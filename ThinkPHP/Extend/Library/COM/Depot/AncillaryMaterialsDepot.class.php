<?php
import("COM.Interface.DepotInterface");

/*
* 辅料仓库类
*
*/
class AncillaryMaterialsDepot implements DepotInterface
{
	private $error_info		= '';  //错误信息
	private $in_table		= 'erp_ancillary_materials_depot_in';	//入库表名
	private $out_table		= 'erp_ancillary_materials_depot_out';	//出库表名
	private $depot_table	= 'erp_ancillary_materials_depot';		//库存表名

	/**
	*
	*	辅料入库
	*	$id					 = '要入库的辅料表主键'
	*	$data['number']		 = '入库数量'
	*	$data['type']		 = '入库类型'		  可选项：采购
	*	$data['remarks']	 = '备注'
	*	$data['standard']	 = '规格'
	*
	*/
	public function in($id,$data)
	{
		$seria_number			= $this->make_in_seria_number(4); //流水号
		$data['seria_number']	= $seria_number;
		if(is_array($id)){
			$remark=$data['remarks'];
			$numbe=$data['number'];
			$guige=$data['standard'];
			$receive=$data['receive_id'];
			foreach($id as $k=>$_id){
				if(is_array($remark)){
					$data['remarks']=$remark[$k];
				}
				if(is_array($numbe)){
					$data['number']=$numbe[$k];
				}
				if(is_array($receive)){
					$data['receive_id']=$receive[$k];
				}
				if(is_array($guige)){
					$data['standard']=$guige[$k];
				}
				$data['ancillary_id']	= $_id;		//辅料id
				$data['create_time']	= time();	//入库时间
				$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//入库操作员
				$result				= M()->table($this->in_table)->add($data);
				if( $result )
				{
					//如果入库成功,则更新库存数
					$number = floatval($data['number']);
					$id1['id']=$_id;
					$id1['standard']=$data['standard'];
					$this->update_stock($id1,$number);
				}
			}
			return $seria_number;
		}else{
			//检查是否有库存
			$data['ancillary_id']	= $id;		//辅料id
			$data['create_time']	= time();	//入库时间
			$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//入库操作员
			$result					= M()->table($this->in_table)->add($data);
			if( $result )
			{
				//如果入库成功,则更新库存数
				$number = floatval($data['number']);
				$id['id']=$id;
				$id['standard']=$data['standard'];
				$this->update_stock($id,$number);
				return $seria_number;
			}
		}
		
		$this->error_info = '入库失败!';
		return false;
	}

	/**
	*
	*	辅料出库
	*	$id					 = '要出库的辅料表主键'
	*	$data['number']		 = '出库数量'
	*	$data['type']		 = '出库类型'		  可选项：拌料
	*	$data['remarks']	 = '备注'
	*	$data['standard']	 = '规格'
	*
	*/
	public function out($id,$data)
	{
		
		$seria_number			= $this->make_out_seria_number(4); //流水号
		$data['seria_number']	= $seria_number;
		if(is_array($id)){
			$remark	= $data['remarks'];
			$numbe	= $data['number'];
			$guige	= $data['standard'];
			$type	= $data['type'];
			foreach($id as $k=>$_id)
			{
				if(is_array($remark)){
					$data['remarks']	= $remark[$k];
				}
				if(is_array($numbe)){
					$data['number']		= $numbe[$k];
				}
				if(is_array($guige)){
					$data['standard']	= $guige[$k];
				}
				if(is_array($type)){
					$data['type']		= $type[$k];
				}
				$where['ancillary_id']	= $_id;
				
				//检查是否有库存
				$id1['id']=$_id;
				$id1['standard']=$data['standard'];
				$inventory				= $this->get_stock($id1);
				$number					= floatval($data['number']);
				if( $inventory <= 0 || $inventory < $number )
				{
					$this->error_info = '库存不足!';
					return false;
				}
				$data['ancillary_id']	= $_id;		//辅料id
				$data['create_time']	= time();	//出库时间
				$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//出库操作员
				$result					= M()->table($this->out_table)->add($data);
				if( $result )
				{
					//如果出库成功,则更新库存数
					$number = -$number;
					$id['id']=$_id;
					$id['standard']=$data['standard'];
					$this->update_stock($id,$number);
				}
			}
			return $seria_number;
		}else{
			$where['ancillary_id']	= $id;

			//检查是否有库存
			$_id['id']=$id;
			$_id['standard']=$data['standard'];
			$inventory				= $this->get_stock($_id);
			$number					= floatval($data['number']);
			if( $inventory <= 0 || $inventory < $number )
			{
				$this->error_info = '库存不足!';
				return false;
			}
			$data['ancillary_id']	= $id;		//辅料id
			$data['create_time']	= time();	//出库时间
			$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//出库操作员
			$result					= M()->table($this->out_table)->add($data);
			if( $result ) 
			{
				//如果出库成功,则更新库存数
				$number = -$number;
				$id['id']=$id;
				$id['standard']=$data['standard'];
				$this->update_stock($id,$number);
				return $seria_number;
			}
		}
		
		
		$this->error_info = '出库失败!';
		return false;
	}

	/**
	*
	*	更新库存数
	*
	*	$id					 = '数组 array('id'=>id, 'standard'=>辅料规格)'
	*	$number				 = '要更新的库存数'
	*	$standard			 = '规格'
	*
	*/
	public function update_stock($id,$number)
	{
		$where['ancillary_id']	= $id['id'];
		$where['standard']		= $id['standard'];
		//更新库存之前先检查是否已记录该物品
		$finded		= M()->table($this->depot_table)->where($where)->find();
		if( !$finded )
		{
			$data['ancillary_id']	= $id['id'];
			$data['number']			= $number;
			$data['standard']		= $id['standard'];
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

	//获取库存数
	public function get_stock($id)
	{
		$where['ancillary_id']	= $id['id'];
		$where['standard']		= $id['standard'];
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
		$seria_number_prefix	= 'AMI'.date('ymd');

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
		$seria_number_prefix	= 'AMO'.date('ymd');

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