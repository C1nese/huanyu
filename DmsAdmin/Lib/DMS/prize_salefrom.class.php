<?php

	class prize_salefrom extends prize
	{
		//产生类型
		public $prizeMode=1;
		//产生体系
		public $username='';
		public $rowFrom =1;
		//奖金来源表达式
		public $rowName = '';
		//来源表条件
		public $where = '';
		//订单来源状态下的订单类别
		public $saleState = '已结算,已确认';
		//构成信息
		public $composition= true;
		//小数精度
		public $decimalLen = 2;
	//判断是否显示奖金构成
		public $isSee = true;
		//public function scal($sale)
		//{
		//	$otherwhere=$this->where;
		//	$this->where=$otherwhere." and id=".$sale["id"];
		//	$this->cal();
		//	$this->where=$otherwhere;
		//}
		//结算执行

		//秒结算驱动
		function scal($sale)
		{
			//如果是通过用户表中产生奖金,可以直接走CAL流程
			if($this->rowFrom==0)
			{
				$this->cal();
			}
			else
			{
				if($this->where=="")
				{
					//如果判定没有任何条件,则将条件设置为id等于当前订单
					//这样此奖金只计算当前审核的订单
					$this->where = "id=" . $sale["id"];
					//执行结算
					$this->cal();
					//结算后还原条件
					$this->where = "";
				}
				else
				{
					//如果存在where设定,则使用临时变量存储.
					//并且在原条件中增加对ID的判断,结算完成后,在还原到回原始的where条件
					$otherwhere = $this->where;
					$this->where = '('.$otherwhere . ") and id=".$sale["id"];
					$this->cal();
					$this->where = $otherwhere;
				}
			}
		}

		public function cal()
		{
			if(!$this->ifrun()) return;
			$levels  = X('levels@'.$this->lvName);
			$cons  = $this->getcon('con',array("minlv"=>0,"maxlv"=>0,'val'=>'','where'=>''));
			if($this->rowFrom==0){
				$fromusername='服务中心_上级编号';
				$sales=$this->getuser($this->where,"*,$this->rowName as t_recnum");
			}else{
				$fromusername='报单中心编号';
				$sales=$this->getsale($this->where,"*,$this->rowName as t_recnum",$this->username);
			}
			if(isset($sales))
            foreach($sales as $sale)
			{
				//来源编号，通过逗号拆分
				//循环，在通过_拆分
				//0单元等于X('user')->name
				//根据1单元找到用户
				//$userid=str_replace(X('user')->name.'_','',$sale['来源编号']);
				foreach(explode(',',$sale[$fromusername]) as $resource_sale)
				{
					if($resource_sale !='')
					{
						$fromuser = M('用户')->where(array('编号'=>$sale['编号']))->find();
						$where="编号='".$resource_sale."'";
						$users = $this->getuser($where);
						$user=$users[0];
                        if($this->name=='推店奖' && isset($user['服务中心推荐人'])){
                            $user=M('用户')->where("编号='".$user['服务中心推荐人']."'")->find();
                        }
						//进行验证的订单 如果是来源用户表那么报单记录是不存在的
							if($this->rowFrom==0){
							  $yanzhengsale = null;
							}else{
							  $yanzhengsale = $sale;
							}
						$wheredata=array('U'=>$fromuser,'M'=>$user,'S'=>$yanzhengsale);
						foreach($cons as $con)
						{
							  if($user[$this->lvName] !='' && $user[$this->lvName]>=$con['minlv'] && $user[$this->lvName]<=$con['maxlv'] && transform($con['where'],array(),$wheredata))
								{
									$comstr=substr($con['val'],-1,1) == '%'?$sale['t_recnum'].'*'.$con['val']:'';
									$comstr.='从'.$this->username.':'.$sale['编号'];
									$num=getnum($sale['t_recnum'],$con['val']);
								    $this->addprize($user,$num,$fromuser,$comstr,0);
								}
						}
						
					}
				}
			}
			$this->prizeUpdate();
			unset($sales);
		}
	}
?>