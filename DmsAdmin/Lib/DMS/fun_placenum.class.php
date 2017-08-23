<?php
	class fun_placenum extends stru
	{
		//统计网络业绩的网络关系名
		public $netName = "";
		//包含字段
		//public $row = "day,week,month,year,all";
		//是否包含自己
		public $haveMe = false;
		//产生个人条件
		//public $where = "";
		//计算最小层
		public $MinLayer = -1;
		//计算最大层
		public $MaxLayer = -1;
		//计算统计本周
		public $nowWeek=false;
		//计算统计本月
		public $nowMonth=false;
		//计算统计本年
		public $nowYear=false;
		public $Cache    =array();
		/*系统清空事件*/
		public function event_sysclear()
		{
			M()->execute("TRUNCATE TABLE " . 'dms_'.$this->name.'_业绩;');
		}
		//执行addval
		public function event_valadd($user,$val,$option)
		{
			$this->add($user,$val,$option);
		}
		//增加记录
		public function add($user,$val,$option)
		{
			//取得订单ID
			$saleid = isset($option['saleid']) ? $option['saleid'] : 0 ;
			//产生自身业绩记录
			$indata = array('time'=>systemTime(),
				  'userid'=>$user['id'],
				  'fromid'=>$user['id'],
				  'val'   =>$val,
				  'saleid'=>$saleid,
				  'pid'   =>0,
				);
			//插入原始记录得到Pid
			$pid=M($this->name.'_业绩')->add($indata);
			$this->addUpPv($pid,$val,$user[$this->netName.'_网体数据'],$user['id'],$saleid,systemTime());
		}
		//根据原始记录ID,额度,以及网体数据.更新上级业绩
		private function addUpPv($pid,$val,$netdata,$fromid,$saleid,$time){
			//如果没有网体数据.就表示不需要做任何处理.直接返回
			if(!$netdata) return;
			//网体数据转换
			$t_arrs=array_reverse(explode(',',$netdata));
			$sql=array();
			//区域
			$region2id = array();
			$net = X('*@'.$this->netName);
			if($net->getcon("region",array("name"=>""))){
				foreach($net->getcon("region",array("name"=>"")) as $key=>$Region)
				{
					$region2id[$Region["name"]]=$key+1;
				}
			}
			//业绩缓存？？？？
			$adddata=array();
			foreach($t_arrs as $key=>$t_arr)
			{
				//对业绩层数增的判定
				if(($this->MinLayer == -1 || $key+1 >= $this->MinLayer) && ($this->MaxLayer == -1 || $key+1 <= $this->MaxLayer)) {
					$data = explode('-',$t_arr);
					$adddata[$data[1]][]=$data[0];
				    $region = $region2id[$data[1]];
				    $weeks ='';
				    $mouths='';
				    $years ='';
				    if($this->nowWeek)
				    {
				    	$weeks = $this->name."_{$data[1]}区本周业绩=".$this->name."_{$data[1]}区本周业绩+".$val.",";
				    }
				    if($this->nowMonth)
				    {
				    	$mouths = $this->name."_{$data[1]}区本月业绩=".$this->name."_{$data[1]}区本月业绩+".$val.",";
				    }
				    if($this->nowYear)
				    {
				    	$years = $this->name."_{$data[1]}区本年业绩=".$this->name."_{$data[1]}区本年业绩+".$val.",";
				    }
				    $sql[]  = "($time,$data[0],$fromid,$val,$saleid,$pid,$region)";
				    //对用户的本日本月累计进行增加
				    M()->execute("update dms_用户 set ".$this->name."_{$data[1]}区本期业绩=".$this->name."_{$data[1]}区本期业绩+".$val.",".$weeks.$mouths.$years.$this->name."_{$data[1]}区累计业绩=".$this->name."_{$data[1]}区累计业绩+".$val." where id=".$data[0]);
				}
			}
			//如果SQL数组为空.就退出
			if(!$sql) return;
			$sqlstr = implode($sql,',');
			$sqlstr = 'INSERT INTO dms_'.$this->name.'_业绩 (`time`,`userid`,`fromid`,`val`,`saleid`,`pid`,`region`) VALUES '.$sqlstr;
			M()->execute($sqlstr);
		}
		public function event_diffTime($time)
		{
			$this->update($time);
		}
		//计算时统计
		public function event_cal($tle,$caltime)
		{
			//die('此模块需要创建caladd方法,不在适用cal事件');
			//$this->update($caltime);
		}
		public function getBranch(){
			return X("net_place@".$this->netName)->getBranch();
		}
		public function update($caltime=0){
			if($caltime==0)
				$caltime=strtotime(date("Y-m-d",systemTime()));
			//更新本日业绩
			foreach(X("net_place@".$this->netName)->getBranch() as $rkey=>$region){
				M()->execute("update dms_用户 set ".$this->name."_{$region}区本期业绩=0 where ".$this->name."_{$region}区本期业绩>0");
				M()->execute("update dms_用户 a inner join (select  userid,sum(val) val from dms_".$this->name."_业绩 where region='".($rkey+1)."' and pid<>0 and time>=".($caltime)." and time<".($caltime+86400)." group by userid) b 
				on a.id=b.userid set a.".$this->name."_{$region}区本期业绩=ifnull(b.val,0) where a.".$this->name."_{$region}区本期业绩!=ifnull(b.val,0)");
				//更新本周业绩
				if($this->nowWeek){
					$firstweek=$caltime-3600*24*(date("N",$caltime)-1);
					M()->execute("update dms_用户 set a.".$this->name."_{$region}区本周业绩=0 where a.".$this->name."_{$region}区本周业绩>0");
					M()->execute("update dms_用户 a inner join (select  userid,sum(val) val from dms_".$this->name."_业绩 where region='".($rkey+1)."' and pid<>0 and time>=".($firstweek)." and time<".($caltime+86400)." group by userid) b 
					on a.id=b.userid set a.".$this->name."_{$region}区本周业绩=ifnull(b.val,0) where a.".$this->name."_{$region}区本周业绩!=ifnull(b.val,0)");
				}
				if($this->nowMonth){
					$firstmonth=$caltime-3600*24*(date("d",$caltime)-1);
					M()->execute("update dms_用户 set a.".$this->name."_{$region}区本月业绩=0 where a.".$this->name."_{$region}区本月业绩>0");
					M()->execute("update dms_用户 a inner join (select  userid,sum(val) val from dms_".$this->name."_业绩 where region='".($rkey+1)."' and pid<>0 and time>=".($firstmonth)." and time<".($caltime+86400)." group by userid) b 
					on a.id=b.userid set a.".$this->name."_{$region}区本月业绩=ifnull(b.val,0) where a.".$this->name."_{$region}区本月业绩!=ifnull(b.val,0)");
				}
				if($this->nowYear){
					$firstyear=strtotime(date("Y",$caltime)."-01-01");
					M()->execute("update dms_用户 set a.".$this->name."_{$region}区本年业绩=0 where a.".$this->name."_{$region}区本年业绩>0");
					M()->execute("update dms_用户 a inner join (select  userid,sum(val) val from dms_".$this->name."_业绩 where region='".($rkey+1)."' and pid<>0 and time>=".($firstyear)." and time<".($caltime+86400)." group by userid) b 
					on a.id=b.userid set a.".$this->name."_{$region}区本年业绩=ifnull(b.val,0) where a.".$this->name."_{$region}区本年业绩!=ifnull(b.val,0)");
				}
				//更新累计
				M()->execute("update dms_用户 set ".$this->name."_{$region}区累计业绩=0 where ".$this->name."_{$region}区累计业绩>0");
				M()->execute("update dms_用户 a inner join (select  userid,sum(val) val from dms_".$this->name."_业绩 where region='".($rkey+1)."' and pid<>0 and time<".($caltime+86400)." group by userid) b 
				on a.id=b.userid set a.".$this->name."_{$region}区累计业绩=ifnull(b.val,0) where a.".$this->name."_{$region}区累计业绩!=ifnull(b.val,0)");
			}
		}
		//当网络进行过移动时
		public function event_netmove($net,$user,$movetime)
		{
			$uidsql = $users = M('用户')->where($net->name."_网体数据 like '".($user[$net->name.'_网体数据'].','.$user['id'])."%'")->Field('id')->select(false);
			$ids = M($this->name.'_业绩')->where('pid=0 and userid in '.$uidsql)->Field('id')->getField('id,id id2');
			if($ids)
			{
				M()->execute('delete from dms_'.$this->name.'业绩 where pid in ('.implode(",",$ids).')');
			}
			$adds = M()->table('dms_'.$this->name.'_业绩 a')->join('dms_用户 b on b.id=a.userid')->field('b.'.$net->name.'_网体数据 netdata,b.id uid,a.val,a.id,a.saleid,a.time')->where('pid=0 and `time` >='.$movetime)->select();
			foreach($adds as $add)
			{
				$this->addUpPv($add['id'],$add['val'],$add['netdata'],$add['uid'],$add['saleid'],$add['time']);
			}
		}
	}
?>