<?php
	class net extends stru
	{
		//注册时是否显示
		public $regDisp = true;
		//非正式用户是否可以作为上级
		public $nullUp = false;
		//必须存在上级
		public $mustUp = true;
		//如果开启此属性，则自动会将前台注册的net_rec的注册功能关闭.并以当前用户作为默认人
		public $setNowUser =false;
		//计算推荐人数的周期,0为生效立即计算,1为订单结算周期期间计算
		public $sumMode = 0;
		//计算推荐人数的条件
		public $sumWhere = '';
		public $shopNetDisp=true;   //店铺是否显示网络图
		public $shopListDisp=true;  //店铺是否显示列表
		public $userNetDisp=true;
		public $userListDisp=true;
		public $treeDisp=array();
		public $adminNetLayer = 4;
		public $userNetLayer = 4;
		public $userLookLayer = 0;
		public $shopLookLayer = 0;//店铺前台显示层数
		public $shopNetLayer=4;  //店铺的前台显示层数
		
		public $userNameDisp = true;//用户姓名
		public $userAnotherNameDisp = true;//用户别名
		public $userauto= true;
		//此属性为netplace专用,表示是否要在用户点位上显示业绩表格
		public $userBgxs= true;
		
		public function lvHave($userid)
		{
			$where['编号']=$userid;
            $where[$this->name."_层数"]=array("gt",0);
			$rs=M('用户','dms_')->lock(true)->where($where)->find();
			if($rs)
			{
				$_POST['net_'.$this->getPos()] = $rs['编号'];
				return true;
			}else{
			    return false;
			}
		}
		//判断特定上级编号是否不符合where的条件,用于net节点中的_lock标签的判定
		public function ifLock($userid,$where)
		{
			if($userid=='')
			{
				return true;	
			}
			$upuser=M('用户','dms_')->lock(true)->where(array('编号'=>$userid))->find();
			if(!$upuser)
			{
				return true;
			}
			else
			{
				return !transform($where,$upuser);
			}
		}
	}
?>