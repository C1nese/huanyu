<?php
defined('APP_NAME') || die('不要非法操作哦!');
// 本类由系统自动生成，仅供测试用途
class ReportAction extends CommonAction {
    public function finance(){
    	$banks = X('fun_bank');
    	$residueSum=array();
    	foreach($banks as $bank)
    	{
    		//得到货币剩余
    		$residue=M('用户')->sum($bank->name)
    		$title=$residue;
    		if($residue>100000)
    		{
    			
    		}
    		$residueSum[] = array('title'=> .' - '. $bank->name,'residue'=>$residue);
    		
    		
    		
    		//$residue =M('用户')->sum($bank->name);
    		$IncreaseSum = M()->query('select 类型,sum(金额) from dms_'.$bank->name.'明细 where 金额>0 group by 类型');
    		$ReduceSum   = M()->query('select 类型,sum(金额) from dms_'.$bank->name.'明细 where 金额<0 group by 类型');
    	}
        //$list->addshow("状态",array("row"=>array(array(&$this,"getStatus"),"[状态]"),'css'=>'width:10%'));
        //$list->addshow("操作",array("row"=>array(array(&$this,"getDofun"),"[状态]",'[id]','[发件人类型]'),'css'=>'width:10%',"excel"=>false)); 
        $this->assign('residueSum',$residueSum);
    	$this->display();
    }
}
?>