<?php
	$cache=array();
class TestAction extends  Action{
	
	
	
	function index(){
		//$WshShell = new COM("WScript.Shell");
		//$oExec = $WshShell->Run("D://wamp/bin/php/php5.3.10/php.exe ".ROOT_PATH."/test.php '12222212'", 0, false); 
	//	echo 1;
		$cmd="/usr/bin/php ".ROOT_PATH."/test.php ";
	 	//pclose ( popen ( "start /B " .  $cmd ,  "r" )); 
		exec($cmd . " '333' > callog.txt &",$out,$re);
		print_r($out);
		print_r($re);
		//echo 2;
	}
	function index11(){
		echo 1;
		sleep(20);
		M('aa')->add(array('aa'=>1));

	}
	public function enc($text){
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);  
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);  
		$key = "This is a very secret key";//密钥
		  
		$crypttext =base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv));  
		return $crypttext;
	}
	public function denc($text){
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);  
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);  
		$key = "This is a very secret key";//密钥
		  
		$crypttext =mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key,base64_decode($text),MCRYPT_MODE_ECB,$iv);
		return $crypttext;
	}
	function index1(){
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);  
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);  
		$key = "This is a very secret key";//密钥  
		$crypttext ='PvQsmVJo6M9PSoY3f2lPhwesYASk2HoJz2DI5fAGCNqpaloiKL4t6lYGf+9dg19rrGtoN4pOdcKSsFDF9o67eQ==';
		echo mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key,base64_decode($crypttext),MCRYPT_MODE_ECB,$iv);//
	}
	public function gettag($html,$tagname)
	{
		preg_match("/$tagname\s*=\s*['\"]?([^\s\>'\"]+)['\"].*?/",$html,$out2);
		return $out2;
	}
	function index2()
	{
		M()->startTrans();
		$ret=M('银行卡')->lock(true)->find();
		$ret['时间'] += 1;
		M('银行卡')->save($ret);
		M()->commit();
		echo $_POST['name'];
	}
	public function dwz_index()
	{
		$this->display();
	}
	public function make()
	{
		$cons=json_decode($_POST['conjson'],true);
		$code='<'."?php\n";
		$code.='class '.$_POST['ActionName']."Action extends CommonAction\n";
		$code.='    public $_validate = $array('."\n";
		foreach($cons as $con)
		{
			if($con['require'])
			{
				$msg  = $con['require_msg'];
				$msg  = str_replace('[title]',$con['title'],$msg);
				$code.="    array('".$con['name']."','require','".$msg."',Model::MUST_VALIDATE,'regex',Model::MODEL_BOTH),\n";
			}
			if($con['number'])
			{
				$msg  = $con['number_msg'];
				$msg  = str_replace('[title]',$con['title'],$msg);
				$code.="    array('".$con['name']."','number','".$msg."',Model::MUST_VALIDATE,'regex',Model::MODEL_BOTH),\n";
			}
			if($con['email'])
			{
				$msg  = $con['email_msg'];
				$msg  = str_replace('[title]',$con['title'],$msg);
				$code.="    array('".$con['name']."','email','".$msg."',Model::MUST_VALIDATE,'regex',Model::MODEL_BOTH),\n";
			}
			if($con['strlen'])
			{
				$msg  = $con['strlen_msg'];
				$msg  = str_replace('[title]',$con['title'],$msg);
				$msg  = str_replace('[min]',$con['strlen_min'],$msg);
				$msg  = str_replace('[max]',$con['strlen_max'],$msg);
				$code.="    array('".$con['name']."','".$con['strlen_min'].",".$con['strlen_max']."','".$msg."',Model::MUST_VALIDATE,'length',Model::MODEL_BOTH),\n";
			}
		}
		
		$code.="        );\n";
		$code.='    function '.$_POST['indexName']."(){\n";
		$code.="        \$list=new TableListAction('".$_POST['tableName']."');\n";
		$code.="        \$button=array(\n";
		$code.='            "添加"=>array("class"=>"add","href"=>__URL__."/'.$_POST['addName'].'","target"=>"navTab","mask"=>"true"),'."\n";
		$code.='            "修改"=>array("class"=>"add","href"=>__URL__."/'.$_POST['editName'].'/id/{tl_id}","target"=>"navTab","mask"=>"true"),'."\n";
		$code.='            "删除"=>array("class"=>"add","href"=>__URL__."/'.$_POST['deleteName'].'/id/{tl_id}","target"=>"navTab","mask"=>"true","title"=>"确定要删除该数据吗？"),'."\n";
		$code.="         );\n";
        $code.='         $list->setButton = $button;'."\n";
		foreach($cons as $con)
		{
			if($con['list']!='')
			{
				if($con['title']=='')
				{
					$this->error($con['name'].'如需在列表显示,必须填写标题');
				}
				$code.='         $list->addshow("'.$con['title'].'",';
				//增加标题设定
				$code.='array("row"=>"['.$con['name'].']"';
				//增加搜索设定
				$code.=',"searchRow"=>"'.$con['name'].'"';
				//增加搜索类型
				if($con['list']=='txt')
				{
					$code.=',"searchMode"=>"text"';
				}
				if($con['list']=='date')
				{
					$code.=',"searchMode"=>"date"';
					$code.=',"format"=>"date"';
				}
				if($con['list']=='num')
				{
					$code.=',"searchMode"=>"num"';
				}
				//置顶搜索
				if($con['tops'])
				{
					$code.=',"searchPosition"=>"top"';
				}
				if($con['order'])
				{
					$code.=',"order"=>"['.$con['name'].']"';
				}
				if($con['sum'])
				{
					$code.=',"sum"=>"['.$con['name'].']"';
				}				
				$code.="));\n";
			}
		}
		$code.='         $list->getHtml();'."\n";
		$code.="    }\n";
		//生成插入页
		if($_POST['addName'] != '')
		{
			$code.="    public function ".$_POST['addName']."(){\n";
			$code.="        \$this->display();\n";
			$code.="    }\n";
		}
		if($_POST['addokName'] != '')
		{
			$code.="    public function ".$_POST['addokName']."(){\n";
			$code.="        \$data=array();\n";
			foreach($cons as $con)
			{
				$code.="        \$data['".$con['name']."']=\$_POST['".$con['name']."'];\n";
				
			}
			$code.="        \$m=M('".$_POST['tableName']."');\n";
			$code.="        \$m->setProperty('_validate',\$this->_validate);\n";
			$code.="        if(\$m->create()===false){\n";
			$code.="            \$this->error(\$m->getError());\n";
			$code.="        }\n";
			$code.='        $m->add($data);'."\n";
			$code.='        $this->success("添加成功");'."\n";
			$code.="    }\n";
		}
		//生成编辑页is_number
		if($_POST['editName'] != '')
		{
			$code.="    public function ".$_POST['editName']."(){\n";
			//参数错误判定
			$code.="        if(\!isset(\$_GET['id']) || !is_numeric(\$_GET['id'])){\n";
			$code.="            \$this->error('参数不正确或者您选择了多行记录')\n";
			$code.="            }\n";
			$code.="        \$data=M('".$_POST['tableName']."')->find(\$_GET['id']);\n";
			$code.="        if(!\$data){\n";
			$code.="            \$this->error('抱歉,您要编辑的记录未找到');\n";
			$code.="            }\n";
			$code.="        \$this->assign('formdata',\$data);\n";
			$code.="        \$this->display();\n";
			$code.="    }\n";
		}
		if($_POST['editokName'] != '')
		{
			$code.="    public function ".$_POST['editokName']."(){\n";
			$code.="        if(\!isset(\$_GET['id']) || !is_numeric(\$_GET['id'])){\n";
			$code.="            \$this->error('参数不正确或者您选择了多行记录')\n";
			$code.="            }\n";
			$code.="        \$data=M('".$_POST['tableName']."')->find(\$_GET['id']);\n";
			$code.="        if(!\$data){\n";
			$code.="            \$this->error('抱歉,您要编辑的记录未找到');\n";
			$code.="            }\n";
			$code.="        \$m=M('".$_POST['tableName']."');\n";
			$code.="        \$m->setProperty('_validate',\$this->_validate);\n";
			$code.="        if(\$m->create()===false){\n";
			$code.="            \$this->error(\$m->getError());\n";
			$code.="        }\n";
			$code.="        \$m->save(\$data);\n";
			$code.='        $this->success("编辑成功");'."\n";
			$code.="    }\n";
		}
		//生成删除页面
		if($_POST['deleteName'] != '')
		{
			$code.="    public function ".$_POST['deleteName']."(){\n";
			$code.="        if(\!isset(\$_GET['id'])){\n";
			$code.="            \$this->error('参数不正确')\n";
			$code.="            }\n";
			$code.="        \$m=M('".$_POST['tableName']."');\n";
			$code.="	    foreach(explode(',',\$_GET['id']) as \$id){";
			$code.="            \$m->delete(\$data);\n";
			$code.="            }\n";
			$code.='        $this->success("删除成功");'."\n";
			$code.="    }\n";
		}
		$code.="}\n";
		$code.="?".">";
		dump($code);
		$this->error('生成成功');
	}
}
?>