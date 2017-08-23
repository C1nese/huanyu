<?php
defined('APP_NAME') || die(L('not_allow'));
class Fun_stockAction extends CommonAction {
	public function index(fun_stock $fun_stock)
	{
		$stockClose=$fun_stock->getatt('stockClose');
		if($stockClose){
			$this->error("交易市场尚未开启，请耐心等待");
		}
		$userinfo=$this->userinfo;
		$price=$fun_stock->stockPrice();
		//出售中
		$stockSale=M($fun_stock->name."市场")->where(array("编号"=>$userinfo['编号'],'type'=>1,'state'=>0))->sum("num");
		if(!$stockSale) $stockSale=0;
		//总拥有量
		$stockTotal=$stockSale+$userinfo[$fun_stock->name];
		$this->assign("stockSale",$stockSale);
		$this->assign("stockTotal",$stockTotal);
		$this->assign('price',$this->formatPrice($price));
		$this->assign('trade',$userinfo[$fun_stock->tradeBank]);//交易账户
		$this->assign('tradeName',$fun_stock->tradeBank);//交易账户		
		$this->assign('decimalLen',$fun_stock->getatt('decimalLen'));//小数位数
		$this->assign('stockBuybutton',$fun_stock->getatt('stockBuybutton'));//买入按钮开启
		$this->assign('stockSalebutton',$fun_stock->getatt('stockSalebutton'));//卖出按钮开启
		$this->assign('stockBuycancel',$fun_stock->getatt('stockBuycancel'));//买入撤销按钮开启
		$this->assign('stockSalecancel',$fun_stock->getatt('stockSalecancel'));//卖出撤销按钮开启
		$this->assign('stockInputPrice',$fun_stock->getatt('stockInputPrice'));//买卖是否录入价格
		$this->assign('highprice',$fun_stock->getatt('stockHighPrice'));//最高价
		$this->assign('lowprice',$fun_stock->getatt('stockLowPrice'));//最低价
		//$this->assign('startprice',$fun_stock->getatt('stockStartMoney'));
		//$this->assign('trust',$userinfo[$fun_stock->name."托管"]);//托管账户
		//$this->assign('cash',$userinfo[$fun_stock->cashBank]);//现金账户
		//$this->assign('stockAllNum',$fun_stock->getatt('stockAllNum'));//公司发行量
		//$this->assign('stockHasGive',$fun_stock->getatt('stockHasGive'));//已认购量
		$this->assign('stockName',$fun_stock->byname);
		$list = $this->getxml();
		$this->list = $list;
		$lists=M($fun_stock->name."市场")->where(array('编号'=>$userinfo['编号'],'state'=>0))->order("addtime desc")->select();
		$this->assign('lists',$lists);
		$this->display();
	}

	//限制交易价格小数位
	public function formatPrice($price){
		$fun_stock=X(">");
		$decimalLen=$fun_stock->getatt('decimalLen');
		return number_format($price,$decimalLen,'.','');
	}

	public function getxml(fun_stock $fun_stock)
	{
	   	$model = M($fun_stock->name.'走势');
		$num = $model->count();
		$num2 = $num;
		$list = array();
		if($num && $num<=30){
			$list=$model->limit($num)->order("id asc")->field('计算日期,价格')->select();
			$dtime = $list[$num-1]['计算日期'];
			for($i=0;$i<(30-$num2);$i++){
				$num++;	
				//补足不够的天数
				$list[$num]['价格'] = '0.10';
				$list[$num]['计算日期'] = $dtime+($i+1)*24*3600;
				
			}
		}else if($num && $num>30){
			$list = $model->limit(30)->order('id desc')->field('计算日期,价格')->select();
			asort($list);					//倒序
		}else{
			for($i=0;$i<(30-$num2);$i++){
				$list[$num]['价格'] = '0.10';
				$list[$num]['计算日期'] = time()+$i*24*3600;
				$num++;	
			}
		}
		
		return $list; 
	}

	//判断录入条件
	public function submitJudge($type){

		$fun_stock=X(">");$hint='';
		$stockClose=$fun_stock->getatt('stockClose');
		if($stockClose){
			$this->error("交易市场尚未开启，请耐心等待");
		}
		//当前价
		$price=$fun_stock->stockPrice();
		
		//提交间隔
		$stockSec=$fun_stock->getatt('stockSec');
		//10秒内只允许提交一次交易委托
		if($stockSec>0){
			$rschk=M($fun_stock->name."市场")->where("编号='".$this->userinfo['编号']."' and  (addtime+".$stockSec.")>".systemTime())->field('id')->find();
			if ($rschk['id']){
				$hint.=$stockSec."秒内只允许提交一次交易委托<br>";
				
			}
		}
		
		if(intval($_POST['num'])<=0){
			$hint.=L('数量输入有误')."<br>";
		}
		if(floatval($_POST['price'])<=0){
			$hint.=L('单价输入有误')."<br>";
		}else{
			//如果开启了用户自定义价格
			if($fun_stock->getatt('stockInputPrice')){
				$lowprice=$fun_stock->getatt('stockLowPrice');
				$highprice=$fun_stock->getatt('stockHighPrice');
				$stockDrop=$fun_stock->getatt('stockDrop');
				$stockRise=$fun_stock->getatt('stockRise');
				//先判断涨跌幅
				if ($price>$_POST['price']){	
					if (($price-$price*$stockDrop/100)>$_POST['price']){
						$hint.="价格超出了跌的限制"."<br>";
					}
				}else{
					
					if (($price+$price*$stockRise/100)<$_POST['price']){
						$hint.="价格超出了涨的限制"."<br>";
					}
				}
				
				if($_POST['price']<$lowprice && $lowprice>0){
					$hint.=L('单价不能低于').$lowprice."<br>";
				}
				if($_POST['price']>$highprice && $highprice>0){
					$hint.=L('单价不能高于').$highprice."<br>";
				}
			}else{
				$_POST['price']=$price;//当前价
			}
		}
		//判断密码
		if($_POST['password']==''){
			$hint.="请输入交易密码<br>";
		}elseif(md100($_POST['password'],'EN') !== $this->userinfo['pass2']){
			$hint.="交易密码错误<br>";
		}
		return $hint;
	}

   //1卖出 2 买入
	public function stock_buy(fun_stock $fun_stock)
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
	     B('XSS');
		M()->startTrans();
		$userinfo=$this->userinfo;
		if(!$fun_stock->getatt('stockBuybutton')){
			$this->error(L("买入交易临时关闭，稍后开启，请耐心等待"));
		}

		//判断录入条件
		$msg=$this->submitJudge('买入');
		if($msg!=''){
			$this->error($msg);
		}
		//判断封顶，出售中
		$stockSale=M($fun_stock->name."市场")->where(array("编号"=>$userinfo['编号'],'type'=>1,'state'=>0))->sum("num");
		//总拥有量
		$stockTotal=$stockSale+$userinfo[$fun_stock->name];
		//用户最多买入
		$maxnum=$fun_stock->getatt('stockMax'.$userinfo[$fun_stock->parent()->name.'级别']);

		$total=$stockTotal+$_POST['num'];
		if($maxnum>0){
			if($stockTotal>=$maxnum){
				$this->error("您买入的".$fun_stock->name."已达到封顶值");
			}elseif($total>$maxnum){
				$cha=$total-$maxnum;
				$this->error("您还能买入".$cha);
			}
		}
		list($fun_stock,$price,$num,$money,$user)=$this->sellbuydata();

		if(!$fun_stock->tradeMoney($money,$user)){

			  $this->error(L($fun_stock->tradeBank).L('余额不足'));
		}
		
		// 防止点击多次提交按钮，重复提交
        $checks = M('用户');
        if(!$checks->autoCheckToken($_POST)){
            redirect(__URL__."/index:__XPATH__",2,L("完成"));
        }
        
		//扣款
		bankset($fun_stock->tradeBank,$user['编号'],-$money,L($fun_stock->name).L('买入'),L('购买').$num.L('股'));

		//创建挂单记录
	    $fun_stock->setcompany($user['编号'],$price,$num,$fun_stock->name,2);
		M()->commit();
		$this->success(L("完成"));
	}

	//股票出售
    public function stock_sell()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
	     B('XSS');
		M()->startTrans();
		$fun_stock=X(">");
		if(!$fun_stock->getatt('stockSalebutton')){
			$this->error(L('卖出交易临时关闭，稍后开启，请耐心等待'));
		}
		//判断录入条件
		$msg=$this->submitJudge('买入');
		if($msg!=''){
			$this->error($msg);
		}
		list($fun_stock,$price,$num,$money,$user)=$this->sellbuydata();
		//判定余额
		if(!$fun_stock->judgeNum($num,$user,$fun_stock->name)){
			  $this->error(L($fun_stock->byname).L('数量不足'));
		}
		// 防止点击多次提交按钮，重复提交
        $checks = M('用户');
        if(!$checks->autoCheckToken($_POST)){
            redirect(__URL__."/index:__XPATH__",2,L("完成"));
        }
		//扣除交易货币流程
		$fun_stock-> setrecord($user['编号'],$price,$num,$fun_stock->name,L('发布').L($fun_stock->name).L('卖出订单').L('出售').$num.L('股'),1);
		//创建挂单记录
	    $fun_stock->setcompany($user['编号'],$price,$num,$fun_stock->name,1);
		M()->commit();
		$this->success(L("完成"));
	}	

	//取得买入卖出的基础数据
	public function sellbuydata()
	{
		//取得数值并进行有效判定
		$fun_stock=X(">");
		$price=isset($_REQUEST['price'])?$_REQUEST['price']:$fun_stock->stockPrice();

		$num  =$_REQUEST['num'];
		if($fun_stock->getatt('stockMinint')>0)
		{
            if($num%$fun_stock->getatt('stockMinint') !=0){
				$this->error(L('数量不合法，必须为').$fun_stock->getatt('stockMinint').L('的整数倍'));
			}
		}
		return array($fun_stock,$price,$num,$price*$num,$this->userinfo);
	}

   //股票市场中的交易记录查看
	public function tradedetail()
	{
		$stockobj=X(">");
	    if(!isset($_REQUEST['id']) || $_REQUEST['id']=='' || $stockobj===false){
			$this->error(L('参数错误'));
		}
		$info=M($stockobj->name."市场")->where(array('id'=>$_REQUEST['id']))->find();
		$this->assign('infos',unserialize($info['tradeinfo']));
		$this->assign("name",L($stockobj->parent()->name).' '.$info['编号'].L($stockobj->name).L('交易记录'));
		$this->display();
	}

	//查看股票账户前100个交易 1卖出 2 买入
	public function viewlist()
	{
		$fun_bank=X(">");
	    if(!isset($_REQUEST['mode']) || $_REQUEST['mode']==''){
		   $this->error(L('参数错误'));
		}
		if(!isset($_REQUEST['account']) || $_REQUEST['account']=='')
		{
			$this->error(L('参数错误'));
		}
		if($_REQUEST['mode']=='sell') $type=1;
		if($_REQUEST['mode']=='buy') $type=2;
		if($_REQUEST['mode']=='zhuan') die;
		$account=($_REQUEST['account']=='stock')?$fun_bank->stockBank:$fun_bank->name."托管";
        //$p=isset($_REQUEST['p'])?$_REQUEST['p']:0;
		//$num=10;
		$where=array();
        $where=array(
			'账户'=>$account,
			'type'=>$type,
			'state'=>0,
			'num'=>array('gt',0)
			);
		if($type==1){
			$order="price asc,addtime asc";
		}else{
			$order="price desc,addtime asc";
		}
		//先查出第100条的id
		$count = M($fun_bank->parent()->name."_".$fun_bank->name."市场")->where($where)->count();
		if($count>100){
			$list100=M($fun_bank->parent()->name."_".$fun_bank->name."市场")->where($where)->order($order)->limit('0,100')->select();
			$idstr='';
			foreach($list100 as $val){
				if($idstr!='') $idstr.=',';
				$idstr.=$val['id'];
			}
			$where['id']=array('in',$idstr);
		}
		//重新统计100条
		//$count = M($fun_bank->parent()->name."_".$fun_bank->name."市场")->where($where)->count();
		$list1 = new TableListAction($fun_bank->parent()->name."_".$fun_bank->name."市场");
        $list1 ->where($where)->order($order);
        /*$list1 ->setShow = array(
            L('账号') => array("row"=>"[编号]"),
			L('数量') => array("row"=>"num"),
            L('交易价格') => array("row"=>"[price]"),
			L('时间') => array("row"=>"[addtime]","format"=>"time"),
        );*/
        $list = $list1 ->getData();
		//$list=M($fun_bank->parent()->name."_".$fun_bank->name."市场")->where($where)->order($order)->page($p.",".$num)->select();
/*
		import("ORG.Util.Page");

		$Page  = new Page($count,$num);
		$show       = $Page->show();
		$this->assign('page',$show);
		$this->assign('num',$num);
		$this->assign('p',$p);*/
		$this->assign("data",$list);
		$this->assign('decimalLen',$fun_bank->getatt('decimalLen'));//小数位数
		$this->display();
	}
	public function deal_list()
	{
		$fun_stock=X(">");
		$user=$this->userinfo;
		$list=M($fun_stock->name."交易")->where("买入编号='".$user['编号']."' OR 卖出编号='".$user['编号']."'")->select();
		$this->assign('lists',$list);
		$this->assign('stockName',$fun_stock->byname);
		$this->display();
	}
	//交易明细
	public function deal_detail()
	{
		$fun_stock=X(">");
		$user=$this->userinfo;
		$list=M($fun_stock->name."明细")->where(array('编号'=>$user['编号']))->select();
		$this->assign('lists',$list);
		$this->assign('stockName',$fun_stock->byname);
		$this->display();
	}
	//挂单列表
	public function selllist(fun_stock $fun_stock){
		$list = new TableListAction($fun_stock->name.'市场');
        $list ->where(array('编号'=>$this->userinfo['编号'],"num"=>array("gt",0),"state"=>0))->order("addtime desc,id desc")->limit(15);
        $list ->setShow = array(
            L('挂单日期')=>array("row"=>"[addtime]","format"=>"time"),
            L('类型')=>array("row"=>array(array(&$this,"stocktype"),"[type]")),
            L('挂单价')=>array("row"=>"[price]"),
            L('交易量')=>array("row"=>"[num2]"),
            L('剩余量')=>array("row"=>"[num]"),
            L('挂单总量')=>array("row"=>"[num1]"),
            L('操作')=>array("row"=>array(array(&$this,"opreat"),"[num]","[tradeinfo]","[id]","[type]"))
        );
        $data = $list->getData();
        $this->assign('data',$data);
		$this->display();
	}
	public function stocktype($type)
	{
		if($type==1) return '卖出';
		if($type==2) return '买入';
	}
	function opreat($num,$tradeinfo,$id,$type){
		$cxstr="<a href='__URL__/stockcancel:__XPATH__/id/{$id}'>撤销</a>";
		$str="<a href='__URL__/tradedetail:__XPATH__/id/{$id}'>查看交易明细</a>";
		if($tradeinfo){	
			if($num==0){
				$cxstr="";
			}
		}else{
			$str="尚未进行交易";
		}
		if((!$fun_stock->getatt('stockBuycancel') && $type==2) || ($type==1 && !$fun_stock->getatt('stockSalecancel'))) $cxstr='';
		
		return $str."&nbsp;&nbsp;".$cxstr;
	}
	//撤销
	public function stockcancel()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
	     B('XSS');
	     M()->startTrans();
		$fun_stock=X(">");
		if(!isset($_REQUEST['id']) || $fun_stock==false){
			$this->error(L('参数错误'));
		}
		$mark_m=M($fun_stock->name."市场");
		$user_m=M($fun_stock->parent()->name);
		$markinfo=$mark_m->where(array('id'=>$_REQUEST['id']))->find();
		if(!$markinfo)$this->error(L('获取订单失败'));
		if($markinfo['state']==1 || $markinfo['num']==0) $this->error(L('订单状态错误'));
		if($markinfo['type']==2){
			$money=$markinfo['num']*$markinfo['price'];
			bankset($fun_stock->tradeBank,$markinfo['编号'],$money,L($fun_stock->name)."买入撤销","撤销挂单买入".L($fun_stock->name).L('剩余').$markinfo['num'].L('股,每股').$markinfo['price'].L('元'));
		}
		if($markinfo['type']==1){
			$num=$markinfo['num'];
    			$fun_stock->setrecord($markinfo['编号'],$markinfo['price'],$markinfo['num'],$markinfo['账户'],"撤销挂单卖出".$fun_stock->name.L('剩余').$markinfo['num'].L('股'),2);
		}
        $mark_m->where(array('id'=>$_REQUEST['id']))->save(array('state'=>1,'num'=>0));
        M()->commit();
		$this->success(L('完成'));

	}
	
	public function stock_change()
	{
		R("DmsAdmin://User/Index/header");
		$this->display();
	}
	//删除
	public function stockdelete()
	{
		$fun_stock=X(">");
		if(!isset($_REQUEST['id']) || $fun_stock==false){
			$this->error(L('参数错误'));
		}
		$mark_m=M($fun_stock->name."市场");
		$markinfo=$mark_m->where(array('id'=>$_REQUEST['id']))->find();
		if(!$markinfo)$this->error(L('获取订单失败'));
		//已成交过的无法删除
		if($markinfo['state']==0 && $markinfo['num']>0 ) $this->error(L('订单状态错误'));
		M()->startTrans();
		$rs=$mark_m->where(array('id'=>$_REQUEST['id']))->delete();
		if($rs){
			M()->commit();
			$this->success(L('完成'));
		}else{
			M()->rollback();
			$this->error(L('失败'));
			}
	}
	
	//托管账户出售
	public function stock_trustsell()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
	     B('XSS');
         list($fun_stock,$price,$num,$money,$user)=$this->sellbuydata();
		if(isset($_POST['input6'])&&$_POST['input6']!='')
		{

			if(mymd5($_POST['input6'],'EN') !== $user['pass2']){
				$this->error(L('二级密码错误'));
			}
		}
		else
		{
			$this->error(L('请输入交易密码'));
		}
		//判定余额
		if(!$fun_stock->judgeNum($num,$user,$fun_stock->name."托管")){
			  $this->error(L($fun_stock->name.'托管数量不足'));
		}
		//判断是否超出了限定额度
		$num1=$fun_stock->getTrustNum($user,$num);
		if($num1<$num || $num1==0){
			$this->error(L('该账户卖出额度不能超出本月的').$fun_stock->getatt("stockTrustMax")."%");
		}
		/*if($fun_stock->getatt("stockTrustMax") !=0 && $fun_stock->getatt("stockTrustMax") !=100){
		    $allsell=$user[$fun_stock->name."托管_本月累计"]+$num;
			$allnum=$user[$fun_stock->name."托管"]+$user[$fun_stock->name."托管_本月累计"];
			if($allsell>$allnum*$fun_stock->getatt("stockTrustMax")/100){

			}
		}*/
		//扣除交易货币流程
		M()->startTrans();
      $fun_stock-> setrecord($user['编号'],$price,$num1,$fun_stock->name.'托管','发布'.$fun_stock->name.'卖出订单'.'出售'.$num1.'股',1);
		//创建挂单记录
	    $fun_stock->setcompany($user['编号'],$price,$num1,$fun_stock->name.'托管',1);
	    M()->commit();
		$this->success(L('完成'));

	}

	//托管账户出售
	public function stock_zhuan()
	{
		//防XSS跨站攻击登入 调用ThinkPHP中的XSSBehavior
	     B('XSS');
        $num=$_REQUEST['num'];
		$user=$this->userinfo;
		$fun_stock=X(">");
		if(!$fun_stock->judgeNum($num,$user,$fun_stock->name)){
			  $this->error($fun_stock->name.L('数量不足'));
		}
		M()->startTrans();
		$fun_stock->setrecord($user['编号'],'',$num,L($fun_stock->name),L($fun_stock->name).L('转入').L($fun_stock->name).L('托管').$num.L('股'),1);
		$rs=M($fun_stock->parent()->name)->where("编号='".$user['编号']."'")->save(array($fun_stock->name."托管"=>$user[$fun_stock->name."托管"]+$num));
		if($rs){
			M()->commit();
			$this->success(L('完成'));
		}else{
			M()->rollback();
			$this->error(L('更新').L($fun_stock->name).L('托管信息失败'));
		}
	}

}
?>