<?php

	class prize_givedownt extends prize
	{
		//奖金产生类型0为不产生,1为产生,2为扣除
		public $prizeMode = 1;
		//网络名称
		public $netName = '';
		//来源表条件
		public $where = '';
		//用户条件,不限制可设置1
		public $getwhere="状态='有效'";
		//来源表达式
		public $rowName = '';
		//来源表达式
		public $rowFrom = 0;
		//起征数量
		public $startNum=0;
		//起征字段
		public $startRow='';
		//构成信息
		public $composition= false;
			//判断是否显示奖金构成
		public $isSee = true;
		//构成信息
		public $conFilter=array('con'=>array("minlayer","maxlayer","minlv","maxlv","val","where","weighing",'isSee'));
		function cal()
		{
			if(!$this->ifrun()) return;
			$net = X('*@'.$this->netName);
			if($net === NULL)
			{
				throw_exception($this->name."计算时网络体系获取失败,请检查其netName设置是否正确");
			}			
			$rec_maxlayer = 0;
			$cons = $this->getcon('con',array("minlayer"=>1,"maxlayer"=>1,"minlv"=>1,"maxlv"=>1,"val"=>"","where"=>"","weighing"=>''));
			//找出计算的最大层数
			foreach($cons as $con){
				if($con['maxlayer']>$rec_maxlayer)
					$rec_maxlayer=$con['maxlayer'];
			}
			$calusers=array();//参与计算用户数组
			$users=M("用户")->where(delsign($this->where))->order($this->netName."_层数 desc")->getField("编号 as kid,id,编号,".$this->lvName.",".$this->rowName.",".$this->netName."_上级编号,推荐_层数");
			foreach($users as $username=>$user){
				$upname=$user[$this->netName."_上级编号"];//上级编号
				$continu=true;
				$layer=1;//层数
				while($continu){
					if(isset($users[$upname])){
						$upuser=$users[$upname];//上级用户信息
						//是否有奖金计算
						if($upuser[$this->rowName]>0){
							foreach($cons as $ckey=>$con){
								$wheredata=array("M"=>$user,"U"=>$upuser);
								if($layer>=$con['minlayer'] && $layer<=$con['maxlayer'] && transform($con['where'],array(),$wheredata)){
									$calusers[$upname][$layer][]=$user;//记录当层拿奖用户信息
									$layer++;
								}
							}
						}
						//再次往上找
						if(isset($upuser[$this->netName."_上级编号"])){
							$upname=$upuser[$this->netName."_上级编号"];
						}else{
							$continu=false;//跳出while继续
						}
						unset($upuser);
						if($layer>$rec_maxlayer){
							$continu=false;//跳出while继续
						}
					}else{
						$continu=false;//跳出while继续
					}
				}
			}
			//计算奖金 网体 参与计算的数据 用户信息 最大层数 con数组
			$this->calculate($net,$calusers,$users,$rec_maxlayer,$cons);
			unset($users);
			unset($calusers);
			unset($cons);
			$this->prizeUpdate();
		}
		//计算处理
		public function calculate($net,&$calusers,$users,$rec_maxlayer,&$cons)
		{
			foreach($calusers as $upuname=>$dusers){
				$t_num=$users[$upuname][$this->rowName];//
				//循环节点计算金额
				foreach($cons as $con)
				{
					$startlayer=$con['minlayer'];
					$endlayer=$con['maxlayer'];
					$prizenum  = getnum($t_num,$con['val']);//平分的金额
					//循环层数找出当层的计算用户
					for($i=$startlayer;$i<=$endlayer;$i++){
						if(!isset($dusers[$i]))
							continue;
						$ndownusers=$dusers[$i];//参与拿奖用户
						$usercount=count($dusers[$i]);
						//循环拿奖
						foreach($ndownusers as $downuser)
						{
							$prize = $prizenum/$usercount;
							//生成构成信息
							$calculateType = $prizenum .'/'.$usercount.'人';
							$this->addprize($downuser,$prize,$users[$upuname],$calculateType,$i);
						}
					}
				}
			}
			unset($downusers);
			unset($user);
		}
	}
?>