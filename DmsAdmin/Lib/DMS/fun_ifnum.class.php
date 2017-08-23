<?php
	class fun_ifnum extends stru
	{
		//是否允许插入编号空缺
		public $insert = false;
		//进入排号的条件
		public $where = "1=1";
		//当这个人被删除.要对后续人前移
		public $deleteMove = false;
		//当这个人后期不符合条件，是否要移除网络
		public $noWhereMove = false;
		//最多排号
		public $maxNum = 0;
		//用户呗审核的事件入口
		public function event_user_verify($user)
		{
			if(!transform($this->where,$user))
			{
				return;
			}
			$maxnum=M('用户','dms_')->Max($this->name);
			$maxnum++;
			if($maxnum<=$this->maxNum || $this->maxNum==0){
			M('用户','dms_')->where(array('编号'=>$user['编号']))->save(array($this->name=>$maxnum));
			}else{
				return;
			}
		}
		//秒结奖金入口
		public function event_scal()
		{
			if($this->noWhereMove)
			{
				$upnum=M('用户')->where("{$this->name}!=0 and not (".delsign($this->where).")")->save(array($this->name=>'0'));
				//是否紧缩处理
				if($this->deleteMove && $upnum != 0)
				{
					M()->execute("update `dms_用户` inner join (SELECT @rowid:=@rowid+1 as rowid ,id FROM `dms_用户`, (SELECT @rowid:=0) as init where dms_用户.{$this->name}>0 ORDER BY dms_用户.{$this->name} asc) b on `dms_用户`.id=b.id set `dms_用户`.{$this->name}=b.rowid");
				}
			}
			$users=M('用户')->where("{$this->name}=0 and (".delsign($this->where).")")->limit($this->maxNum)->select();
			foreach($users as $user)
			{
				if(!transform($this->where,$user))
				{
					return;
				}
				$maxnum=M('用户','dms_')->Max($this->name);
				$maxnum++;
				if($maxnum<=$this->maxNum){
					M('用户','dms_')->where(array('编号'=>$user['编号']))->save(array($this->name=>$maxnum));
				}else{
					return;
				}
			}
		}
		//对用户资料保存的事件处理
		public function event_usersave($user)
		{
			if($user[$this->name]!=0 && !transform($this->where,$user) && $this->noWhereMove)
			{
				$upnum=M('用户')->where("{$this->name}!=0 and not (".delsign($this->where).")")->save(array($this->name=>'0'));
				//是否紧缩处理
				if($this->deleteMove && $upnum != 0)
				{
					M()->execute("update `dms_用户` inner join (SELECT @rowid:=@rowid+1 as rowid ,id FROM `dms_用户`, (SELECT @rowid:=0) as init where `dms_用户`.{$this->name}>0 ORDER BY `dms_用户`.{$this->name} asc) b on `dms_用户`.id=b.id set `dms_用户`.{$this->name}=b.rowid");
				}
			}
			if($user[$this->name]==0 && transform($this->where,$user))
			{
				$maxnum=M('用户','dms_')->Max($this->name);
				$maxnum++;
				if($maxnum<=$this->maxNum){
					M('用户','dms_')->where(array('编号'=>$user['编号']))->save(array($this->name=>$maxnum));
				}else{
					return;
				}
			}
			return;
			
		}
		public function event_valadd($user,$val)
		{
			if(!transform($this->where,$data['user']))
			{
				return;
			}
			$maxnum=M('用户','dms_')->Max($this->name);
			$maxnum++;
			if($maxnum<=$this->maxNum){
			M('用户','dms_')->where(array('编号'=>$user['编号']))->save(array($this->name=>$maxnum));
			}else{
				return;
			}
		}
	}
?>