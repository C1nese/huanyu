<?php
defined('APP_NAME') || die('不要非法操作哦!');
class Fun_stockAction extends CommonAction
{
	//股票设置
	public function config(fun_stock $fun_stock)
	{
		$this->assign('stockAllNum',$fun_stock->getatt('stockAllNum'));//公司发行量
		$this->assign('stockHasGive',$fun_stock->getatt('stockHasGive'));//已认购量
		$this->assign('stockMinint',$fun_stock->getatt('stockMinint'));//买卖整数倍
		$this->assign('decimalLen',$fun_stock->getatt('decimalLen'));//交易的小数位数
		$this->assign('buyComStock',$fun_stock->getatt('buyComStock'));//购买公司发行
		 //股票拆分
		 $this->assign('splitStart',$fun_stock->splitStart);
		 M()->startTrans();
		if(!CONFIG("","StockSplit:".$fun_stock->Path())){
			$fun_stock->setatt('StockSplit',2);
		}
		$this->assign('StockSplit',$fun_stock->getatt('StockSplit'));
		$this->assign('stockStartMoney',$fun_stock->getatt('stockStartMoney'));//起始单价

		//股票交易总数
		if(!CONFIG("","stockTrade:".$fun_stock->Path())){
			$fun_stock->setatt('stockTrade',0);
		  }
		 $this->assign('stockTrade',$fun_stock->getatt('stockTrade'));
		$this->assign('stockMoneyForm',$fun_stock->getatt('stockMoneyForm'));//价格计算公式

		if(!CONFIG("","stockNowPrice:".$fun_stock->Path())){
			$fun_stock->setatt('stockNowPrice',$fun_stock->stockPrice());
		}
		if(!CONFIG("","stockHighPrice:".$fun_stock->Path())){
			$fun_stock->setatt('stockHighPrice',0);
		}
		if(!CONFIG("","stockLowPrice:".$fun_stock->Path())){
			$fun_stock->setatt('stockLowPrice',0);
		}
		if(!CONFIG("","stockDrop:".$fun_stock->Path())){
			$fun_stock->setatt('stockDrop',0);
		}
		if(!CONFIG("","stockRise:".$fun_stock->Path())){
			$fun_stock->setatt('stockRise',0);
		}
		if(!CONFIG("","stockSec:".$fun_stock->Path())){
			$fun_stock->setatt('stockSec',0);
		}
		M()->commit();
		$this->assign('stockNowPrice',$fun_stock->stockPrice());//股票当前价格
		$this->assign('stockInputPrice',$fun_stock->getatt('stockInputPrice'));//买卖是否录入价格
		$this->assign('stockHighPrice',$fun_stock->getatt('stockHighPrice'));//买卖最高价格
		$this->assign('stockLowPrice',$fun_stock->getatt('stockLowPrice'));//买卖最低价格
		$this->assign('stockDrop',$fun_stock->getatt('stockDrop'));//买卖最高跌幅
		$this->assign('stockRise',$fun_stock->getatt('stockRise'));//买卖最高涨幅
		$this->assign('stockClose',$fun_stock->getatt('stockClose'));//股票休市
		$this->assign('stockBuybutton',$fun_stock->getatt('stockBuybutton'));//买入按钮开启
		$this->assign('stockSalebutton',$fun_stock->getatt('stockSalebutton'));//卖出按钮开启
		$this->assign('stockBuycancel',$fun_stock->getatt('stockBuycancel'));//买入撤销按钮开启
		$this->assign('stockSalecancel',$fun_stock->getatt('stockSalecancel'));//卖出撤销按钮开启
		$this->assign('stockSec',$fun_stock->getatt('stockSec'));//用户提交间隔
		$this->assign('stockname',$fun_stock->byname);
		//获取用户级别
		$level=array();
		$levels=X("levels@");
		foreach($levels->getcon("con",array("name"=>"","lv"=>0)) as $lev)
		{
			$level[$lev['lv']]['name'] = $lev['name'] ;
			$level[$lev['lv']]['max'] = $fun_stock->getatt('stockMax'.$lev['lv'])?$fun_stock->getatt('stockMax'.$lev['lv']):0;	
		}
		$this->assign('levels',$level);
		//$this->assign('stockOutTax',$fun_stock->getatt('stockOutTax'));//股票卖出手续费
		//$this->assign('stockBuyTax',$fun_stock->getatt('stockBuyTax'));//股票买入手续费
		//$this->assign('startStockAutoBuy',$fun_stock->getatt('startStockAutoBuy'));//三进三出开关
		//$this->assign('stockAutoBuyRate',$fun_stock->getatt('stockAutoBuyRate'));//三进三出比例
		//$this->assign('stockAutoBuy',$fun_stock->getatt('stockAutoBuy'));//三进三出金额自动购买
		//$this->assign('stockTrustMax',$fun_stock->getatt('stockTrustMax'));//托管账户限卖
		//$this->assign('TrustAuto',$fun_stock->getatt('TrustAuto'));//托管账户三进三出开关
		$this->display();
	}
	public function configSave($fun_stock)
	{
		if($_POST['decimalLen']>4){
			$_POST['decimalLen']=4;
		}
		M()->startTrans();
		$fun_stock->setatt('stockAllNum',$_POST['stockAllNum']);
		$fun_stock->setatt('stockHasGive',$_POST['stockHasGive']);
		$fun_stock->setatt('stockMinint',$_POST['stockMinint']);
		$fun_stock->setatt('decimalLen',$_POST['decimalLen']);
		if(isset($_POST['buyComStock']) && $_POST['buyComStock']=='true')
			{
			  $fun_stock->setatt('buyComStock',true);
			}else{
			 $fun_stock->setatt('buyComStock',false);
			}
		if(isset($_POST['stockInputPrice']) && $_POST['stockInputPrice']=='true')
			{
			  $fun_stock->setatt('stockInputPrice',true);
			}else{
			 $fun_stock->setatt('stockInputPrice',false);
			}
		$fun_stock->setatt('StockSplit',$_POST['StockSplit']);
		$fun_stock->setatt('stockStartMoney',$_POST['stockStartMoney']);	
		$fun_stock->setatt('stockHighPrice',$_POST['stockHighPrice']);
		$fun_stock->setatt('stockLowPrice',$_POST['stockLowPrice']);
	    $fun_stock->setatt('stockDrop',$_POST['stockDrop']);
	    $fun_stock->setatt('stockRise',$_POST['stockRise']);
		$fun_stock->setatt('stockTrade',$_POST['stockTrade']);
		$fun_stock->setatt('stockMoneyForm',$_POST['stockMoneyForm']);
		$fun_stock->setatt('stockSec',$_POST['stockSec']);
		 if(isset($_POST['stockClose']) && $_POST['stockClose']=='true')
			{
			  $fun_stock->setatt('stockClose',true);
			}else{
			 $fun_stock->setatt('stockClose',false);
			}
		if(isset($_POST['stockBuybutton']) && $_POST['stockBuybutton']=='true')
		{
			  $fun_stock->setatt('stockBuybutton',true);
		}else{
			 $fun_stock->setatt('stockBuybutton',false);
		}
		if(isset($_POST['stockSalebutton']) && $_POST['stockSalebutton']=='true')
		{
			  $fun_stock->setatt('stockSalebutton',true);
		}else{
			 $fun_stock->setatt('stockSalebutton',false);
		}
		if(isset($_POST['stockBuycancel']) && $_POST['stockBuycancel']=='true')
		{
			  $fun_stock->setatt('stockBuycancel',true);
		}else{
			 $fun_stock->setatt('stockBuycancel',false);
		}
		if(isset($_POST['stockSalecancel']) && $_POST['stockSalecancel']=='true')
		{
			  $fun_stock->setatt('stockSalecancel',true);
		}else{
			 $fun_stock->setatt('stockSalecancel',false);
		}
		//获取用户级别,存放封顶值
		$level=array();
		$levels=X("levels@");
		foreach($levels->getcon("con",array("name"=>"","lv"=>0)) as $lev)
		{	
			$temp="stockMax".$lev['lv'];
			if(empty($_POST[$temp])) $_POST[$temp]=0;
			 $fun_stock->setatt($temp,$_POST[$temp]);	
		}
		/*
		$fun_stock->setatt('stockOutTax',$_POST['stockOutTax']);
		$fun_stock->setatt('stockBuyTax',$_POST['stockBuyTax']);
		 $fun_stock->setatt('stockTrustMax',$_POST['stockTrustMax']);
		$fun_stock->setatt('stockAutoBuyRate',$_POST['stockAutoBuyRate']);	
		if(isset($_POST['stockAutoBuy']) && $_POST['stockAutoBuy']=='true')
			{
			  $fun_stock->setatt('stockAutoBuy',true);
			}else{
			 $fun_stock->setatt('stockAutoBuy',false);
			}
		if(isset($_POST['startStockAutoBuy']) && $_POST['startStockAutoBuy']=='true')
			{
			  $fun_stock->setatt('startStockAutoBuy',true);
			}else{
			 $fun_stock->setatt('startStockAutoBuy',false);
			}
			if(isset($_POST['TrustAuto']) && $_POST['TrustAuto']=='true')
			{
			  $fun_stock->setatt('TrustAuto',true);
			}else{
			 $fun_stock->setatt('TrustAuto',false);
			}*/
		M()->commit();
		$this->success('设置完成');
	}
	//股票列表，显示有股票的用户
	public function index($fun_stock)
	{
		$setButton=array(    
				$fun_stock->byname.'充值'=>array("class"=>"add","href"=>__APP__."Admin/Fun_stock/addin:__XPATH__","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"260"),
        );
        $list=new TableListAction("用户");
        $list->setButton = $setButton;
		$list->where(array($fun_stock->name=>array('gt',0)))->order($fun_stock->name." desc");
        $list->addshow("用户编号",array("row"=>"[编号]","searchMode"=>"text","excelMode"=>"text","searchPosition"=>"top"));
        $list->addShow($fun_stock->byname."数量",array("row"=>"[".$fun_stock->name."]","searchMode"=>"text","excelMode"=>"text"));
        $this->assign('list',$list->getHtml());    
        $this->display();
	}
	//股票充值页面
	public function addin($fun_stock)
	{
		$this->assign('username',"用户编号");
		$this->assign('stocknum',$fun_stock->byname."数量");
        $this->display();
	}
	//股票充值保存
	public function savein($fun_stock)
	{
		$user_model=M("用户");
		$data=array();
		$data['编号']=$_POST['编号'];
		$data['num']=floatval($_POST['num']);
		if($data['num']=='' || $data['num']<=0)  $this->error('请输入充值数量');
		M()->startTrans();
		$user=$user_model->where(array('编号'=>$data['编号']))->find();
		if($user){			
			$fun_stock->setrecord($data['编号'],0,$data['num'],$fun_stock->name,"后台充值".$data['num']."股",2);
			M()->commit();
			$this->success("添加".$fun_stock->name.'成功');
			
		}else{
			M()->rollback();
			$this->error("用户".$data['编号']."不存在");
		}
	}
	
	 //股票明细
	public function record($fun_stock)
	{
        $list=new TableListAction($fun_stock->name.'明细');
        $list->setButton = $setButton;
		$list->order("id asc");
        $list->addshow("时间",array("row"=>"[addtime]","searchMode"=>"date","format"=>"time","order"=>"时间"));
		$list->addshow("类型",array("row"=>array(array(&$this,"tradetype"),"[type]"),"searchMode"=>"text","searchPosition"=>"top",'searchRow'=>'[type]',"searchSelect"=>array('增加'=>2,"减少"=>1)));
        $list->addshow("编号",array("row"=>"[编号]","searchMode"=>"text","excelMode"=>"text","order"=>"编号","searchPosition"=>"top"));
        $list->addShow("交易数量",array("row"=>array(array(&$this,"tradenum"),"[num]","[type]"),"searchMode"=>"num","excelMode"=>"text"));
		$list->addShow("余额",array("row"=>array(array(&$this,'formatNum'),"[余额]"),"searchMode"=>"num","excelMode"=>"text"));
        $list->addshow("交易价格",array("row"=>array(array(&$this,'formatPrice'),"[price]"),"excelMode"=>"num","searchMode"=>"num"));
	    //$list->addShow($fun_stock->name."账户",array("row"=>"[账户]","searchMode"=>"text","searchPosition"=>"top"));
		$list->addshow("备注",array("row"=>"[memo]","excelMode"=>"text"));
        $this->assign('list',$list->getHtml());              // 分配到模板
        $this->display();
	}
	
	public function tradetype($type){
		if($type==1) return "减少";
		if($type==2) return "增加";
	}

	//股票交易
	public function trade($fun_stock)
	{
        $list=new TableListAction($fun_stock->name.'交易');
        $list->setButton = $setButton;
		$list->order("addtime desc");
        $list->addshow("时间",array("row"=>"[addtime]","searchMode"=>"date","format"=>"time","order"=>"时间"));
		$list->addshow("买入ID",array("row"=>"[买入ID]","searchMode"=>"text","excelMode"=>"text","searchPosition"=>"top"));
        $list->addshow("买入编号",array("row"=>"[买入编号]","searchMode"=>"text","excelMode"=>"text","order"=>"编号","searchPosition"=>"top"));
        $list->addShow("交易数量",array("row"=>"[num]","searchMode"=>"text","excelMode"=>"text"));
        $list->addshow("交易价格",array("row"=>array(array(&$this,'formatPrice'),"[price]"),"excelMode"=>"text","searchMode"=>"num"));
		$list->addshow("卖出ID",array("row"=>"[卖出ID]","searchMode"=>"text","excelMode"=>"text","searchPosition"=>"top"));
        $list->addshow("卖出编号",array("row"=>"[卖出编号]","excelMode"=>"text","searchMode"=>"text","searchPosition"=>"top"));
		
        $this->assign('list',$list->getHtml());
        $this->display();
	}

	//股票市场
	public function shop($fun_stock)
	{
		$setButton=array(                 // 底部操作按钮显示定义
				/*'添加交易'=>array("class"=>"add","href"=>__APP__."Admin/Fun_stock/add:__XPATH__","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"260"),
				'编辑'=>array("class"=>"edit","href"=>__APP__."Admin/Fun_stock/edit:__XPATH__/id/{tl_id}","target"=>"dialog","mask"=>"true","width"=>"520","height"=>"260"),
				'删除'=>array("class"=>"delete","href"=>__APP__."Admin/Fun_stock/delete:__XPATH__/id/{tl_id}","target"=>"ajaxTodo","mask"=>"true","title"=>"确定要删除该数据吗？"),*/
				'撤销全部'=>array("class"=>"delete","href"=>__APP__."Admin/Fun_stock/cancelall:__XPATH__","target"=>"ajaxTodo","mask"=>"true","title"=>"确定要撤销所有正在交易的买卖订单吗？"),

        );
        $list=new TableListAction($fun_stock->name.'市场');
        $list->setButton = $setButton;
		$list->order("addtime desc");
		$list->addshow("挂单时间",array("row"=>"[addtime]","format"=>"time","searchMode"=>"text","excelMode"=>"text","searchPosition"=>"top"));
        $list->addshow("用户编号",array("row"=>"[编号]","searchMode"=>"text","excelMode"=>"text","searchPosition"=>"top"));
        $list->addShow($fun_stock->byname."原始数量",array("row"=>"[num1]","searchMode"=>"text","excelMode"=>"text"));
		$list->addShow($fun_stock->byname."已成交量",array("row"=>"[num2]"));
		$list->addShow($fun_stock->byname."剩余数量",array("row"=>"[num]"));
		$list->addShow($fun_stock->byname."价格",array("row"=>array(array(&$this,'formatPrice'),"[price]",$fun_stock),"searchMode"=>"text","excelMode"=>"num"));
		$list->addShow($fun_stock->byname."总价",array("row"=>array(array(&$this,"stockAllprice"),"[price]","[num1]")));
		//$list->addShow("账户类型",array("row"=>"[账户]","searchMode"=>"text","searchPosition"=>"top","searchSelect"=>array($fun_stock->name=>$fun_stock->name,$fun_stock->name."托管"=>$fun_stock->name."托管")));
		$list->addShow("类型",array("row"=>array(array(&$this,"stocktype"),"[type]",$fun_stock->Path()),"searchMode"=>"text","searchPosition"=>"top",'searchRow'=>'[type]',"searchSelect"=>array('买入'=>2,"卖出"=>1)));
		$list->addShow("状态",array("row"=>array(array(&$this,"saleType"),"[state]"),"searchMode"=>"text","searchPosition"=>"top",'searchRow'=>'[state]',"searchSelect"=>array('正常'=>0,'已撤销'=>1)));
		$list->addShow("交易信息",array("row"=>array(array(&$this,"tradeInfo"),"[id]","[tradeinfo]")));
        $this->assign('list',$list->getHtml());          // 分配到模板
        $this->display();
	}
	
	public function cancelall($fun_stock)
	{
		$where=array();
		$where['num']=array('gt',0);
		$where['state']=array('eq',0);
		M()->startTrans();
		$all=M($fun_stock->name."市场")->where($where)->count('id');
		if(!$all){
			$this->error('没有符合的订单');
		}
		$fun_stock->cancelall();
		M()->commit();
		$this->success('订单撤销成功');
	}
	
	public function stockAdd($fun_stock)
	{
		$this->assign('stockname',$fun_stock->name);
		$this->assign('hyname',$fun_stock->parent()->name);
		$this->display();
	}
	public function stockAddSave($fun_stock)
	{
		$m_user = M('用户');
		M()->startTrans();
		$user  = $m_user->where(array('编号'=>$_POST['userid']))->find();
		if(!$user)
		{
			$this->error('用户不存在');
		}
		$num=$_POST['num'];
		if($num =='' || intval($num) == 0)
		{
			$this->error('数量不合法');
		}

		$fun_stock->setrecord($user['编号'],0,$num,$_POST['account'],"后台充值".$_POST['memo'],2);
		M()->commit();
		$this->success('充值成功');
	}

	//股票拆骨
	public function stockSplit($fun_stock)
	{
		$this->assign('stockname',$fun_stock->name);
		$this->assign('StockSplit',$fun_stock->getatt('StockSplit'));
		$this->display();
	}
	public function intwp()
	{
		$this->assign("splitnum",$_REQUEST['splitnum']);
		$this->display();
	}
	//股票拆股
	public function splitSave($fun_stock)
	{
		$repwd=$_REQUEST['repwd'];
		if($repwd=='') $this->error("请填写密码");
		$where['id'] = $_SESSION[C('RBAC_ADMIN_AUTH_KEY')];
		M()->startTrans();
        $result=M()->table("admin")->where($where)->field("password")->find();
        if($result['password']!=md100($repwd,"EN")){

            $this->error("管理员密码错误");
        }

		$fun_stock->stockPrice();
		$num=$_REQUEST['splitnum'];
		if($num=="" || $num<=0 || $num==1){
			M()->rollback();
		  $this->error("拆分倍数不合法");
		}
		$fun_stock->cancelall();
		$fun_stock->splitstock($num);
		$fun_stock->upconf($num);
		M()->commit();
		$this->success('拆分完成');
	}
	//一键挂单显示
	public function stockComSell($fun_stock)
	{
		$this->assign('stockname',$fun_stock->name);
		$this->assign('stockNowPrice',$fun_stock->stockPrice());//股票当前价格
		//$this->assign('stockHighPrice',$fun_stock->getatt('stockHighPrice'));//买卖最高价格
		//$this->assign('stockLowPrice',$fun_stock->getatt('stockLowPrice'));//买卖最低价格
		//获取用户级别
		$level=array();
		$levels=X("levels@");

		foreach($levels->getcon("con",array("name"=>"","lv"=>"")) as $lvconf)
		{
			$level[$lvconf['lv']]['name'] = $lvconf['name'] ;
			$level[$lvconf['lv']]['max'] = $fun_stock->getatt('stockfd'.$lvconf['lv']);
		}
      
		$this->assign('levels',$level);
		$this->assign('formula',$fun_stock->getatt('formula'));
		$this->display();
	}
	//一键挂单操作
	public function stockComSellSave($fun_stock)
	{
		$u_model=M("用户");
		$uname="用户";
		$account=$fun_stock->name;
		if($fun_stock===false){
		   $this->error(L('参数错误'));
		}
		/*
		if($_POST['minnum']!='' && (!is_numeric($_POST['minnum'])  || $_POST['minnum']<=0)){
			$this->error(L('填写的最低出售数量不合法'));
		}
		if($_POST['maxnum']!='' && (!is_numeric($_POST['maxnum'])  || $_POST['maxnum']<=0)){
			$this->error(L('填写的最高出售数量不合法'));
		}*/

		if(!is_numeric($_POST['formula']) || $_POST['formula']<=0){
			$this->error(L('填写的公式分母不合法'));
		}
		if($_POST['sellprice']!='' && (!is_numeric($_POST['sellprice']) || $_POST['sellprice']<=0)){
			$this->error(L('填写的出售金额不合法'));
		}

		//出售价格
		$price=$fun_stock->stockPrice();//默认当前价格
		if($_POST['sellprice']>0) $price=$_POST['sellprice'];
		if($price<=0){
			$this->error(L('系统暂无当前价格'));
		}
		//初始化查询条件
		$where=$account.">0";
		//先更新参数
		M()->startTrans();
		$fun_stock->setatt('formula',$_POST['formula']);
		//获取用户级别
		$level=array();
		$levels=X("levels@");
		$maxary=array();
	
			foreach($levels->getcon("con",array("name"=>"","lv"=>"")) as $lvconf)
			{
				$fun_stock->setatt('stockfd'.$lvconf['lv'],$_POST['stockfd'.$lvconf['lv']]);
				//同时根据级别查找条件
				if($lvconf['lv']==1) $where.=" and (";
				else $where.=" or ";
				$where.="(".$uname."级别=".$lvconf['lv']." and ".$account.">".$_POST['stockfd'.$lvconf['lv']].")";
				//每一级别的限制数量
				$maxary[$lvconf['lv']]=$_POST['stockfd'.$lvconf['lv']];
 			}
   
		$where.=")";
		//dump($where);
		//dump($maxary);
		/*
		//查询符合条件的用户
		if($_POST['minnum']>0)
		{
			$where[$fun_stock->name]=array("egt",intval($_POST['minnum']));
		}else{
			$where[$fun_stock->name]=array("gt",0);
		}
		*/
		
		$users=$u_model->where($where)->field('编号,'.$account.",".$uname."级别")->select();

		if($users){
			foreach($users as $user){
				$num=floor(($user[$account]-$maxary[$user[$uname."级别"]])/$_POST['formula']);

				//扣除交易货币流程
				$fun_stock-> setrecord($user['编号'],$price,$num,$account,"公司一键发布".$fun_stock->name."卖出订单",1);
				//创建挂单记录
				$fun_stock->setcompany($user['编号'],$price,$num,$account,1);
			}
			M()->commit();
			$this->success(L("出售完成"));
		}else{
			M()->rollback();
			$this->error(L('没有符合条件的用户'));
		}


	}
	
	//股票走势
	public function stockTrend($fun_stock)
	{
        $list=new TableListAction($fun_stock->name.'走势');
        $setButton=array(                 // 底部操作按钮显示定义
				'编辑'=>array("class"=>"edit"  ,"href"=>__APP__."Admin/Fun_stock/stockTrendedit:__XPATH__/id/{tl_id}","target"=>"dialog","mask"=>"true","width"=>"350","height"=>"220","title"=>"编辑走势"),
				'删除'=>array("class"=>"delete","href"=>__APP__."Admin/Fun_stock/stockTrenddelete:__XPATH__/id/{tl_id}","target"=>"ajaxTodo","mask"=>"true","title"=>"确定要删除该数据吗？"),
        );
		//$list->setButton = $setButton;
		$list->order("计算日期 desc");
        $list->addshow("时间",array("row"=>"[计算日期]","searchMode"=>"date","format"=>"date","order"=>"[计算日期]","searchMode"=>"date","searchPosition"=>"top"));
        $list->addshow("最后成交价格",array("row"=>"[价格]"));
        $list->addShow("成交量",array("row"=>array(array(&$this,'formatNum'),"[成交量]")));
        $list->addshow("认购量",array("row"=>array(array(&$this,'formatNum'),"[认购量]")));
        $list->addshow("成交金额",array("row"=>"[成交金额]"));
        $this->assign('list',$list->getHtml());
        $this->display();
	}
	//限制交易价格小数位
	public function formatPrice($price,$fun_stock){
		$decimalLen=$fun_stock->getatt('decimalLen');
		return number_format($price,$decimalLen,'.','');
	}
	//限制股票显示
	public function formatNum($price){
		return intval($price);
	}

  
	public function tradenum($num,$type){
        if($type==1) return "-".$num;
		if($type==2) return "+".$num;
	}
	
	
	public function tradedetail()
	{
		$fun_stock=X(">");
		if(!isset($_REQUEST['id']) || $fun_stock==false || $_REQUEST['id']==''){
		   $this->error("参数错误");
		}
		$tradeinfo=M($fun_stock->name."市场")->where(array('id'=>$_REQUEST['id']))->getField('tradeinfo');
		$this->assign('info',unserialize($tradeinfo));
		//小数位数
		$decimalLen=$fun_stock->getatt('decimalLen');
		$this->assign('decimalLen',$decimalLen);
		$this->display();

	}
	public function tradeInfo($id,$tradeinfo)
	{
		$tradeinfo=unserialize($tradeinfo);
		if(empty($tradeinfo)){
		  return "未售";
		}
	   return "<a href='__URL__/tradedetail:__XPATH__/id/".$id."' target='dialog' mask='true'>点击查看</a>";
	}
	public function saleType($type)
	{
		if($type==0) return '正常';
		if($type==1) return '已撤销';
		//return $fun->type[$type];
	}
	public function stocktype($type,$path)
	{
		if($type==1) return '卖出';
		if($type==2) return '买入';
		//return $fun->type[$type];
	}
	public function stockAllprice($price,$num)
	{
         return $this->formatPrice($price*$num);
	}
	
	//股票增加
	public function add($fun_stock)
	{
		$this->assign('type',$fun_stock->type);
        $this->assign('name',$fun_stock->name);
		$this->assign('username',"用户编号");
		$this->assign('stocknumName',$fun_stock->name."数量");
		$this->assign('stockprizeName',$fun_stock->name."价格");
		$this->assign('stockprize',$fun_stock->stockPrice());
        $this->display();
	}
	public function save($fun_stock)
	{
		$modle=M("用户".'_'.$fun_stock->name."市场");
		$data=$modle->create();
		if(!$data) $this->error('获取数据失败');
		M()->startTrans();
		$user_model=M("用户");
		$user=$user_model->where("编号='".$data['编号']."'")->find();
		if($user){
           if($user[$fun_stock->name]<$data['num']){
           	   M()->rollback();
		     $this->error('该'."用户".'的'.$fun_stock->name.'数量不足'.$data['num']);
		   }else{
		     $data['addtime']=systemTime();
			 $data['num1']=$data['num'];
			 $data['tradeinfo']=$fun_stock->encode();
		      $rsadd=$modle->add($data);
		        if($rsadd){
					$fun_stock->setrecord1($data['编号'],$data['price'],$data['num'],$data['type']);
					M()->commit();
		            $this->success("添加".$fun_stock->name.'成功');
	            }else{
	            	M()->rollback();
			          $this->error("添加".$fun_stock->name.'失败');
		           }
		   }
		}else{
			M()->rollback();
			$this->error("用户".$data['编号']."不存在");
		}

	}
	public function edit($fun_stock)
	{
        $this->assign('name',$fun_stock->name);
        $this->display();
	}
	public function delete($fun_stock)
	{
		$id=$_REQUEST['id'];
		M()->startTrans();
		if(M("用户".'_'.$fun_stock->name)->where("id={$id}")->delete()){
			M()->commit();
			$this->success('删除成功');
		}else{
			M()->rollback();
		    $this->error('删除失败');
		}
	}
	

	

	public function stockTrendedit($fun_stock)
	{
		$vo=M($fun_stock->name.'走势')->where(array("id"=>$_REQUEST["id"]))->find();
		$this->assign('vo',$vo);
        $this->display();
	}
	public function stockTrenddelete($fun_stock)
	{
		$id=$_REQUEST['id'];
		M()->startTrans();
		if(M("用户".'_'.$fun_stock->name."走势")->where("id={$id}")->delete()){
			M()->commit();
			$this->success('删除成功');
		}else{
			M()->rollback();
		    $this->error('删除失败');
		}
	}
	public function stockTrendsave($fun_stock)
	{
		$id=$_REQUEST['id'];
		$modle=M("用户".'_'.$fun_stock->name."走势");
		$data=$modle->create();
		if(!$data) $this->error('获取数据失败');
		M()->startTrans();
		$rsadd=$modle->where(array("id"=>$id))->save($data);
		 if($rsadd){
			M()->commit();
		    $this->success('成功');
	     }else{
	     	 M()->rollback();
			$this->error('失败');
		 }
	}

	//
	public function stockAnalysis($fun_stock)
	{
		$hyname=$fun_stock->parent()->name;
		$m_user = M($hyname);
		$m_sc = M($fun_stock->name.'市场');
		//持有交易股
		$tradeHave = $m_user->sum($fun_stock->name.'账户');
		//持有托管股
		$trustHave = $m_user->sum($fun_stock->name.'托管');
		//普通股卖出
		$tradeSell = $m_sc->where("type=1 and num>0 and 账户='".$fun_stock->name."账户'")->sum('num');
		//普通股买入
		$tradeBuy  = $m_sc->where("type=2 and num>0 and 账户='".$fun_stock->name."账户'")->sum('num');
		//托管股卖出
		$trustSell = $m_sc->where("type=1 and num>0 and 账户='".$fun_stock->name."托管'")->sum('num');
		//总持有量
		$allHave   = $tradeHave + $trustHave + $tradeSell + $trustSell;
		$this->assign('data',array('allHave'=>$allHave,'tradeHave'=>$tradeHave,'trustHave'=>$trustHave,'tradeSell'=>$tradeSell,'trustSell'=>$trustSell));
		$this->display();
	}
	
}
?>