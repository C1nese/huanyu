<?php
// 本类由系统自动生成，仅供测试用途
defined('APP_NAME') || die('不要非法操作哦!');
class TransferAction extends CommonAction{
	//货币转账设置明细
	public function index()
	{
        $setButton=array(                 // 底部操作按钮显示定义
			'添加转账'    =>array("class"=>"add","href"=>"__URL__/add",'target'=>"dialog",'mask'=>"true",'title'=>"添加转账配置",'height'=>"550",'width'=>"520"),
			'修改转账'    =>array("class"=>"edit","href"=>"__URL__/edit/id/{tl_id}","target"=>"dialog",'title'=>'修改转账','mask'=>true,'height'=>"550",'width'=>"520"),
			'删除转账'    =>array("class"=>"delete","href"=>"__URL__/del/id/{tl_id}","target"=>"ajaxTodo","title"=>"确定要删除吗?"),
			'高级设置'    =>array("class"=>"addMore","href"=>"__URL__/givemoneyconfig",'target'=>"dialog",'mask'=>"true",'title'=>"转账总配置",'icon'=>'/Public/Images/ExtJSicons/cog.png'),
        );
        $list=new TableListAction("转账设置");
        $list->setButton = $setButton;// 定义按钮显示
        $list->order("time desc,id desc");
        $list->addshow("标题",array("row"=>"[title]"));
        //$list->addshow("转出货币",array("row"=>"[bank]","searchMode"=>"text",'searchGet'=>'bank',"excelMode"=>"text","searchPosition"=>"top"));   
		//$list->addshow("转入货币",array("row"=>"[tobank]","searchMode"=>"text",'searchGet'=>'tobank',"excelMode"=>"text","searchPosition"=>"top"));
		$list->addShow("转账货币",array("row"=>array(array($this,'transfer'),"[bank]","[tobank]")));
        $list->addShow("转账类型",array("row"=>array(array($this,'istome'),"[tome]","[toyou]")));
        $list->addshow("转账税",array("row"=>"[tax]","searchMode"=>"num",'searchRow'=>'tax'));
        $list->addshow("最大额",array("row"=>"[maxnum]","searchMode"=>"num",'searchRow'=>'maxnum'));
        $list->addshow("最小额",array("row"=>"[minnum]","searchMode"=>"text",'searchRow'=>'minnum'));
        $list->addshow("整数额",array("row"=>"[intnum]","searchMode"=>"text",'searchRow'=>'intnum'));
        $list->addshow("网络体系限定",array("row"=>array(array($this,'netview'),"[nets]")));
        $list->addShow("状态",array("row"=>array(array($this,'status'),"[status]")));
        $this->assign('list',$list->getHtml());
        $this->display();
	}
	//转账货币
	public function transfer($outbank,$tobank)
	{
		return $outbank."<span style='font-size:18px;color:#FF0000;'>→</span>".$tobank;
	}
	//网络体系限定显示
	public function netview($nets)
	{
		foreach(X('net_rec,net_place') as $net){
			if($nets == $net->name)
			{
				return $net->byname;
			}
			if($nets == $net->name.'上级')
			{
				return $net->byname.'上级';
			}
			if($nets == $net->name.'下级')
			{
				return $net->byname.'下级';
			}
		}	
		return '无';
	}
	//是否转给自己
    public function istome($me,$you)
    {
    	if($me && $you)
    	{
    		return "转给自己和其他用户";
    	}else{
    		if($me)
			{
				return '转给自己';
			}
			if($you)
			{
				return '转给其他用户';
			}
    	}
    }
    //判断转账状态是否开启
    public function status($str){
       if($str)
       {
       		return '开启';
       }else{
       		return '关闭';
       }
    }
    //添加转账功能
    public function add()
    {
    	$banks = X('fun_bank');
    	$banknames = array();
   		foreach($banks as $key=>$bank)
   		{
   			$banknames[$bank->name] = $bank->byname;
   		}
   		$netset="";
		foreach(X('net_rec,net_place') as $net){
			$netset[$net->name] = $net->byname;
			$netset[$net->name.'上级']=$net->byname.'上级';
			$netset[$net->name.'下级']=$net->byname.'下级';
		}
        $this->assign('netsets',$netset);
   		$this->assign('banknames',$banknames);
    	$this->display();
    }
    //提交转账设置
    public function addsave()
    {
    	//验证转账类型是否选择
    	if(!isset($_POST['toyou']) && !isset($_POST['tome']))
    	{
    		$this->error('请选择转账类型!');
    	}
    	//标题判断	
    	if(!isset($_POST['title']))
    	{
    		$this->error('前填写标题!');
    	}
    	//判断标题名字是否存在
    	$haves = M('转账设置')->where(array('title'=>$_POST['title']))->find();
    	if($haves)
    	{
    		$this->error('标题名已存在,请重新命名!');
    	}
    	M()->startTrans();
    	//组合转账类型数组
    	$data =array();
    	$verdata = array();
    	//标题
    	$data['title'] = $_POST['title'];
    	$verdata['title'] = $_POST['title'];
    	//转出货币
    	$data['bank'] = $_POST['outbank'];
    	$verdata['bank'] = $_POST['outbank'];
    	//转入货币
    	$data['tobank'] = $_POST['tobank'];
    	$verdata['tobank'] = $_POST['tobank'];
    	//转账给自己
    	if(isset($_POST['tome']))
    	{
    		$data['tome'] = 1;
    	}else{
    		$data['tome'] = 0;
    	}
    	//转账给其他人
    	if(isset($_POST['toyou']))
    	{
    		$data['toyou'] = 1;
    		$toyoutype="";
            if(isset($_POST['toyoutype']))
                $toyoutype=implode(',',$_POST['toyoutype']);
            $data['toyoutype']=$toyoutype;
    	}else{
    		$data['toyou'] = 0;
    		$data['toyoutype']="";
    	}
    	//转账手续费
    	$data['taxfrom'] = $_POST['taxfrom'];
    	$data['tax'] = $_POST['tax'];
    	$verdata['tax'] = $_POST['tax'];
    	//转账手续费上限
    	$data['taxtop'] = $_POST['taxtop'];
    	$verdata['taxtop'] = $_POST['taxtop'];
    	//转账手续费下限
    	$data['taxlow'] = $_POST['taxlow'];
    	$verdata['taxlow'] = $_POST['taxlow'];
    	//转账最大金额
    	$data['sacl'] = $_POST['sacl'];
    	$verdata['sacl'] = $_POST['sacl'];
    	//转账最大金额
    	$data['maxnum'] = $_POST['max'];
    	$verdata['maxnum'] = $_POST['max'];
    	//转账最小金额
    	$data['minnum'] = $_POST['min'];
    	$verdata['minnum'] = $_POST['min'];
    	//转账最整数倍额
    	$data['intnum'] = $_POST['intnum'];
    	$verdata['intnum'] = $_POST['intnum'];
    	//转账的网体
    	$data['nets'] = $_POST['nets'];
    	$verdata['nets'] = $_POST['nets'];
    	//服务中心限定
    	$data['shop'] = $_POST['shop'];
    	$verdata['shop'] = $_POST['shop'];
    	//对提交数据验证是否已经存在转账类型
    	$num1 = M('转账设置')->where($data)->count();
		if($num1>0)
		{
			$this->error('转账类型已存在!');
		}
    	//验证转账是否存在
    	if(isset($_POST['tome']) && isset($_POST['toyou']))
    	{
    		$verdata['tome'] = 0;
    		$verdata['toyou'] = 1;
    		$num2 = M('转账设置')->where($verdata)->count();
    		$verdata['tome'] = 1;
    		$verdata['toyou'] = 0;
    		$num3 = M('转账设置')->where($verdata)->count();
    		if($num2>0 || $num3>0)
    		{
    			$this->error('转账类型已存在!');
    		}
    	}
    	//验证转账是否存在
		$verdata['toyou'] = 1;
		$verdata['tome'] = 1;
		$num4 = M('转账设置')->where($verdata)->count();
		if($num4>0)
		{
			$this->error('转账类型已存在!');
		}
    	//状态
    	$data['status'] = $_POST['status'];
    	//更新时间
    	$data['time'] = systemTime();
    	//数据添加
   		$result = M('转账设置')->add($data);
   		//开启转账
        CONFIG('giveMoney',1);
   		if($result)
   		{
   			M()->commit();
   			$this->success('转账设置成功!');
   		}else{
   			$this->error('转账设置失败!');
   		}
    }
    //执行删除操作
    public function del()
    {
    	$model=M('转账设置');
    	$succNum = 0;
		$errNum = 0;
		if(isset($_GET['id']))
		foreach(explode(',',$_GET['id']) as $id){
			if($id == '') continue;
			$where['id'] = $id;
			M()->startTrans();
			if($model->where($where)->delete()){
				$succNum++;
				M()->commit();
			}else{
				$errNum++;
				M()->rollback();
			}
		}
		if($errNum !=0){
			$this->error("删除成功：".$succNum .'条记录；删除失败：'.$errNum .'条记录；');
		}else{
			$this->success("删除成功：".$succNum .'条记录；');
		}
    }
    //修改操作
    public function edit()
    {
    	if(strpos($_GET['id'],',')!== false){
			$this->error('参数错误!');
		}
		//查询要修改的数据
		$bankdata = M('转账设置')->where(array('id'=>$_GET['id']))->find();
		$bankdata['bynets']="";
		if($bankdata)
		{
			$netset="";
			foreach(X('net_rec,net_place') as $net){
				$netset[$net->name] = $net->byname;
				$netset[$net->name.'上级']=$net->byname.'上级';
				$netset[$net->name.'下级']=$net->byname.'下级';
				if($bankdata['nets'] == $net->name)
				{
					$bankdata['bynets'] =  $net->byname;
				}
				if($bankdata['nets'] == $net->name.'上级')
				{
					$bankdata['bynets'] =  $net->byname.'上级';
				}
				if($bankdata['nets'] == $net->name.'下级')
				{
					$bankdata['bynets'] = $net->byname.'下级';
				}
			}
	        $this->assign('netsets',$netset);
			$this->assign('bankdata',$bankdata);
			$toyoutype=isset($bankdata['toyoutype'])?explode(',',$bankdata['toyoutype']):array();
            $this->assign('toyoutype',$toyoutype);
			$this->display();
		}else{
			$this->error('信息有误!');	
		}
    }
     //提交转账设置
    public function editSave()
    {
    	if(!isset($_POST['toyou']) && !isset($_POST['tome']))
    	{
    		$this->error('请选择转账类型!');
    	}
    	M()->startTrans();
    	//组合转账类型数组
    	$data =array();
    	$verdata = array();
    	//转账给自己
    	if(isset($_POST['tome']))
    	{
    		$data['tome'] = 1;
    	}else{
    		$data['tome'] = 0;
    	}
    	//转账给其他人
    	if(isset($_POST['toyou']))
    	{
    		$data['toyou'] = 1;
    		$toyoutype="";
            if(isset($_POST['toyoutype']))
                $toyoutype=implode(',',$_POST['toyoutype']);
            $data['toyoutype']=$toyoutype;
    	}else{
    		$data['toyou'] = 0;
    		$data['toyoutype']="";
    	}
    	$data['taxfrom'] = $_POST['taxfrom'];
    	//转账手续费
    	$data['tax'] = $_POST['tax'];
    	//转账最大金额
    	$data['maxnum'] = $_POST['max'];
    	//转账最小金额
    	$data['minnum'] = $_POST['min'];
    	//转账最整数倍额
    	$data['intnum'] = $_POST['intnum'];
    	//转账的网体
    	$data['nets'] = $_POST['nets'];
    	//服务中心限定
    	$data['shop'] = $_POST['shop'];
    	//获取修改的数据
    	$olddata =  M('转账设置')->where(array('id'=>$_POST['id']))->find();
    	$verdata = $data;
    	$verdata['title'] = $olddata['title'];
    	$verdata['bank'] = $olddata['bank'];
    	$verdata['tobank'] = $olddata['tobank'];
    	$verdata['id'] = array('neq',$olddata['id']);
    	//对提交数据验证是否已经存在转账类型
    	$num1 = M('转账设置')->where($verdata)->count();
		if($num1>0)
		{
			$this->error('转账类型已存在!');
		}
    	//验证转账是否存在
    	if(isset($_POST['tome']) && isset($_POST['toyou']))
    	{
    		$verdata['tome'] = 0;
    		$verdata['toyou'] = 1;
    		$num2 = M('转账设置')->where($verdata)->count();
    		$verdata['tome'] = 1;
    		$verdata['toyou'] = 0;
    		$num3 = M('转账设置')->where($verdata)->count();
    		if($num2>0 || $num3>0)
    		{
    			$this->error('转账类型已存在!');
    		}
    	}
    	//验证转账是否存在
		$verdata['toyou'] = 1;
		$verdata['tome'] = 1;
		$num4 = M('转账设置')->where($verdata)->count();
		if($num4>0)
		{
			$this->error('转账类型已存在!');
		}
    	//状态
    	$data['status'] = $_POST['status'];
    	//更新时间
    	$data['time'] = systemTime();
    	//数据添加
   		$result = M('转账设置')->where(array('id'=>$_POST['id']))->save($data);
   		if($result)
   		{
   			M()->commit();
   			$this->success('转账修改成功!');
   		}else{
   			$this->error('转账修改失败!');
   		}
    }
    public function givemoneyconfig(){
    	$this->assign('pwd3Switch',adminshow('pwd3Switch'));
		$this->assign('giveMoney',CONFIG('giveMoney'));
		$this->assign('sureGiveMoney',CONFIG('sureGiveMoney'));
		$this->assign('giveMoneyPass2',CONFIG('giveMoneyPass2'));
		$this->assign('giveMoneyPass3',CONFIG('giveMoneyPass3'));
		$this->assign('giveMoneySmsSwitch',CONFIG('giveMoneySmsSwitch'));
		$this->assign('giveMoneySmsContent',CONFIG('giveMoneySmsContent'));
		$this->display();
    }
    //系统设置更新
    public function gmconfigsave()
	{
		$data=array();
	
		$giveMoney  		   = isset($_POST['giveMoney'])?$_POST['giveMoney']:0;
		$sureGiveMoney  	   = isset($_POST['sureGiveMoney'])?$_POST['sureGiveMoney']:0;
		$giveMoneyPass2        = isset($_POST['giveMoneyPass2'])?$_POST['giveMoneyPass2']:0;
		$giveMoneyPass3        = isset($_POST['giveMoneyPass3'])?$_POST['giveMoneyPass3']:0;
		$giveMoneySmsSwitch    = isset($_POST['giveMoneySmsSwitch'])?$_POST['giveMoneySmsSwitch']:0;
		$giveMoneySmsContent   = isset($_POST['giveMoneySmsContent'])?$_POST['giveMoneySmsContent']:'';
		M()->startTrans();
		CONFIG('giveMoney',$giveMoney);
		CONFIG('sureGiveMoney',$sureGiveMoney);
		CONFIG('giveMoneyPass2',$giveMoneyPass2);
		CONFIG('giveMoneyPass3',$giveMoneyPass3);
		CONFIG('giveMoneySmsSwitch',$giveMoneySmsSwitch);
		CONFIG('giveMoneySmsContent',$giveMoneySmsContent);
		M()->commit();
		$this->saveAdminLog('',$_POST,"转账设置","转账参数设置");
        $this->success('修改完成',__URL__.'/givemoneyconfig');
	}
}
?>