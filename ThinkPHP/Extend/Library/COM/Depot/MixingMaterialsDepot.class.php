<?php
import("COM.Interface.DepotInterface");

/*
* ���ϲֿ���
*
*/
class MixingMaterialsDepot implements DepotInterface
{
	private $error_info		= '';  //������Ϣ
	private $in_table		= 'erp_mixing_materials_depot_in';	//������
	private $out_table		= 'erp_mixing_materials_depot_out';	//�������
	private $depot_table	= 'erp_mixing_materials_depot';		//������

	/**
	*
	*	�������
	*	$id					 = 'Ҫ���İ��ϱ�����'
	*	$data['number']		 = '�������'
	*	$data['type']		 = '�������'		  ��ѡ�����
	*	$data['remarks']	 = '��ע'
	*
	*/
	public function in($id,$data)
	{
		//����Ƿ��п��
		$data['mixing_id']		= $id;			//����id
		$data['create_time']	= time();	//���ʱ��
		$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//������Ա
		$data['seria_number']	= $this->make_in_seria_number(4); //��ˮ��

		//���ݰ���id�����,
		/*
		production_task_id		�����������������
		formula_id				���������䷽������
		layer					���:�ڡ��⡢��
		*/
		$mixing_reg				= M()->table('erp_mixingregister')->find($id);
		if( $mixing_reg )
		{
			$data['production_task_id']	= $mixing_reg['production_task_id'];
			$data['formula_id']			= $mixing_reg['formula_id'];
			$data['layer']				= $mixing_reg['layer'];
		}
		
		$result					= M()->table($this->in_table)->add($data);
		if( $result )
		{
			//������ɹ�,����¿����
			$number = floatval($data['number']);
			return $this->update_stock($id,$number);
		}
		$this->error_info = '���ʧ��!';
		return false;
	}

	/**
	*
	*	���ϳ���
	*	$id					 = 'Ҫ����İ��ϱ�����'
	*	$data['number']		 = '��������'
	*	$data['type']		 = '��������'		  ��ѡ���������
	*	$data['remarks']	 = '��ע'
	*
	*/
	public function out($id,$data)
	{
		
		$where['mixing_id']	= $id;

		//����Ƿ��п��
		$inventory				= $this->get_stock($id);
		$number					= floatval($data['number']);
		if( $inventory <= 0 || $inventory < $number )
		{
			$this->error_info = '��治��!';
			return false;
		}
		$data['mixing_id']		= $id;		//����id
		$data['create_time']	= time();	//����ʱ��
		$data['admin_id']		= $_SESSION [C ( 'RBAC_ADMIN_AUTH_KEY' )];		//�������Ա
		$data['seria_number']	= $this->make_out_seria_number(4); //��ˮ��

		//���ݰ���id�����,
		/*
		production_task_id		�����������������
		order_formula_id		���������䷽������
		layer					���:�ڡ��⡢��
		*/
		$mixing_reg				= M()->table('erp_mixingregister')->find($id);
		if( $mixing_reg )
		{
			$data['production_task_id']	= $mixing_reg['production_task_id'];
			$data['formula_id']			= $mixing_reg['formula_id'];
			$data['layer']				= $mixing_reg['layer'];
		}

		$result					= M()->table($this->out_table)->add($data);
		if( $result ) 
		{
			//�������ɹ�,����¿����
			$number = -$number;
			return $this->update_stock($id,$number);
		}
		$this->error_info = '����ʧ��!';
		return false;
	}

	/**
	*
	*	���¿����
	*
	*	$id					 = 'Ҫ���µİ��ϱ������id'
	*	$number				 = 'Ҫ���µĿ����'
	*
	*/
	public function update_stock($id,$number)
	{
		$where['mixing_id']	= $id;


		//���¿��֮ǰ�ȼ���Ƿ��Ѽ�¼����Ʒ
		$finded		= M()->table($this->depot_table)->where($where)->find();
		if( !$finded )
		{
			//���ݰ���id�����,
			/*
			production_task_id		�����������������
			order_formula_id		���������䷽������
			layer					���:�ڡ��⡢��
			*/
			$mixing_reg						= M()->table('erp_mixingregister')->find($id);
			if( $mixing_reg )
			{
				$data['production_task_id']	= $mixing_reg['production_task_id'];
				$data['formula_id']			= $mixing_reg['formula_id'];
				$data['layer']				= $mixing_reg['layer'];
			}

			$data['mixing_id']		= $id;
			$data['number']			= $number;
			return M()->table($this->depot_table)->add($data);
		}
		else
		{
			if( $number > 0 )
			{
				$data['number'] = array( 'exp' , 'number+'.floatval($number) ); //���ӿ��
				return M()->table($this->depot_table)->where($where)->save($data);
			}
			else if( $number < 0 )
			{
				$data['number'] = array( 'exp' , 'number'.floatval($number) ); //���ٿ��
				return M()->table($this->depot_table)->where($where)->save($data);
			}
			$this->error_info = 'δ���¿����!';
			return false;
		}
	}

	//��ȡ�����
	public function get_stock($id)
	{
		$where['mixing_id']	= $id;
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
	*	���������ˮ��
	*
	*	$seria_number_suffix_length		= ��ˮ�ź�׺�ĳ���
	*
	*/
	public function make_in_seria_number($seria_number_suffix_length=4)
	{
		//��ʽ ������ + 4λ��ˮ�� ��  GMI1312010001

		//��ˮ��ǰ׺
		$seria_number_prefix	= 'MI'.date('ymd');

		$where['seria_number']	= array('like',"{$seria_number_prefix}%");
		$result					= M()->table($this->in_table)->where($where)->order('id desc')->find();

		//��ˮ�ź�׺
		$seria_number_suffix = 0;

		//�������
		if( $result )
		{
			$existed_seria_number		= $result['seria_number'];
			//������������ˮ��
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
	*	���ɳ�����ˮ��
	*
	*	$seria_number_suffix_length		= ��ˮ�ź�׺�ĳ���
	*
	*/
	public function make_out_seria_number($seria_number_suffix_length=4)
	{
		//��ʽ ������ + 4λ��ˮ�� ��  GMI1312010001

		//��ˮ��ǰ׺
		$seria_number_prefix	= 'MO'.date('ymd');

		$where['seria_number']	= array('like',"{$seria_number_prefix}%");
		$result					= M()->table($this->out_table)->where($where)->order('id desc')->find();

		//��ˮ�ź�׺
		$seria_number_suffix = 0;

		//�������
		if( $result )
		{
			$existed_seria_number		= $result['seria_number'];
			//������������ˮ��
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

	//���ش�����Ϣ
	public function get_error()
	{
		return $this->error_info;
	}
}
?>