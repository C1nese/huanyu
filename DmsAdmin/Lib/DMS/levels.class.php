<?php
	class levels extends stru
	{
		//结算开始执行
		public $calStart =true ;
		//结算结束执行
		public $calEnd   =false;
		//秒结算开始执行
		public $scalStart=true ;
		//秒结算结束执行
		public $scalEnd  =false;
		//是否在后台可以修改申请级别
		public $regEdit  =false;
		//是否可以在后台修改赠送级别
		public $giveEdit =false;
		/*级别是否必须一级一级升
			如用户1能够达到3级的条件，却达不到2级的条件，则不能直直接升级为3级
		*/
		public $onlylevel = false;
		//开启区域选择
		public $area=false;
		public $lv_cache=array();
		public $conFilter=array('con'=>array("lv",'name',"pvmoney","num",'money','number','where','use','update','area','only'));
		//升级时获取奖金参数，计算差额
		public function getlevel($lv)
		{
			$cons=$this->getcon("con",array("lv"=>0,"pvmoney"=>-1,'money'=>0,"num"=>0,'number'=>1));
			foreach($cons as $con)
			{
				if($con["lv"] == (int)$lv)
				{
					$con['pvmoney'] = $con["pvmoney"]== -1 ? $con['money']*$con['number'] : $con["pvmoney"]*$con['number'];
					$con['money']   = $con['money']*$con['number'];
					return $con;
				}
			}
		}
		//缓存级别数据
		public function event_getCache($caltime,$username=""){
			//读取数据
			if($caltime==0 && isset($user)){
				if(isset($this->lv_cache[$user['编号']])){
					$user[$this->name]=$this->lv_cache[$user['编号']];
					return $user;
				}else{
					return false;
				}
			}
			//记录数据
			$this->lv_cache=M()->table("dms_用户 u")->join("inner join (select userid,min(olv) olv from dms_lvlog where time>".($caltime+86400)." and lvname='".$this->name."' group by userid ) lv on u.id=lv.userid")->getField("u.编号,lv.olv");
			return true;
		}
		//获取区域代理的数字
		public function getAreanum($lv)
		{
			$num=0;
			$cons=$this->getcon("con",array("area"=>'',"lv"=>0));
			foreach($cons as $con)
			{
				if($con["lv"] == (int)$lv)
				{
					if($con['area']=="country")     $num=1;
					elseif($con['area']=="province")$num=2;
					elseif($con['area']=="city")    $num=3;
					elseif($con['area']=="county")  $num=4;
					elseif($con['area']=="town")    $num=5;
					return $num;
				}
			}
		}
		/*自动升级功能
			$event触发的事件类型
			$time 触发的时间点
		*/
		public function uplv($event,$time,$saleid=0)
		{
			if(($event=='scalStart' && !$this->scalStart) || ($event=='scalEnd' && !$this->scalEnd) || ($event=='calStart' && !$this->calStart) || ($event=='calEnd' && !$this->calEnd))
				return ;
			//审核用户的时间限制
			$endtime=$time;
			if($event=='scalStart' || $event=='scalEnd' || $event=='calStart' || $event=='calEnd'){
				$endtime=X("tle@")->_caltime+86400-1;
			}
			$username = $this->parent()->name;
			$con=$this->getcon('con',array('name'=>'','l_name'=>'','lv'=>0,'pvmoney'=>0,'money'=>0,'num'=>0,'uptime'=>'','where'=>'','update'=>''));
			$haveUp=false;
			foreach($con as $key=>$val)
			{
				if($val['where']!=''){
					$haveUp=True;
					break;
				}
			}
			if($haveUp)
			{
				calmsg($this->byname.'的自动升级','/Public/Images/ExtJSicons/medal_gold_add.png');
			}
			foreach($con as $key=>$val)
			{
				$joins = '';
				extract($val);
				if($where!='')
				{
					preg_match_all("/\{(.*)\}/U",$where,$exp);
					if(!empty($exp[1])){
						foreach($exp[1] as $key=>$re)
						{
							//算法模块参数
							$funargs=explode(',',$re);
							//算法模块名
							$funname=array_shift($funargs);
							switch($funname)
							{
								case 'scratch':
								//{scratch,考核模块名,考核月份范围,符合月份数量,是否连续}
									$tmpstr='';
									list($funname,$allmonth,$ifmonth,$iscoherent) = $funargs;
									//if(!$iscoherent)$tmpstr="";//条件1不为空,条件2为空
									if($iscoherent=='true')
									{
										//IF(1>2,2,3);
										$sdate =mktime(0, 0, 0, date('m',$caltime)-$allmonth+$ifmonth-1, 1, date('Y',$caltime));
										$tmpstr="select distinct 编号 from `dms_{$funname}` where 时间>=$sdate and 连贯>={$ifmonth}";
									}
									else
									{
										$sdate =mktime(0, 0, 0, date('m',$caltime)-$allmonth, 1, date('Y',$caltime));
										$tmpstr="select 编号 from `dms_{$funname}` where 时间>=$sdate group  by 编号 having count(1) >={$ifmonth}";
									}
								break;
								case 'down':
									$tmpstr=$this->down($re,$endtime);
								break;
								//{per,推荐,推荐_团队人数,1,>=30}推荐的下级用户中存在 推荐_团队人数最大（1）且推荐_团队人数人数>=30
								case 'per':
									$tmpstr=$this->per($re,$endtime);
								break;
								//{place,安置,2,>=100}用户安置网下 第（2）大区的业绩（>=100）--------------------最多只支持三个区
								case 'place':
									$tmpstr='';
									list($oper,$net,$num,$con) = explode(',',$re);
									if($num!='' && $con!=''){
										$re_con=$this->place(array($net,$num),$endtime);
										$tmpstr="select 编号 from dms_用户 where ($re_con)".$con;
									}
								break;
								//{all,安置,2,M[自己条件]>=1 U[下级条件],>2}管理网体下，第二大区 满足（用户级别>=1）的团队人数（>2）
								case 'placeall':
									$tmpstr=$this->placeall($re,$endtime);
								break;
								//{all,推荐,用户级别>=1,>2}推荐网体下 满足（用户级别>=1）的团队数（>2）
								case 'all':
									$tmpstr=$this->all($re,$endtime);
								break;
								case 'allsum':
									$tmpstr=$this->allsum($re,$endtime);
								break;
								case 'full':
									$tmpstr=$this->full($re,$endtime);
								break;
								default:
									throw_exception('在进行升级操作时，使用了一个未知的大括号语法'.$funname.'目前只支持,scratch,down,per,place,all,allsum,full');
							}
							//$where = str_replace('{'.$re.'}','编号 in (select 编号 from ('.$tmpstr.') b)',$where);
							$where = str_replace('{'.$re.'}','1',$where);
							$joins .= ' inner join ('.$tmpstr.') b'.$key.' on a.编号=b'.$key.'.编号 ';
						}
					}
					$updatestr = $val['update'];
					//额外字段更新
					if($updatestr != '')
					{
						$updatestr = "," . $updatestr;
					}
					$lastlv=$lv-10;
					if($this->onlylevel){
						//$upwhere="[{$this->name}]={$lastlv}";
						$upwhere="{$this->name}={$lastlv}";
					}else{
						//$upwhere="[{$this->name}]<={$lastlv}";
						$upwhere="{$this->name}<={$lastlv}";
					}
					//对即将自动升级的用户生成升级日志
					//$upers = M()->query("select id,{$this->name} from dms_用户 where {$upwhere} and ($where)");
					$upers = M("用户")->alias('a')->join($joins)->where("{$upwhere} and ($where)")->field("id,用户级别,{$this->name}")->select();
					if($upers)
					{
						//用于做升级update的操作,update语句不在做联查，使用ID IN 提高效率
						$ids='';
						foreach($upers as $uper)
						{
							M('lvlog')->badd(array(
								'userid'=>$uper['id'],       //用户id
								'lvname'=>$this->name,       //级别名称
								'time'  =>$time,             //升级时间
								'olv'   =>$uper[$this->name],//老级别值
								'nlv'   =>$lv,               //新级别值
								'saleid'=>$saleid  ,         //升级订单
								'adminid'=>0                 //
							));
							$ids.=','.$uper['id'];
						}
						M('lvlog')->bupdate();
						$ids=trim($ids,',');
						$joinstr = "update dms_用户 set {$this->name}={$lv}{$updatestr} where id in ({$ids})";
						M()->execute($joinstr);
					}
				}
			}
		}
		//如果执行结算，则要还原当日升级级别
		protected function down($para,$endtime)
		{
			//{down,推荐,用户级别>=2 and 团队业绩>=2000,>=2}推荐的下级用户的（用户级别>=2 and 团队业绩>=2000）人数>=2
			$tmpstr='';
			list($oper,$net,$con1,$con2) = explode(',',$para);
			preg_match_all('/\[(.*)\]/Uis',$con1,$trform,PREG_SET_ORDER);
			if($con1!='' && $con2=='')$tmpstr="select distinct {$net}_上级编号 编号 from `dms_用户` where {$con1}";//条件1不为空,条件2为空
			if($con1=='' && $con2!='')$tmpstr="select {$net}_上级编号 编号 from `dms_用户` group by {$net}_上级编号 having count(1) {$con2}";//条件2不为空,条件1为空
			if($con1!='' && $con2!='')$tmpstr=$tmpstr="select {$net}_上级编号 编号 from `dms_用户` where {$con1} group by {$net}_上级编号 having count(1) {$con2}";//两条件都不为空
				
			return $tmpstr;
		}
		protected function placeall($para,$endtime)
		{
			$tmpstr='';
			list($oper,$net,$regionnum,$where,$con)= explode(',',$para);
			if(strpos($where,'U[')===false && strpos($where,'M[')===false)
			{
				throw_exception("升级条件中的{all标签中至少要定义U[XXX]或M[XXX]的字段条件,U代表被判定人,M代表要升级的人");
			}
			$qualifiedUsers = X('user')->placeallusers($net,$regionnum,$where,$con,$endtime);
			if($qualifiedUsers!=''){
				$tmpstr = "select 编号 from dms_用户 where id in({$qualifiedUsers})";
			}else{
				$tmpstr = "select 编号 from dms_用户 where 1=0";
			} 
			return $tmpstr;
		}
		
		protected function all($para,$endtime)
		{
			$tmpstr='';
			if(count(explode(',',$para))==4){
				list($oper,$net,$con1,$con2) = explode(',',$para);
				if(strpos($con1,'U[')===false && strpos($con1,'M[')===false)
				{
						throw_exception("升级条件中的{all标签中至少要定义U{XXX]或M[XXX]的字段条件,U代表被判定人,M代表要升级的人");
				}
				$qualifiedUsers = X('user')->allusers($net,$con1,$con2);
			}else{
				list($oper,$net,$con1,$con2,$con3) = explode(',',$para);
				$qualifiedUsers = X('user')->allusers($net,$con1,$con2,$con3);
			}
			if($qualifiedUsers!=''){
				$tmpstr = "select 编号 from dms_用户 where id in({$qualifiedUsers})";
			}else{
				$tmpstr = "select 编号 from dms_用户 where 1=0";
			} 
			return $tmpstr;
		}
		//层满
		protected function full($para,$endtime)
		{
			static $cache=array();
			if(isset($cache[$para]))
			{
				return $cache[$para];
			}
			list($oper,$net,$num) = explode(',',$para);
			if($num!=''){
				$netobj = X('*@'.$net);
				if(get_class($netobj) == 'net_place'){
					$regions = $netobj->getRegion();
					$count = count($regions);
					$sumarr=array();
					$udata = M('用户')->where(array(array("状态"=>"有效","审核日期"=>array("elt",$endtime))))->field("{$net}_网体数据 net")->select();
					foreach($udata as $user)
					{
						$ids = explode(',',$user['net']);
						$ids = array_map(
							function ($v){
								$ret=explode('-',$v);
								return $ret[0];
							}
						,$ids);
						$ids=array_reverse($ids);
						$ids=array_slice($ids,0,$num);
						foreach($ids as $k=>$id)
						{
							$sumarr[$k][$id]++;
						}
					}
					$ids=array();
					foreach($sumarr[$num-1] as $uid=>$as)
					{
						if($as == pow($count,$num))
						{
							$ids[]=$uid;
						}
					}
				}
				if(!$ids)
				$ids[]=0;
				
				$tmpstr = "select 编号 from dms_用户 where id in(".implode($ids,',').")";
			}
			$cache[$para]=$tmpstr;
			return $tmpstr;
		}
		//取得团队下边的人数
		//取得团队下边的人数
		protected function allsum($para,$endtime)
		{
			list($oper,$net,$con1,$con2,$minlayer,$maxlayer) = explode(',',$para);
			if($con1=='')
			{
				$con1='1=1';
			}
			$netobj = X('*@'.$net);
			if(get_class($netobj) == 'net_place'){
				$regions = $netobj->getRegion();
				$netStr = "`{$net}_网体数据`";
				foreach($regions as $region){
					$netStr = "REPLACE({$netStr},'-{$region['name']}','')";
				}
			}else{
				$netStr = "`{$net}_网体数据`";
			}
			
			//层数判定
			$layerwhere='';
			if($minlayer)
			{
				$layerwhere = " and {$net}_层数 - a.{$net}_层数>=".($minlayer - 1);
			}
			else
			{
				$layerwhere = " and {$net}_层数 - a.{$net}_层数>=0";
			}
			if($maxlayer)
			$layerwhere = " and {$net}_层数 - a.{$net}_层数<=".($maxlayer - 1);
			if($con1!='' && $con2!='')
				$tmpstr = "select 编号,sum(s)sums from (select {$net}_上级编号 编号,(
				select sum(1) from dms_用户  where  (FIND_IN_SET(a.id,$netStr) or a.id=id) and ({$con1}) {$layerwhere}
				) as s from dms_用户 a) b group by 编号 having sums {$con2}";
			return $tmpstr;
		}
		//处理团队业绩判定SQL
		protected function per($para,$endtime)
		{
			$tmpstr='';
			list($oper,$net,$row,$con1,$con2) = explode(',',$para);
			if(false && $con1==1){
				$tmpstr="select {$net}_上级编号 编号,max({$row})sumtj from dms_用户 where {$row}{$con2} group by {$net}_上级编号";//最大
			}elseif(is_numeric($con1)){
				$tmpstr="
					 select {$net}_上级编号 编号,rank from ( 
					 select heyf_tmp.编号,heyf_tmp.{$net}_上级编号,heyf_tmp.$row,@rownum:=@rownum+1 , 
					 if(@pdept=heyf_tmp.{$net}_上级编号,@rank:=@rank+1,@rank:=1) as rank, 
					 @pdept:=heyf_tmp.{$net}_上级编号 
					 from (  
					 select 编号,{$net}_上级编号,$row from dms_用户 order by {$net}_上级编号 asc ,$row desc  
					 ) heyf_tmp ,(select @rownum :=0 , @pdept := null ,@rank:=0) a ) result where rank={$con1} AND {$row}{$con2}";
				
			}elseif(strpos($con1,'-')!==false){//第几大到第几大
				list($num1,$num2) = explode('-',$con1);
				if(empty($num2))$num2=99999999;
				$tmpstr="SELECT  {$net}_上级编号 编号 FROM (
					SELECT sum($row) sum,{$net}_上级编号 from (
						select 编号,{$net}_上级编号,$row,rank from ( 
						 select heyf_tmp.编号,heyf_tmp.{$net}_上级编号,heyf_tmp.$row,@rownum:=@rownum+1 , 
						 if(@pdept=heyf_tmp.{$net}_上级编号,@rank:=@rank+1,@rank:=1) as rank, 
						 @pdept:=heyf_tmp.{$net}_上级编号 
						 from (  
						 select 编号,{$net}_上级编号,$row from dms_用户 order by {$net}_上级编号 asc ,$row desc  
						 ) heyf_tmp ,(select @rownum :=0 , @pdept := null ,@rank:=0) a ) result where rank>={$num1} and rank<={$num2})x 
					GROUP BY {$net}_上级编号
				  )xx where sum{$con2}";
			}
			return $tmpstr;
		}
		protected function place($para,$endtime)
		{
			list($m_netname,$m_set)=$para;
			$net = X('net_place@'.$m_netname);
			$Branchs = $net->getBranch();
			$rowsstr="";
			foreach($Branchs as $Branch)
			{
				if($rowsstr != "")
				$rowsstr .= ",";
				//$rowsstr.= $m_netname . "_" . $Branch . "区累计业绩";
                $rowsstr.=  "团队业绩_" . $Branch . "区累计业绩";
			}
			//第一大区
			if($m_set=="1")
			{
				$tsql='GREATEST('.$rowsstr.')';
			}
			//最小一个区
			if($m_set == "3" || ($m_set == "2" && count($Branchs)==2))
			{
				$tsql='LEAST('.$rowsstr.')';
			}
			//当有三条线的时候中间那个区
			if($m_set == "2" && count($Branchs)==3)
			{
				$tsql="(".str_replace(",","+",$rowsstr).")-LEAST($rowsstr)-GREATEST($rowsstr)";
			}
			//所有区
			if(($m_set == "12" && count($Branchs)==2)||($m_set == "123" && count($Branchs)==3))
			{
				$tsql="(".str_replace(",","+",$rowsstr).")";
			}
			//三个区中的前两个区
			if(($m_set == "12" && count($Branchs)==3))
			{
				$tsql="(".str_replace(",","+",$rowsstr).")-LEAST($rowsstr)";
			}
			//三个区中的前两个区
			if(($m_set == "23" && count($Branchs)==3))
			{
				$tsql = "(".str_replace(",","+",$rowsstr).")-GREATEST($rowsstr)";
			}
			return $tsql;
		}
	}
?>