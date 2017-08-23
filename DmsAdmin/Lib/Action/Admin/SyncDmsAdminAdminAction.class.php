<?php
import("COM.Interface.SyncInterface");
import("COM.Interface.QuickSearchInterface");
class SyncDmsAdminAdminAction extends Action implements SyncInterface,QuickSearchInterface
{
	public $APP="DmsAdmin";
	public $prefix="dms_";
	public function __construct()
	{
		//影响加载的效率 屏蔽 这段代码适用于检测config.xml的配置数据的
		if(APP_NAME != 'Install')
		{
			$md5=md5_file(ROOT_PATH."/DmsAdmin/config.xml");
			if($md5!=CONFIG('XMLMD5'))
			{
				CONFIG('XMLMD5',md5_file(ROOT_PATH."/DmsAdmin/config.xml"));
				import('DmsAdmin.DMS.xmlCheck');
				xmlCheck::check();
			}
		}
	}
	/*
	* 清空用户信息数据资料
	*
	*/
	public function clearSystemData()
	{
		$user=X('user');
		C('DB_PREFIX',$this->prefix);
		$user->callevent('sysclear',array());
	}
	public function getuserdata(){
		$userinfo=array();$tjtime=systemTime();
		//本日
		$starttime=strtotime(date('Y-m-d',$tjtime));
		$endtime=$starttime+24*3600;
		//周1
		$week=date('N',$tjtime);
		$daydiff=$week-1;
		$wstarttime=$starttime-$daydiff*86400;
		//本月1号
		$mstarttime=mktime(0,0,0,date('m',$tjtime),1,date("Y",$tjtime));
		//本年1号
		$ystarttime=mktime(0,0,0,1,1,date("Y",$tjtime));
		
		//用户查询
		$countuser=M($this->prefix.'用户')->count('id');
		
		$day_user=M($this->prefix.'用户')->where("登入日期>='".$starttime."' and 登入日期<='".$endtime."'")->count('id');
		$day_join=M($this->prefix.'用户')->where("注册日期>='".$starttime."' and 注册日期<='".$endtime."'")->count('id');
		if($countuser>0){
			$userinfo['用户总人数']=$countuser;
		}else{
			$userinfo['用户总人数']=0;
		}
		if($day_user>0){
			$userinfo['活跃人数']=$day_user;
		}else{
			$userinfo['活跃人数']=0;
		}
		if($day_join>0){
			$userinfo['注册人数']=$day_join;
		}else{
			$userinfo['注册人数']=0;
		}
		//货币累计信息
		$userinfo['bank']=array();
		$userinfo['累计提现'] = 0;
		foreach(X('fun_bank') as $bank){
			//$c_bank=M($this->prefix.'用户')->sum($bank->name);
			$c_bank=M($this->prefix.'货币')->sum($bank->name);//货币分离
			if($c_bank>0){
				$userinfo[$bank->byname.'累计']=$c_bank;
			}else{
				$userinfo[$bank->byname.'累计']='0.00';
			}
			$userinfo['bank'][]=$bank->byname.'累计';
			//$c_bank=M('提现')->where('状态>1')->sum('提现额');
			//if($c_bank>0){
			//	$userinfo[$bank->byname.'累计提现']=$c_bank;
			//	$userinfo['累计提现']+=$c_bank;
			//}else{
			//	$userinfo[$bank->byname.'累计提现']='0.00';
			//	$userinfo['累计提现']+=0.00;
			//}
			//if($bank->getMoney)
			//{
			//	$userinfo['bank'][]=$bank->byname.'累计提现';
			//}

			$c_bank=M($this->prefix.$bank->name.'明细')->where('来源=""')->sum('金额');
			if($c_bank>0){
				$userinfo[$bank->byname.'累计充值']=$c_bank;
			}else{
				$userinfo[$bank->byname.'累计充值']='0.00';
			}
			$userinfo['bank'][]=$bank->byname.'累计充值';
		}
		//奖金累计播出
		/*
		需要改为支持多TLE的形式
		$tles=X('tle');
		//$tleinfo=M($this->prefix.$tles->name.'总账')->order('id desc')->limit('1')->find();
		if($tleinfo){
			//$per=round($userinfo['累计提现']/$tleinfo['总奖金'],4)*100;
			$userinfo['累计总奖金']=$tleinfo['总奖金'];
			$userinfo['累计总业绩']=$tleinfo['总业绩'];
			$per=round($tleinfo['总奖金']/$tleinfo['总业绩'],4)*100;
			$userinfo['累计播出']=$per.'%';
		}else{
			$userinfo['累计总奖金']='0.00';
			$userinfo['累计总业绩']='0.00';
			$userinfo['累计播出']='0.00%';
		}
		*/
		//汇款累计
		$notranders=M($this->prefix.'汇款通知')->where('状态=0')->count('id');
		$userinfo['未处理汇款']=$notranders;
		//汇款累计
		$tranders=M($this->prefix.'汇款通知')->where('状态>0')->count('id');
		$userinfo['总汇款记录']=$tranders;
		//汇款累计
		$trande_money=M($this->prefix.'汇款通知')->where('状态>0')->sum('金额');
		if($trande_money){
			$userinfo['总汇款金额']=$trande_money;
		}else{
			$userinfo['总汇款金额']='0.00';
		}
		
		$daytime=strtotime(date("Y-m-d",systemTime()));
		foreach(X('sale_*') as $sale)
		{
			$daysum=M('报单','dms_')->where(array('报单类别'=>$sale->name,'到款日期'=>array(array('egt',$daytime),array('lt',$daytime+86400))))->count();
			$allsum=M('报单','dms_')->where(array('报单类别'=>$sale->name))->count();
			$salesum[$sale->name]=array('day'=>$daysum,'all'=>$allsum);
		}
		$userinfo['salesum'] = $salesum;
		$userinfo['salenosend'] = M('报单','dms_')->where(array('物流状态'=>'未发货'))->count();
		$userinfo['daytimestart'] = $daytime;
		$userinfo['daytimeend'] = $daytime+86400;
		
		$nolookmsg=M('邮件','dms_')->where("收件人类型='管理员' and 状态=0")->count();
		$userinfo['nolookmsg'] = $nolookmsg;
		//奖金业绩情况
		$prizeNet=array();
		foreach(X('prize_*') as $prize)
		{
			if(property_exists($prize,'netName'))
			{
				$prizeNet[$prize->byname]=$prize->netName;
				
			}
		}
		$userinfo['prizeNet']=$prizeNet;
		
		//订单进业绩提示
		$addnet=array();
		foreach(X('sale_*') as $sale)
		{
			$addcon=$sale->getcon('addval',array(),true);
			foreach($addcon as $con)
			{
				$obj=X('@'.$con['to']);
				if(get_class($obj) == "net_place" && !isset($con['set']))
				{
					if(!isset($addnet[$obj->byname]))
					{
						$addnet[$obj->byname]=array();
					}
					$addnet[$obj->byname][]=$sale->name.'('.$sale->byname.')';
				}
			}
		}
		$userinfo['addnet'] = $addnet;
		//业绩统计
		$tjdateary=array("本日","本周","本月","本年","累计");
		$userinfo["tjdateary"]=$tjdateary;
		//是否显示
		$userinfo["user_yeji"]=adminshow('user_yeji');

		$netPlaceName = array();
		foreach(X('net_place') as $netPlace){
			$netPlaceName[]=$netPlace->name;
		}
		
		if(count($netPlaceName)>0){
			$userinfo["yjcount"]=count($netPlaceName);
			$netyj=array();
			foreach($netPlaceName as $netName){
				foreach($tjdateary as $dval){
					$where="val>0 and pid=0";
					switch($dval){
						case '本日':$where.=' and time>='.$starttime;break;
						case '本周':$where.=' and time>='.$wstarttime;break;
						case '本月':$where.=' and time>='.$mstarttime;break;
						case '本年':$where.=' and time>='.$ystarttime;break;
					}
					$yjsum=M($netName."_业绩",'dms_')->where($where)->sum('val');
					if(!$yjsum) $yjsum=0;
					$netyj[$netName][$dval]=$yjsum;
				}	
			}
			$userinfo['netyj'] = $netyj;
		}
		return $userinfo;
	}
	/*
	* 返回应用的相关信息
	*
	*/
	public function returnApplicationInfo()
	{
		return array(
			'is_sync_node'		=> '1',	//是否同步节点数据
			'is_sync_menu'		=> '1',	//是否同步菜单数据
			'is_quick_search'	=> '1',			//是否启用快捷搜索
			'title'				=> '结算管理',	
		    'group'				=> 'Admin',//应用名称
		);
	}

	/*
	* 返回快捷搜索的html结果
	*/
	public function returnQuickSearch($name)
	{
        //import($this->APP.'.DMS.stru');
		//$con=new stru();
		$return=array();
		//1. 搜索user用户表
		$user = X('user');
		//编号搜索
		/****** 编号精确查询******/
		$sqlid="select * from dms_用户 where 编号 ={$name}";
		$rsid=M()->query($sqlid);
		if(!empty($rsid)){
			foreach($rsid as $v)
			{
				if($v['编号']!=''){
					if(!array_key_exists($v['编号'],$return)){
						$return[$v['编号']]=array(
							'url'=>'<a class="edit" href="#" search="'.$v['编号'].'" target="search"  title="查看'.$user->byname.'"><span>['.$user->byname.$v['编号'].'('.$v['姓名'].')]</span></a>&nbsp;&nbsp;',
							'obj'=>$user,
							'user'=>$v,
						);
					}
				}
			}
		}
		/****** 编号模糊查询******/
		$sqlidlike="select * from dms_用户 where 编号 like '%{$name}%'";
		$rsidlike=M()->query($sqlidlike);
		if(!empty($rsidlike)){
			foreach($rsidlike as $v)
			{
				if(!array_key_exists($v['编号'],$return)){
				if($v['编号']!=''){
				   $return[$v['编号']]=array(
					   'url'=>'<a class="edit" href="#" search="'.$v['编号'].'" target="search"  title="查看'.$user->byname.'"><span>[搜索'.$v['编号'].'('.$v['姓名'].')]</span></a>&nbsp;&nbsp;',
					   'obj'=>$user,
					   'user'=>$v,
					);
				}
				}
			}
		}
		//姓名搜索
		/****** 姓名精确查询******/
		$sqlname="select * from dms_用户 where 姓名='".$name."'";
		$rsname=M()->query($sqlname);
		if(!empty($rsname)){
			foreach($rsname as $v)
			{
				if($v['姓名']!=''){
					if(!array_key_exists($v['编号'],$return)){
						$return[$v['编号']]=array(
						'url'=>'<a class="edit" href="#" search="'.$v['编号'].'" target="search" title="查看'.$user->byname.'"><span>['.$user->byname.$v['编号'].'('.$v['姓名'].')]</span></a>&nbsp;&nbsp;',
						'obj'=>$user,
						'user'=>$v,
						);
					}
				}
			}
		}
		/****** 姓名模糊查询******/
		$sqlnamelike="select * from dms_用户 where 姓名 like '%{$name}%'";
		$rsnamelike=M()->query($sqlnamelike);
		if(!empty($rsnamelike)){
			foreach($rsnamelike as $v)
			{
				if($v['姓名']!=''){
				  if(!array_key_exists($v['编号'],$return)){
					  $return[$v['编号']]=array(
					   'url'=>'<a class="edit" href="#" search="'.$v['姓名'].'" target="search"  title="'.$v['姓名'].'"><span>[搜索'.$v['编号'].'('.$v['姓名'].')]</span></a>&nbsp;&nbsp;',
					  'obj'=>$user,
					  'user'=>$v,
					);
				}
				}
			}
		}

		//返回值
		$rs='当前搜索值:'.$name.'<br />';
		/****当精确查询和模糊查询的结果只有一个时****/
		if(count($return)==1){
			foreach($return as $k=>$v){
				$userobj=$v['obj'];
				$id=$v['user']['id'];
				$username=$v['user']['姓名'];
				$userid=$k;
				$rs.='<a href="/index.php?s=/Admin/User/loginToUser/id/'.$userid.'" rel="admin_edit" target="_blank" title="登陆'.$userobj->byname.'前台"><span>[编号-'.$k.']</span></a>&nbsp;&nbsp;&nbsp;';
				$rs.='<a href="/index.php?s=/Admin/User/edit/id/'.$id.'" rel="admin_edit" target="dialog" mask="true" width="550" height="420" title="修改'.$userobj->byname.'"><span>[姓名-'.$username.']</span></a>&nbsp;&nbsp;&nbsp;';
                //得到货币的明细
				foreach(X('fun_bank') as $funbank)
				{
                    $rs.='<a href="/index.php?s=/Admin/FunBank/index:'.$funbank->objPath().'/userid/'.$userid.'" rel="admin_edit" target="navTab" title="'.$userobj->byname.$userid.$funbank->byname.'明细"><span>['.$funbank->byname.'明细]</span></a>&nbsp;&nbsp;&nbsp;';
				}
				//得到销售奖金的明细
				foreach(X('tle') as $tle)
				{
                    $rs.='<a href="/index.php?s=/Admin/Tle/index:'.$tle->objPath().'/userid/'.$userid.'" rel="admin_edit" target="navTab" title="'.$userobj->byname.$userid.$tle->byname.'"><span>['.$tle->byname.'查询]</span></a>&nbsp;&nbsp;&nbsp;';
				}
                //得到福利奖的明细
				foreach(X('fun_fuli') as $fuli)
				{
                    $rs.='<a href="/index.php?s=/Admin/Fun_fuli/index:'.$fuli->objPath().'/userid/'.$userid.'" rel="admin_edit" target="navTab" title="'.$userobj->byname.$userid.$fuli->byname.'"><span>['.$fuli->byname.'查询]</span></a>&nbsp;&nbsp;&nbsp;';
				}
				 //得到股票的明细
				foreach(X('fun_stock') as $fun_stock)
				{
                    $rs.='<a href="/index.php?s=/Admin/Fun_stock/record:'.$fun_stock->objPath().'/userid/'.$userid.'" rel="admin_edit" target="navTab" title="'.$userobj->byname.$userid.$fun_stock->byname.'明细"><span>['.$fuli->byname.'明细查询]</span></a>&nbsp;&nbsp;&nbsp;';
				}
				//得到订单列表
                    $rs.='<a href="/index.php?s=/Admin/Sale/index/userid/'.$userid.'" rel="admin_edit" target="navTab" title="'.$userobj->byname.$userid.'订单"><span>[订单查询]</span></a>&nbsp;&nbsp;&nbsp;';
			}
		}else{
		foreach($return as $v)
		{
			$rs.=$v['url'];
		}
		}
	}

	/*
	* 返回应用下的菜单列表
	*
	* 数据格式如下：
	*

	return array(
		'菜单组名称'=>array(
			'childs'=>array(
				'菜单名称'=>array(
					'url'=>'菜单链接地址',
					'childs'=>array(
						'子菜单名称'=>array(
							'url'=>'5555',
							'childs'=>array(
								//[.....] 可以无限向下延伸
							),
						),
						'子菜单名称'=>array(
							'url'	=> '/index.php?s=/Admin/index',
						),
						//[......] 更多的子菜单
					),
				),

				或

				'菜单名称'=>'链接地址',
			),
		),
		//[......] 更多的菜单组
	);

	*
	*/
	public function returnMenuList()
	{
		//return array();
		$menus=R('DmsAdmin://Admin/Menu/getMenu');
		foreach($menus as $menu1)
		{
			if($menu1['level']=='1' && $menu1['parent']!="系统管理"){
				if(!array_key_exists('parent1',$menu1)){
					$return['结算管理']['childs'][$menu1['parent']]['childs'][$menu1['title']]=array('url'=>"/index.php?s=/Admin/".$menu1['model']."/".$menu1['action']);
				}
			}
		}
		foreach($menus as $menu1)
		{
			if($menu1['level']=='1' && $menu1['parent']!="系统管理"){
				if(array_key_exists('parent1',$menu1)){
					$return['结算管理']['childs'][$menu1['parent']]['childs'][$menu1['parent1']]['childs'][$menu1['title']]=array('url'=>"/index.php?s=/Admin/".$menu1['model']."/".$menu1['action']);
				}
			}
		}
		return $return;
	}

	/*
	*
	* 返回应用下的节点列表
	*
	* 数据格式如下：
	*
	*

	return array(
		'模块名称'=>array(
			'module'	=>'模块类名',	//如 AdminAction.class.php  只需要填写  Admin
			'sort'		=>1,			//排序值
			'childs'=>array(
				'用户查询'=>array('action'=>'find'),
				'修改产品'=>array('action'=>'edit'),
				'删除产品'=>array('action'=>'delete'),
				//[......] 更多的方法
			),
		),
		//[......] 更多的模块
	);

	*/

	//该返回的节点数组是同一个module、xpath以及parent为一个元素
	public function returnNodeList()
	{
		//import($this->APP.'.DMS.stru');
		//include ROOT_PATH.$this->APP."/Lib/DMS/data/menu.php";
		$return=array();
        //$con=new stru();
		//$xmenu=menuList($con);		
		$menu=R('DmsAdmin://Admin/Menu/getMenu');
		//print_r($menu);
		/*foreach($menu as $key=>$menu1)
		{
			if(array_key_exists($menu1["parent"],$return))
			{
				$return[$menu1["parent"]]['childs'][$menu1["title"]]=array("action"=>$menu1['action'],'setParent'=>$menu1['setParent']);
			}else{
			$return[$menu1["parent"]]=array(
				'module'	=> $menu1['model'],
				'sort'		=> $key,
				'childs'	=> array(
					$menu1["title"]=> array('action'=>$menu1['action'],'setParent'=>$menu1['setParent']),
				),
			 );
			}
		}*/
		foreach($menu as $key=>$menu1)
		{
			isset($menu1['xpath']) || $menu1['xpath']='';
			if(array_key_exists($menu1["model"].','.$menu1['xpath'],$return))
			{
				$action = isset($menu1['actions']) ? $menu1['actions'] : $menu1['action'];
				$return[$menu1["model"].','.$menu1['xpath']]['childs'][$menu1["title"]]=array("action"=>$action,'setParent'=>isset($menu1['setParent'])?$menu1['setParent']:'','parent'=>$menu1["parent"]);
			}else{
				$action = isset($menu1['actions']) ? $menu1['actions'] : $menu1['action'];
				$return[$menu1["model"].','.$menu1['xpath']]=array(
					'title'		=> $menu1['parent'],
					'module'	=> $menu1['model'],
					'sort'		=> $key,
					'childs'	=> array(
						$menu1["title"]=> array('action'=>$action,'setParent'=>isset($menu1['setParent'])?$menu1['setParent']:"",'parent'=>isset($menu1["parent"])?$menu1["parent"]:""),
					),
				 );
			}
		}
		return $return;
	}
	/*
	* 返回应用对环境的检查结果
	*
	*
	*/
	public function checkEnviro()
	{
		return array();
	}

	/*
	* 返回应用对目录的检查结果
	*
	*
	*/
	public function checkDir()
	{
		return array();
	}

	/*
	* 返回配置文件项
	*/

	public function returnConfigList()
	{
		return array(
			'APP_GROUP_LIST'=>"Admin,User,Api,Check",//项目分组
            'DEFAULT_GROUP'=>'Admin',
			'LANG_SWITCH_ON'        =>  true,
	        'My_LANG_SWITCH_ON'		=>false,
			'DB_PREFIX'                 =>  $this->prefix,	
		);
	}	/*
	* 返回应用要创建的基础数据
	* sql语句,每条之间用 ; 隔开
	*
	*/
	public function returnSqlStr()
	{
		import('DmsAdmin.DMS.stru');
		$syncM=D("DmsAdmin://SyncDmsAdminAdmin");
		//返回数据库表字符串
		$xml=$syncM->getAllxml();
		if($xml === false)
		{
			echo '对应表的XML文件有语法错误';
			die;
		}
		$tabarr = array();
		foreach($xml->xpath('./table') as $v_t)
		 {
			if(!array_key_exists((string)$v_t['name'],$tabarr))
			 {
				$tabarr[(string)$v_t['name']]['engine']=(string)$v_t['engine'];
				if(isset($v_t['comment']) && (string)$v_t['comment'] !='')
				 {
				 $tabarr[(string)$v_t['name']]['comment']=(string)$v_t['comment'];
				 }
			 } 
                foreach($v_t->xpath('./field') as $field)
				 {
					if(isset($tabarr[(string)$v_t['name']]['field']) && array_key_exists((string)$field['name'],$tabarr[(string)$v_t['name']]['field']))
					 {
						//echo (string)$v_t['name'].' 表的 '.(string)$field['name']." 字段存在多个<br/>";
						continue;
					 }
                     $tabarr[(string)$v_t['name']]['field'][(string)$field['name']]=array(	 
					     'type'=>(string)$field['type'],
					     'primary'=>(string)$field['primary'],
					     'null'=>(string)$field['null'],
						 'auto_increment'=>(string)$field['auto_increment'],
						 );
					 if(isset($field["default"]))
					 {
                        $tabarr[(string)$v_t['name']]['field'][(string)$field['name']]['default']=(string)$field['default'];
					 }
					 if(isset($field["comment"]) && $field["comment"]!='')
					 {
                        $tabarr[(string)$v_t['name']]['field'][(string)$field['name']]['comment']=(string)$field['comment'];
					 }
			 } 
		 }
		 //$sql='';
		 $sql='SET FOREIGN_KEY_CHECKS = 0;';
		 $query_tg="";//触发器使用
		 foreach($tabarr as $tabkey=>$tab)
		 {
          $query="";
		  $query.="DROP TABLE IF EXISTS `".$this->prefix.$tabkey."`;";
		  $query.="CREATE TABLE `".$this->prefix.$tabkey."` (";
		  foreach($tab['field'] as $fieldkey=>$field)
			{
			    if(strtolower($field["type"]) == 'key'){
					$strquery =$fieldkey;
				}elseif(strtolower($field["type"]) == 'foreign'){
					$strquery =$fieldkey;
				}elseif(strtolower($field["type"]) == 'trigger'){
					$query_tg .=$fieldkey;
					$strquery = '';
				}else{
					$strquery="`".$fieldkey."` ";
					$strquery.=$field["type"];
					if($field['auto_increment']==1) $strquery.=" auto_increment";
					//if($field["null"]==1) 
					$strquery.=" NOT NULL";
					if($field["primary"]==1) $strquery.=" PRIMARY KEY";
				}
				if(isset($field["default"]))
			     {
					if((string)$field["default"]=='')
					 {
						$strquery.=" default ''";
					 }elseif((string)$field["default"]==' ')
					 {
						$strquery.=" default ' '";
					 }elseif((string)$field["default"]=='NULL')
					 {
						$strquery.=" default NULL";
					 }else{
						$strquery.=" default '".(string)$field["default"]."'";
					 }
			      }
				if(isset($field["comment"]))
				{
					$strquery.=" COMMENT '".(string)$field["comment"]."'";
				}
			 	if($strquery != '')$query.=$strquery.",";
			}
			if(substr($query,strlen($query)-1,1)==",") $query=substr($query,0,strlen($query)-1);
			$query.=")";
			if((string)$tab['engine'] != '' && isset($tab["engine"]))
			{
                $query.=" ENGINE=".(string)$tab["engine"];
			}else{
                $query.=" ENGINE=MyISAM";
			}
			if(isset($tab["comment"]) && (string)$tab["comment"] != '')
			{
                $query.=" COMMENT = '".(string)$tab["comment"]."'";
	        }
			$query.=";";
			$sql.=$query;
		 }
		 $sql.=$query_tg;
		 $sql.='SET FOREIGN_KEY_CHECKS = 1;';
//  $sql.="INSERT INTO `node` VALUES ('388', 'DmsAdmin', '结算系统', '', '', '1', '0', '1', '1', '1', '1', 'Admin');";
//配置信息生成
		$sql.="
		INSERT INTO `dms_银行卡` VALUES (1, '中国银行', 'boc.jpg', '324', '234', '有效', '有效', 'http://www.boc.cn/ebanking/');
		INSERT INTO `dms_银行卡` VALUES (2, '中国建设银行', 'ccb.jpg', '2123', '213', '有效', '有效', 'http://www.ccb.com/cn/home/index.html');
		INSERT INTO `dms_银行卡` VALUES (3, '中国工商银行', 'icbc.jpg', '221', '213', '有效', '有效', 'http://www.icbc.com.cn/icbc/');
		INSERT INTO `dms_银行卡` VALUES (4, '中国农业银行', 'abc.jpg', '', '', '有效', '有效', 'http://www.abchina.com/cn/');
		INSERT INTO `dms_银行卡` VALUES (5, '财付通', 'cft.jpg', '', '', '有效', '有效', 'http://www.tenpay.com');
		INSERT INTO `dms_银行卡` VALUES (6, '支付宝', 'zfb.jpg', '', '', '有效', '有效', 'http://www.alipay.com');
		INSERT INTO `dms_银行卡` VALUES (7, '浦发银行', 'spdbank.jpg', '', '', '无效', '有效', 'http://www.spdb.com.cn/chpage/c1/');
		INSERT INTO `dms_银行卡` VALUES (8, '交通银行', 'comm.jpg', '', '', '无效', '有效', 'http://www.bankcomm.com/BankCommSite/cn/default.html');
		INSERT INTO `dms_银行卡` VALUES (9, '中国民生银行', 'cmbc.jpg', '', '', '无效', '有效', 'http://www.cmbc.com.cn/');
		INSERT INTO `dms_银行卡` VALUES (10, '中国光大银行', 'ceb.jpg', '', '', '无效', '有效', 'http://www.cebbank.com/Site/ceb/cn');
		INSERT INTO `dms_银行卡` VALUES (11, '中国邮政储蓄银行', 'psbc.jpg', '', '', '无效', '有效', 'http://www.psbc.com/portal/zh_CN/index.html');
		INSERT INTO `dms_银行卡` VALUES (12, '兴业银行', 'cib.jpg', '', '', '无效', '有效', 'http://www.cib.com.cn/cn/index.html');
		INSERT INTO `dms_银行卡` VALUES (13, '中信银行', 'citic.jpg', '', '', '无效', '有效', 'http://bank.ecitic.com/');
		INSERT INTO `dms_银行卡` VALUES (14, '招商银行', 'cmb.jpg', '234', '234', '无效', '有效', 'http://www.cmbchina.com/');
		INSERT INTO `dms_银行卡` VALUES (15, '华夏银行', 'hxb.jpg', '', '', '无效', '有效', 'http://www.hxb.com.cn/home/cn/');
		INSERT INTO `dms_快递`(company) values('EMS'),('宅急送'),('韵达快递'),('顺丰快递'),('申通快递'),('中通快递'),('圆通快递');
		";
		return $sql;
	}
	public static function gettag($html,$tagname)
	{
		preg_match("/$tagname\s*=\s*['\"]?([^\s\>'\"]+)['\"].*?/",$html,$out2);
		if(count($out2)==0)
		{
			return null;
		}
		return $out2[1];
	}
	public static function getcons()
	{
		$ret=array();
		$content=file_get_contents(ROOT_PATH."/DmsAdmin/config.xml");
		$content=substr($content,strpos($content,"<tleset"));
		$content=substr($content,strpos($content,">")+1);
		$content=substr($content,0,strpos($content,"</tleset>"));
		$content=$content;
		preg_match_all("/<input[^>]*\>|<select[^>]*\>/",$content,$inputs);
		foreach($inputs[0] as $input)
		{
			$name    =self::gettag($input,'name');
			$value   =self::gettag($input,'value');
			$type    =self::gettag($input,'type');
			$offvalue=self::gettag($input,'offvalue');
			//对比较特殊的情况进行处理,如果是checkbox的话.没有设置checked,则要使用offvalue作为value值
			if($type == 'checkbox')
			{
				if(self::gettag($input,'checked') == null)
				{
					$value=$offvalue;
				}
			}
			$ret[]=array('name'=>$name,'value'=>$value,'offvalue'=>$offvalue,'type'=>$type);
		}
		
		preg_match_all('/<select[^>]*[^>]*>[^<]*(<option[^>]*>[^<]*<\/option>[^<]*)*<\/select>/i',$content,$outs2);
		foreach($outs2[0] as $out2)
		{
			preg_match("/name\s*=\s*['\"]?([^\s\>'\"]+)['\"]?/",$out2,$out22);
			$name=$out22[1];
			preg_match_all('/<option[^>]*>[^<]*<\/option>/i',$out2,$uee);
			foreach($uee[0] as $uees){
				if(strpos($uees,'select')!==false)
				{
					$valuesel=self::gettag($uees,'value');
					$ret[]=array('name'=>$name,'value'=>$valuesel,'offvalue'=>'','type'=>'select');
				}
			}
		}
		return $ret;
	}
}
?>