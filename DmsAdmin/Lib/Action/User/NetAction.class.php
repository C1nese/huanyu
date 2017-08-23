<?php
// 网络图
defined('APP_NAME') || die('不要非法操作哦');

class NetAction extends CommonAction
{
    /*
    * 网络图显示
    */
    public function disp($netNode)
    {
        //级别数组
        $levelsArr = array();
        foreach (X('levels') as $level) {
            $levelsArr[$level->name] = array();
            $cons = $level->getcon("con", array("name" => '', 'lv' => ''));
            foreach ($cons as $con) {
                $levelsArr[$level->name][$con['lv']] = $con['name'];
            }
        }
        $netPlaceName = array();
        foreach (X('net_place') as $netPlace) {
            $regions = $netPlace->getcon("region", array('name' => ''));
            foreach ($regions as $region) {
                $netPlaceName[$netPlace->name][] = $region['name'];
            }
        }

        //增加注册用args
        foreach (X('sale_reg') as $sale) {
            if ($sale->user != 'admin' && $sale->use) {
                $this->assign('regXpath', $sale->objPath());
            }
        }

        //谁可以报单
        $bdreg = true;
        if ($this->userobj->shopWhere != '' && CONFIG('USER_SHOP_SALEONLY') && transforms(X('user')->shopWhere, $this->userinfo)) {
            $bdreg = false;
        }
        $this->assign('bdreg', $bdreg);
        $treenumArr = array();
        foreach (X('fun_treenum') as $treenum) {
            if ($treenum->netName == $netNode->name && in_array($treenum->name, $netNode->treeDisp)) {
                $treenumArr[$treenum->netName] = $treenum->name;
            }
        }
        $this->assign('treenumArr', $treenumArr);
        $this->assign('levelsArr', $levelsArr);
        $this->assign('netPlaceName', $netPlaceName);
        $style = isset($_REQUEST['nettype']) ? $_REQUEST['nettype'] : (!$netNode->userauto ? 'dir' : '');
        if ($style == 'dir') {    //目录树
            $this->assign('style', 'dir');
            $this->showDirTree($netNode, $netPlaceName, $levelsArr);
        } else {    //分支图
            $this->assign('style', 'ramus');
            if (get_class($netNode) == 'net_place') {    //安置关系
                $this->showPositionRamusTree($netNode, $netPlaceName, $levelsArr);
            } else {        //推荐关系
                $this->showRamusTree($netNode, $netPlaceName, $levelsArr);
            }
        }
    }

    public function lineList()
    {
        //级别数组
        $levelsArr = array();
        foreach (X('levels') as $level) {
            $levelsArr[$level->name] = array();
            $cons = $level->getcon("con", array("name" => '', 'lv' => ''));
            foreach ($cons as $con) {
                $levelsArr[$level->name][$con['lv']] = $con['name'];
            }
        }
        $this->assign('levelsArr', $levelsArr);
        $this->showLineTree($levelsArr);

    }

    //  推荐列表
//    function listDisp($rec){
//        if(!$rec->userListDisp){
//            $this->error($rec->byname."列表未开启");
//        }
//		if(isset($_GET['userid']) && $_GET['userid']){
//		$userid=$_GET['userid'];
//		}else{
//		$userid=$this->userinfo["编号"];
//		}
//        $list = new TableListAction('用户');
//        $list ->where(array($rec->name.'_上级编号'=>$userid))->order("id desc");
//        $list ->setShow = array(
//            L('编号') => array("row"=>'<a href="'.__URL__.'/listDisp:__XPATH__/userid/[编号]/style/lists">[编号]</a>'),
//			L('姓名')=> array("row"=>"[姓名]"),
//            L('注册日期') => array("row"=>"[注册日期]","format"=>"time"),
//			L('状态') => array("row"=>"[状态]"),
//        );
//        $data = $list ->getData();
//        dump($data);die;
//        $this->assign("name",$rec->byname);
//        $this->assign("data",$data);
//        $this->display();
//    }

    function listDisp($rec)
    {
        if (!$rec->userListDisp) {
            $this->error($rec->byname . "列表未开启");
        }
        $this->assign('rec_tagname', $rec->xml->tagName);
        if ((isset($_GET['userid']) && $_GET['userid'])) {
            $userid = $_GET['userid'];
        } else if ((isset($_POST['userid']) && $_POST['userid'])) {
            $userid = $_POST['userid'];
        } else {
            $userid = $this->userinfo["编号"];
        }

        $user_model = M('用户');
        $current_info = $user_model->where('`编号` = "' . $userid . '"')->find();
		if(empty($current_info)) $this->error('用户不存在！');
        /*btx 判断是否为本网络下编号 2017/04/06 start*/
        $strlen = strlen($this->userinfo[$rec->name . '_网体数据']);
        $str = substr($current_info[$rec->name . '_网体数据'],0,$strlen);
        if($str != $this->userinfo[$rec->name . '_网体数据']){
            $current_info = $this->userinfo;
            $userid = $this->userinfo["编号"];
        }
        /*btx 判断是否为本网络下编号 2017/04/06 end*/

        /*btx 显示层数 2017/04/06 start*/
        if($this->userinfo['商务中心级别'] < 10){
            $userLookLayer = $rec->userLookLayer - 1;
            if($current_info[$rec->name.'_层数'] - $this->userinfo[$rec->name.'_层数'] > $userLookLayer && $userLookLayer != -1){
                $current_info = $this->userinfo;
                $userid = $this->userinfo["编号"];
            }
        }
        /*btx 显示层数 2017/04/06 end*/

        $net_info = strstr($current_info[$rec->name . '_网体数据'], ',') ? explode(',', $current_info[$rec->name . '_网体数据']) : $current_info[$rec->name . '_网体数据'];

        if (is_array($net_info)) {
            foreach ($net_info as $key => $val) {
                if (strstr($val, '-')) {
                    $net_top .= strstr($val, '-', true) . ',';
                } else {
                    $net_top .= $val . ',';
                }
            }
            $net_top = trim($net_top, ',');
        } else {
            if (strstr($net_top, '-')) {
                $net_top = strstr($net_info, '-', true);
            } else {
                $net_top = $net_info;
            }

        }

        $net_top = strstr($net_top, $this->userinfo["id"]);

        if ($net_top) {
            $net_tops = $user_model->where(array('id' => array('in', $net_top)))->field('编号')->order('id asc')->select();

            foreach ($net_tops as $top => $tops) {
                $net_nowtit .= "<a href='__URL__/listDisp:__XPATH__/userid/" . $tops['编号'] . "'>" . $tops['编号'] . "</a> >> ";
            }
            $net_nowtit .= "<a href='__URL__/listDisp:__XPATH__/userid/" . $current_info['编号'] . "'>" . $current_info['编号'] . "</a> >>";
        } else {
            $net_nowtit .= "<a href='__URL__/listDisp:__XPATH__/userid/" . $this->userinfo["编号"] . "'>" . $this->userinfo["编号"] . "</a> >>";
        }


        $user_w = $user_model->where('`管理_上级编号` = "' . $current_info['编号'] . '"')->field('管理_位置')->select();
        $user_we = array();
        if (is_array($user_w)) {
            foreach ($user_w as $ks => $vs) {
                $user_we[] = $vs['管理_位置'];
            }
        }
        $current_info['user_we'] = $user_we;

        /*btx 姓名处理显示 2017/04/06 start*/
        $current_info['姓名'] = "**" . mb_substr($current_info['姓名'], -1, 1, "utf-8");
        /*btx 姓名处理显示 2017/04/06 end*/

        $data['list'] = M('用户')->where(array($rec->name.'_上级编号' => $userid))->order("管理_位置 asc")->select();
        foreach ($data['list'] as $k => $v) {
            $wei = $user_model->where('`' . $rec->name . '_上级编号` = "' . $v['编号'] . '"')->field($rec->name . '_位置')->select();
            $www = array();
            if (is_array($wei)) {
                foreach ($wei as $kk => $vv) {
                    $www[] = $vv[$rec->name . '_位置'];
                }
            }
            $data['list'][$k]['num'] = $www;
            unset($www);
            $data['list'][$k]['姓名'] = "**" . mb_substr($v['姓名'], -1, 1, "utf-8");
        }

        //增加注册用args
        foreach (X('sale_reg') as $sale) {
            if ($sale->user != 'admin' && $sale->use) {
                $this->assign('regXpath', $sale->objPath());
            }
        }

        //谁可以报单
        $bdreg = true;
        if ($this->userobj->shopWhere != '' && CONFIG('USER_SHOP_SALEONLY') && transforms(X('user')->shopWhere, $this->userinfo)) {
            $bdreg = false;
        }
        //级别
        $levelsArr = array();
        foreach(X('levels') as $level){
            $levelsArr[$level->name] = array();
            $cons=$level->getcon("con",array("name"=>'','lv'=>''));
            foreach($cons as $con){
                $levelsArr[$level->name][$con['lv']] = $con['name'];
            }
        }
        $this->assign('levelsArr',$levelsArr['用户级别']);
        $this->assign('bdreg', $bdreg);
        $this->assign("userno", $this->userinfo['编号']);
        $this->assign("name", $rec->name);
        $this->assign("net_nowtit", trim($net_nowtit, '>>'));
        $this->assign("current_info", $current_info);
        $this->assign("data", $data);
        $this->display();
    }

    //  推荐列表
    function listDisps_down(net_rec $net_rec)
    {

        $first_userid = $_REQUEST['first_userid'];
        if (empty($first_userid)) $first_userid = $this->userinfo['编号'];
        if (!$net_rec->userListDisp) {
            $this->error($net_rec->byname . "列表未开启");
        }
        $userLevelArray = array();

        foreach (X('levels') as $levels) {
            foreach ($levels->getcon("con", array("name" => "", "lv" => "")) as $lvconf) {
                $_temp[$lvconf['lv']] = L($lvconf['name']);
            }
            $userLevelArray = $_temp;
        }

        $this->userLevelArray = $userLevelArray;
        $list = new TableListAction('用户');
        $list->where(array($net_rec->name . '_上级编号' => $first_userid))->order("id desc");
        $list->setShow = array(
            L('编号') => array("row" => "<a href='__URL__/listDisps_down:__XPATH__/first_userid/[编号]'>[编号]</a>"),
            L('姓名') => array("row" => "[姓名]"),
            L('注册日期') => array("row" => "[注册日期]", "format" => "time"),
            L('状态') => array("row" => "[状态]"),
            L('用户级别') => array("row" => array(array(&$this, "printUserLevel"), "[用户级别]")),

        );
        $data = $list->getData();
        //dump($data);
        $this->assign("name", $net_rec->byname);
        $this->assign("data", $data);
        $this->assign('recommend_id', $first_userid);
        $this->display();
    }


    /*
    * 获取用户的子节点
    */
    public function getChild($netNode)
    {
        $uid = $_REQUEST['id'];
        $userNode = $netNode->parent();
        $netName = $netNode->name;            //网体名称
        $userLookLayer = $netNode->userLookLayer; // 查看深度

        //用户级别数组
        $userLevelArray = array();

        foreach (X('levels') as $levels) {
            $_temp = array();
            foreach ($levels->getcon("con", array("name" => "", "lv" => "")) as $lvconf) {
                $_temp[$lvconf['lv']] = $lvconf['name'];
            }
            $userLevelArray[$levels->name] = $_temp;
        }

        $this->userLevelArray = $userLevelArray;

        //获取下级
        $userModel = M('用户');
        $recommend_list = $userModel->where(array($netName . "_上级编号" => $uid))->select();
        $c = array();
        $recommend_total = count($recommend_list) - 1;
        $jsstr = "";
        foreach ($recommend_list as $i => $recommend) {
            if (($recommend[$netName . '_层数'] - $this->userinfo[$netName . '_层数']) > $userLookLayer && $userLookLayer > 0) {
                $jsstr .= "{ id:'0', pId:'', name:'',  open:false,isParent:false}";
                break;
            }
            if ($jsstr != '') {
                $jsstr .= ',';
            }
            $name = $recommend['编号'];
            foreach (X('levels') as $level) {
                $name .= '[' . $this->print_user_level($recommend[$level->name], $level->name) . ']';
            }


            if ($recommend["审核日期"]) {
                $name .= '[审核日期:' . date("Y-m-d", $recommend["审核日期"]) . ']';
            } else {
                $name .= '[注册日期:' . date("Y-m-d", $recommend["注册日期"]) . ']';
            }
            $isParent = 'true';
            $down = $userModel->where(array($netName . "_上级编号" => $recommend['编号']))->field('id')->find();
            if (!$down) $isParent = 'false';
            $jsstr .= "{ id:'{$recommend['编号']}', pId:'" . $uid . "', name:'" . $name . "',  open:true,isParent:" . $isParent . "}";

        }
        if ($jsstr != '') {
            echo "[" . $jsstr . "]";
        }

    }


    /************************   net_place2网络图   *********************/
    public function place2(net_place2 $net)
    {
        //获得net_place2的节点的区域
        $model = M($net->name, 'dms_');
        if ($_REQUEST['tid'] == 'go') {
            $userz = $model->where(array('编号' => $_REQUEST['uid']))->find();
            if (!$userz) {
                $this->error('用户不存在');
            }
            if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                $upuser = $model->where(array('编号' => $this->userinfo['编号']))->find();
                $where1 = '';
                foreach ($net->getcon("region", array("name" => "")) as $key => $val) {
                    if ($key == 0) {
                        $where1 = "find_in_set('{$upuser['id']}-{$val['name']}',网体数据)";
                    } else {
                        $where1 .= " or find_in_set('{$upuser['id']}-{$val['name']}',网体数据)";
                    }
                }
                $firstUser = $model->where("id='" . $userz['id'] . "' and (" . $where1 . ")")->find();
                if (!$firstUser) {
                    $this->error(L('该用户不在公排网体下!'));
                }
            }
            $_REQUEST['uid'] = $userz['id'];
        }

        if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
            $upnode = $model->where(array('id' => $_REQUEST['uid']))->find();
        } else {
            $upnode = $model->where(array('编号' => $this->userinfo['编号']))->find();
        }

        if (!$upnode) {
            $upnode = $model->where(array('层数' => 1))->find();
        }
        if (!$upnode) {
            die('未找到用户');
        }
        $result = array();
        $upUserLayer = $upnode['层数'];
        for ($i = 0; $i < $net->adminNetLayer - 1; $i++) {
            $downUsers = $net->getdown($upnode, $i + 1, $i + 1);
            if (!$downUsers) {
                break;
            }
            $result[$i] = $downUsers;

            //	$result[$i] = $downUsers;
        }
        $this->assign('thisUser', $this->userinfo);
        $this->assign('showLayer', $net->adminNetLayer);
        $this->assign('firstUserInfo', $upnode);
        //$this->assign('userNode',$userNode);
        $this->assign('netNode', $net);
        $this->assign('netName', $net->name);
        $this->assign('netTree', $result);
        $this->display('place2');
    }

    /*
    * 显示 网络目录树
    */
    private function showDirTree($netNode, $netPlaceName, $levelsArr)
    {

        $userModel = M('用户');
        $userNode = X('user');
        $userLookLayer = $netNode->userLookLayer; // 查看深度
        $netName = $netNode->name;
        $thisuser = $this->userinfo;

        //获取树
        if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
            if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                $this->error('非法表单数据');
            }
            if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                $firstUser = $userModel->where(array("编号" => trim($_REQUEST['uid'])))->find();
                if ($firstUser) {
                    $firstUserNetInfos = explode(',', $firstUser[$netName . '_网体数据']);
                    $firstUserNetArray = array();
                    foreach ($firstUserNetInfos as $firstUserNetInfo) {
                        $firstUserNetInfoArr = explode('-', $firstUserNetInfo);
                        $firstUserNetArray[] = $firstUserNetInfoArr[0];
                    }

                    if (!in_array($this->userinfo['id'], $firstUserNetArray)) {
                        $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                    }
                    //超出深度
                    if ($userLookLayer > 0 && ($firstUser[$netName . '_层数'] - $this->userinfo[$netName . '_层数']) > $userLookLayer) {
                        $this->error("只允许查看" . $userLookLayer . "层内的用户");
                    }
                } else {
                    $this->error(L('该' . $this->userobj->byname . '不存在!'));
                }
                $firstUserInfo = $firstUser;
            }
        } else {
            $result = M('用户')->where(array("编号" => $_SESSION[C('USER_AUTH_NUM')]))->select();
            $firstUserInfo = $result[0];
        }
        if (!$firstUserInfo) {
            echo "未找到对应{$this->userobj->byname}.<a href='javascript:navTab.reload();'>返回</a>";
            exit;
        }

        $firstUserid = $firstUserInfo['编号'];
        $downUsers = M('用户')->where(array($netName . '_上级编号' => $firstUserid))->select();
        if ($downUsers)
            foreach ($downUsers as $key => $downUser) {
                //检查是否存在下级
                $info = $userModel->where("{$netName}_上级编号='{$downUser['编号']}'")->find();
                if ($info) {
                    $downUsers[$key]['haveChild'] = true;
                } else {
                    $downUsers[$key]['haveChild'] = false;
                }
                //$downUsers[$key]['floatStr'] = $this->getFloatJson($userNode,$downUsers[$key],$levelsArr);
            }

        //$firstUserInfo['floatStr'] = $this->getFloatJson($userNode,$firstUserInfo,$levelsArr);

        $this->assign('firstUserInfo', $firstUserInfo);
        $this->assign('downUsers', $downUsers);
        $this->assign('userNode', $this->userobj);
        $this->assign('netNode', $netNode);
        $this->display('dir_tree');

    }


    /*
    * 显示 树状 网络分支图(安置关系)
    *
    * $userType		 : 当前用户节点的数据名
    * $netName		 : 网络名称
    * $userNode		 : 当前用户节点
    * $position_list : 区位列表
    */
    private function showPositionRamusTree($netNode, $netPlaceName, $levelsArr)
    {
        //判断是否是ion模版
        if (CONFIG('DEFAULT_THEME') == 'ion') {
            //判断如果  开启只有报单中心才能看网络图地
            if ($netNode->showbd == 1 and $this->userinfo['服务中心'] == 0) {
                $showLayer = $netNode->showceng; //显示几层
                //$this->assign('showstate',1);
            } else {
                $showLayer = $netNode->userNetLayer; //显示几层
            }
            $userLookLayer = $netNode->userLookLayer; // 查看深度
            $userModel = M('用户');
            $netName = $netNode->name;

            //判断是否在自己网体下,不在自己网体下不能查看
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                    $this->error('非法表单数据');
                }
                if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                    $firstUser = $userModel->where(array("编号" => trim($_REQUEST['uid'])))->find();
                    if ($firstUser) {
                        $firstUserNetInfos = explode(',', $firstUser[$netName . '_网体数据']);
                        $firstUserNetArray = array();
                        foreach ($firstUserNetInfos as $firstUserNetInfo) {
                            $firstUserNetInfoArr = explode('-', $firstUserNetInfo);
                            $firstUserNetArray[] = $firstUserNetInfoArr[0];
                        }

                        if (!in_array($this->userinfo['id'], $firstUserNetArray)) {
                            $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                        }
                    } else {
                        $this->error(L('该' . $this->userobj->byname . '不存在!'));
                    }
                }

            }
            $levelStr = '';
            foreach ($levelsArr as $levelName => $level) {
                $levelStr .= ',a.' . $levelName;
            }

            //获取树
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                //需要加层数>0的条件
                $where = array(
                    '编号' => array(eq, $_REQUEST['uid']),
                    $netName . '_层数' => array(gt, '0')
                );
                $result = M('用户')->where($where)->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置","a.编号='".trim($_REQUEST['uid'])."' and a.{$netName}_层数>0");
            } else {
                $where = array(
                    '编号' => array(eq, $this->userinfo['编号']),
                    $netName . '_层数' => array(gt, '0')
                );
                $result = M('用户')->where($where)->select();
                //$result = M('用户')->where(array('编号'=>$this->userinfo['编号']))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置","a.编号='{$this->userinfo['编号']}' and a.{$netName}_层数>0");
            }
            $firstUserInfo = $result[0];
            $result = array();
            if ($firstUserInfo) {

                $upid = $firstUserInfo['id'];
                $upUserLayer = $firstUserInfo[$netName . '_层数'];

                // 循环获得下面几层数据
                for ($i = 0; $i < $showLayer - 1; $i++) {
                    $where = '' . $netName . '_层数=' . ($upUserLayer + $i + 1) . ' and (';
                    foreach ($netPlaceName[$netName] as $region) {
                        $where .= " find_in_set('{$upid}-{$region}',{$netName}_网体数据) or";
                    }

                    $where = trim($where, 'or') . ')';
                    //$downUsers = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置",$where);
                    $downUsers = M('用户')->where($where)->select();
                    if (!$downUsers || ($userLookLayer != 0 && ($upUserLayer + $i + 1 - $this->userinfo[$netName . '_层数']) >= $userLookLayer)) {
                        break;
                    }
                    $result[$i] = $downUsers;
                }
            }
            $this->assign('thisUser', $this->userinfo);
            $this->assign('showLayer', $showLayer);
            $this->assign('firstUserInfo', $firstUserInfo);
            $this->assign('userNode', $this->userobj);
            $this->assign('netNode', $netNode);
            $this->assign('netName', $netName);
            $this->assign('netTree', $result);
            $this->display('net_place_tree2');
        } else {
            $showLayer = $netNode->userNetLayer; //显示几层
            $userLookLayer = $netNode->userLookLayer; // 查看深度
            $userModel = M('用户');
            $netName = $netNode->name;
            //店铺查看深度替换
            if (X('user')->shopWhere != '' && transforms(X('user')->shopWhere, $this->userinfo)) {
                $showLayer = $netNode->shopNetLayer;
                $userLookLayer = $netNode->shopLookLayer;
            }
            //判断是否在自己网体下,不在自己网体下不能查看
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                    $this->error('非法表单数据');
                }
                if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                    $firstUser = $userModel->where(array("编号" => trim($_REQUEST['uid'])))->find();
                    if ($firstUser) {
                        $firstUserNetInfos = explode(',', $firstUser[$netName . '_网体数据']);
                        $firstUserNetArray = array();
                        foreach ($firstUserNetInfos as $firstUserNetInfo) {
                            $firstUserNetInfoArr = explode('-', $firstUserNetInfo);
                            $firstUserNetArray[] = $firstUserNetInfoArr[0];
                        }

                        if (!in_array($this->userinfo['id'], $firstUserNetArray)) {
                            $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                        }
                        //超出深度
                        if ($userLookLayer > 0 && ($firstUser[$netName . '_层数'] - $this->userinfo[$netName . '_层数']) > $userLookLayer) {
                            $this->error("只允许查看" . $userLookLayer . "层内的用户");
                        }
                    } else {
                        $this->error(L('该' . $this->userobj->byname . '不存在!'));
                    }
                }

            }
            $levelStr = '';
            foreach ($levelsArr as $levelName => $level) {
                $levelStr .= ',a.' . $levelName;
            }
            //获取树
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                //需要加层数>0的条件
                $where = array(
                    '编号' => array('eq', $_REQUEST['uid']),
                    $netName . '_层数' => array('gt', '0')
                );
                $result = M('用户')->where($where)->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置","a.编号='".trim($_REQUEST['uid'])."' and a.{$netName}_层数>0");
            } else {
                $where = array(
                    '编号' => array('eq', $this->userinfo['编号']),
                    $netName . '_层数' => array('gt', '0')
                );
                $result = M('用户')->where($where)->select();
                //$result = M('用户')->where(array('编号'=>$this->userinfo['编号']))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置","a.编号='{$this->userinfo['编号']}' and a.{$netName}_层数>0");
            }
            $firstUserInfo = $result[0];
            $result = array();
            if ($firstUserInfo) {

                $upid = $firstUserInfo['id'];
                $upUserLayer = $firstUserInfo[$netName . '_层数'];

                // 循环获得下面几层数据
                for ($i = 0; $i < $showLayer - 1; $i++) {
                    $where = '' . $netName . '_层数=' . ($upUserLayer + $i + 1) . ' and (';
                    foreach ($netPlaceName[$netName] as $region) {
                        $where .= " find_in_set('{$upid}-{$region}',{$netName}_网体数据) or";
                    }
                    $where = trim($where, 'or') . ')';
                    //$downUsers = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号,a.{$netName}_位置",$where);
                    $downUsers = M('用户')->where($where)->select();
                    if (!$downUsers || ($userLookLayer != 0 && ($upUserLayer + $i + 1 - $this->userinfo[$netName . '_层数']) > $userLookLayer)) {
                        break;
                    }
                    $result[$i] = $downUsers;
                }
            }
            $this->assign('thisUser', $this->userinfo);
            $this->assign('showLayer', $showLayer);
            $this->assign('firstUserInfo', $firstUserInfo);
            $this->assign('userNode', $this->userobj);
            $this->assign('netNode', $netNode);
            $this->assign('netName', $netName);
            $this->assign('netTree', $result);
            $this->display('net_place_tree');
        }
    }

    /*
    * 显示 树状 网络分支图(推荐关系)
    */
    private function showRamusTree($netNode, $netPlaceName, $levelsArr)
    {
        if (CONFIG('DEFAULT_THEME') == 'ion') {
            if ($netNode->showbd == 1 and $this->userinfo['服务中心'] == 0) {
                $showLayer = $netNode->showceng; //显示几层
            } else {
                $showLayer = $netNode->userNetLayer; //显示几层
            }
            $userLookLayer = $netNode->userLookLayer; // 查看深度
            $userModel = M('用户');

            $netName = $netNode->name;
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                    $this->error('非法表单数据');
                }
                if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                    $firstUser = $userModel->where("编号='" . trim($_REQUEST['uid']) . "' and find_in_set('{$this->userinfo['id']}',{$netName}_网体数据)")->find();
                    if (!$firstUser) {
                        $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                    }
                }

            }

            /*$levelStr = '';
            foreach($levelsArr as $levelName=>$level){
                $levelStr .= ','.$levelName;
            }*/
            //获取树
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                $result = M('用户')->where(array('编号' => $_REQUEST['uid']))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号","a.编号='".trim($_REQUEST['uid'])."'");
            } else {
                $result = M('用户')->where(array('编号' => $_SESSION[C('USER_AUTH_NUM')]))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号","a.编号='{$_SESSION[C('USER_AUTH_NUM')]}'");
            }
            //dump($result);
            $firstUserInfo = $result[0];
            $result = array();
            if ($firstUserInfo) {
                $upid = $firstUserInfo['id'];
                $upUserLayer = $firstUserInfo[$netName . '_层数'];

                // 循环获得下面几层数据
                for ($i = 0; $i < $showLayer - 1; $i++) {
                    $where = "{$netName}_层数=" . ($upUserLayer + $i + 1) . " and ";
                    $where .= "find_in_set('{$upid}',{$netName}_网体数据)";
                    $downUsers = M('用户')->where($where)->select();
                    //$downUsers = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号",$where);
                    if (!$downUsers || ($userLookLayer != 0 && ($upUserLayer + $i + 1 - $this->userinfo[$netName . '_层数']) > $userLookLayer)) {
                        break;
                    }
                    $result[$i] = $downUsers;
                }
            }
            $this->assign("disname", $netNode->userNameDisp);
            $this->assign("disnickname", $netNode->userAnotherNameDisp);
            $this->assign('thisUser', $this->userinfo);
            $this->assign('firstUserInfo', $firstUserInfo);
            $this->assign('userNode', $this->userobj);
            $this->assign('netNode', $netNode);
            $this->assign('netName', $netName);
            $this->assign('netTree', $result);
            $this->display('net_rec_tree2');

        } else {
            $showLayer = $netNode->userNetLayer;  //显示几层
            $userLookLayer = $netNode->userLookLayer; // 查看深度
            $userModel = M('用户');
            if ($this->userinfo['服务中心'] == 1) {
                $showLayer = $netNode->shopNetLayer;
                $userLookLayer = $netNode->shopLookLayer;
            }
            $netName = $netNode->name;
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                    $this->error('非法表单数据');
                }
                if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                    $firstUser = $userModel->where("编号='" . trim($_REQUEST['uid']) . "' and find_in_set('{$this->userinfo['id']}',{$netName}_网体数据)")->find();
                    if (!$firstUser) {
                        $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                    }
                    //超出深度
                    if ($userLookLayer > 0 && ($firstUser[$netName . '_层数'] - $this->userinfo[$netName . '_层数']) > $userLookLayer) {
                        $this->error("只允许查看" . $userLookLayer . "层内的用户");
                    }
                }

            }

            /*$levelStr = '';
            foreach($levelsArr as $levelName=>$level){
                $levelStr .= ','.$levelName;
            }*/
            //获取树
            if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
                $result = M('用户')->where(array('编号' => $_REQUEST['uid']))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号","a.编号='".trim($_REQUEST['uid'])."'");
            } else {
                $result = M('用户')->where(array('编号' => $_SESSION[C('USER_AUTH_NUM')]))->select();
                //$result = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号","a.编号='{$_SESSION[C('USER_AUTH_NUM')]}'");
            }
            //dump($result);
            $firstUserInfo = $result[0];
            $result = array();
            if ($firstUserInfo) {
                $upid = $firstUserInfo['id'];
                $upUserLayer = $firstUserInfo[$netName . '_层数'];

                // 循环获得下面几层数据
                for ($i = 0; $i < $showLayer - 1; $i++) {
                    $where = "{$netName}_层数=" . ($upUserLayer + $i + 1) . " and ";
                    $where .= "find_in_set('{$upid}',{$netName}_网体数据)";
                    $downUsers = M('用户')->where($where)->select();
                    //$downUsers = $this->userobj->getUserAchieve("a.id,a.编号,a.姓名,a.状态,a.注册日期,a.审核日期{$levelStr},a.{$netName}_层数,a.{$netName}_推荐人数,a.{$netName}_团队总人数,a.{$netName}_团队人数,a.{$netName}_上级编号",$where);
                    if (!$downUsers || ($userLookLayer != 0 && ($upUserLayer + $i + 1 - $this->userinfo[$netName . '_层数']) > $userLookLayer)) {
                        break;
                    }
                    $result[$i] = $downUsers;
                }
            }
            $this->assign("disname", $netNode->userNameDisp);
            $this->assign("disnickname", $netNode->userAnotherNameDisp);

            $this->assign('thisUser', $this->userinfo);
            $this->assign('firstUserInfo', $firstUserInfo);
            $this->assign('userNode', $this->userobj);
            $this->assign('netNode', $netNode);
            $this->assign('netName', $netName);
            $this->assign('netTree', $result);
            $this->display('net_rec_tree');
        }
    }


    /**
     *幸运网
     *
     */
    public function showLineTree()
    {
        $showLayer = 10;//$netNode->userNetLayer;  //显示几层
        $userLookLayer = 10;//$netNode->userLookLayer; // 查看深度
        $userModel = M('用户');

        $netName = X('fun_ifnum@')->name;
        $firstUser = $this->userinfo;
        $upuser = '';
        if (isset($_REQUEST['uid']) && $_REQUEST['uid'] != '') {
            if (preg_match('/\'|\"|;|select|truncate|drop|insert|update|delete|join|union|into|load_file|outfile/i', $_REQUEST['uid'], $matches)) {
                $this->error('非法表单数据');
            }
            if ($_REQUEST['uid'] != $this->userinfo['编号']) {
                $firstUser = $userModel->where("编号='" . trim($_REQUEST['uid']) . "' and {$netName}>{$this->userinfo[$netName]}")->find();
                if (!$firstUser) {
                    $this->error(L('该' . $this->userobj->byname . '不在' . $netName . '网体下!'));
                }
                $upuser = $userModel->where("状态='有效' and {$netName}>{$this->userinfo[$netName]} and {$netName}<{$firstUser[$netName]}")->order($netName . " desc")->getField('编号');
            }
        }

        $levelStr = '';
        foreach ($levelsArr as $levelName => $level) {
            $levelStr .= ',' . $levelName;
        }
        $firstUserInfo = $firstUser;
        $result = array();
        if ($firstUserInfo) {
            $upid = $firstUserInfo['id'];
            $upUserLayer = $firstUserInfo[$netName];

            // 循环获得下面几层数据
            for ($i = 0; $i < $showLayer - 1; $i++) {
                $where = "{$netName}>=" . ($upUserLayer + $i + 1);
                $downUsers = M('用户')->where($where)->find();
                if (!$downUsers) {
                    break;
                }
                $result[$i] = $downUsers;
            }
        }
        $this->assign("upuser", $upuser);
        $this->assign("disname", $netNode->userNameDisp);
        $this->assign("disnickname", $netNode->userAnotherNameDisp);
        $this->assign('thisUser', $this->userinfo);
        $this->assign('firstUserInfo', $firstUserInfo);
        $this->assign('userNode', $this->userobj);
        $this->assign('netNode', $netNode);
        $this->assign('netName', $netName);
        $this->assign('netTree', $result);
        //	dump($result);
        $this->display('net_line_tree');
    }


    /*
* 打印用户级别
*/
    public function print_user_level($level, $levelname)
    {
        if (isset($this->userLevelArray[$levelname][$level])) {
            return $this->userLevelArray[$levelname][$level];
        } else {
            return $level;
        }
    }


    //网络图打印功能
    function printset()
    {
        $this->display();
    }

    /**
     * 直推伙伴列表
     * author   btx
     * time     2017/04/05
     */
    public function myIntros()
    {
        $sNet = M('用户')->where(array("编号"=>$this->userinfo["编号"]))->getField("管理_网体数据");
        $list = new TableListAction("用户");
        $list->where("推荐_上级编号 = '" . $this->userinfo["编号"] . "'");
        $list->order('注册日期 desc');
        $list->setShow = array(
            L('用户编号') => array("row" => '[编号]'),
            L('用户姓名') => array("row" => array(array(&$this,'hideName'),'[姓名]')),
            L('用户级别') => array("row" => array(array(&$this, "getLevels"), "[用户级别]")),
            L('隶属事业中心') => array("row" => array(array(&$this, "areaName"), "[管理_网体数据]",$sNet)),
            L('注册日期') => array('row' => '[注册日期]', 'format' => 'time'),
            L('审核日期') => array('row' =>"[审核日期]" , 'format' => 'time'),
            L('最后登录日期') => array('row' => array(array(&$this, "ifLogin"), "[登入日期]", "[注册日期]"), 'format' => 'time'),
        );
        $list->pagenum = 15;
        $data = $list->getData();
        $this->assign('data', $data);
        $this->display('net_rec_list');
    }
	public function hideName($name){
        return "**" . mb_substr($name, -1, 1, "utf-8");
    }
    /**
     * 根据传入用户级别返回对应的级别名称
     * @param   $lv 数据库中的级别
     * @return  mixed 配置文件中定义的级别名称
     * @author  btx
     * @time    2017/04/05
     */
    public function getLevels($lv)
    {
        $levels = X('levels@' . $levelname);
        foreach ($levels->getcon("con", array("name" => '', "lv" => '')) as $lvconf) {
            if ($lvconf['lv'] == $lv) {
                return $lvconf['name'];
            }
        }
    }

    /**
     * 判断用户是否登入过系统，如果登入日期为空，则返回注册日期
     * @param   $loginDate 登入日期
     * @param   $regDate 注册日期
     * @return  mixed 日期
     * @author  btx
     * @time    2017/04/05
     */
    public function ifLogin($loginDate, $regDate)
    {
        if (empty($loginDate)) {
            return $regDate;
        } else {
            return $loginDate;
        }
    }
    /**
     *根据管理位置返回对应名称
     * @param   $area 管理位置
     * @return  mixed 位置名称
     * @author  btx
     * @time    2017/04/08
     */
    public function areaName($area,$net){
        $cut = strlen($net) + 1;
        $str = substr($area,$cut);
        $arr = explode(',',$str);
        $arrs = explode('-',$arr[0]);
        $areaName = array(
            "A"=>"第一事业中心",
            "B"=>"第二事业中心"
            );
        return $areaName[$arrs[1]];
    }
}

?>