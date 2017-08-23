<?php
defined('APP_NAME') || die('小样，还想走捷径!');
class SaleAction extends CommonAction{
     // 报单订单列表
    public function index(){
         // 订单类别
        $select = array();
         foreach(X('sale_*') as $sale){
             if(!$sale -> productName){
                 $select[$sale -> byname] = $sale -> byname;
                 }
             }
         $setButton = array(
            "查看" => array("class" => "edit", "href" => __APP__ . "/Admin/Sale/view/id/{tl_id}", "target" => "dialog", "height" => "500", "width" => "800", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png'),
             "删除" => array("class" => "delete", "href" => "__URL__/pre_delete/id/{tl_id}", "target" => "dialog", "mask" => "true"),
            );
        
         if(adminshow('baodan_wuliu'))
            {
             if(adminshow('kuaidi')){
                 $setButton["发货"] = array("class" => "sended", "href" => '__URL__/send/id/{tl_id}/', "target" => "navTab", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 // 判断是否是豪华版 如果是豪华版的话自动快递查询
                if(C('VERSION_SWITCH') == '0'){
                     $setButton["快递查询"] = array("class" => "edit", "href" => "http://www.kuaidi100.com/frame/app/index2.html", "target" => "_blank");
                     }
                 $setButton["物流信息"] = array("class" => "sended", "href" => '__URL__/sendview/id/{tl_id}/', "target" => "dialog", "height" => "500", "width" => "800", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 }else{
                 $setButton["发货"] = array("class" => "sended", "href" => '__URL__/sended/id/{tl_id}/', "target" => "ajaxTodo", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 }
             }
         $list = new TableListAction("报单");
         $list -> table("dms_报单 as a");
         $list -> join('dms_用户 as b on a.编号=b.编号') -> field('a.*,b.姓名');
         $list -> where(array("a.产品" => 0, "a.报单状态" => array('neq', '未确认')));
        
         $list -> setButton = $setButton; // 定义按钮显示
         $list -> showPage = true; // 是否显示分页 默认显示
         $list -> order("购买日期 desc");
         $list -> addshow($this -> userobj -> byname . "编号", array("row" => '<a href="' . __URL__ . '/view/id/[id]" target="dialog" height="500" width="800" mask="true" title="查看" rSelect="true">[编号]</a>', "excelMode" => "text", "order" => "a.编号", "searchMode" => "text", 'searchGet' => 'userid', "searchPosition" => "top", 'searchRow' => 'a.编号'));
         $list -> addshow("姓名", array("row" => "[姓名]", "css" => "width:50px", "searchMode" => "text", "excelMode" => "text"));
        
         // 级别信息
        foreach(X('levels') as $levels)
        {
             $_temp = array();
             foreach($levels -> getcon("con", array("name" => "", "lv" => "")) as $lvconf)
            {
                 $_temp[ $lvconf['name'] ] = $lvconf['lv'];
                 }
             // 自己添加原始级别 作显示作用
            $list -> addshow('原始' . $levels -> byname, array("row" => array(array(& $this, "ysUserLevel"), $levels -> name, "[原始" . $levels -> byname . "]"), "searchMode" => "num", "searchSelect" => $_temp));
             $list -> addshow($levels -> byname, array("row" => array(array(& $this, "nlv"), $levels -> name, "[" . $levels -> byname . "]"), "searchMode" => "num"));
             }
        
         $list -> addshow("订单状态" , array("row" => "[报单状态]", "searchPosition" => "top", "searchMode" => "text", "searchSelect" => array('未确认' => '未确认', '已确认' => '已确认', '已结算' => '已结算'), "order" => "报单状态"));
         $list -> addshow("付款日期" , array("row" => "[到款日期]", "format" => "time", "order" => "到款日期", "searchMode" => "date", 'searchGetStart' => 'daytimestart', 'searchGetEnd' => 'daytimeend'));
         // 是否有发货
        if(adminshow('baodan_wuliu'))
            {
             $list -> addshow("物流状态" , array("row" => "[物流状态]", 'searchGet' => 'sendstate', "searchPosition" => "top", "searchMode" => "text", "searchSelect" => array('未发货' => '未发货', '已发货' => '已发货', '已收货' => '已收货')));
             $list -> addshow("发货日期" , array("row" => "[发货日期]", 'format' => 'time', "order" => "发货日期", "searchMode" => "date"));
             $list -> addshow("收货日期" , array("row" => "[收货日期]", 'format' => 'time', "order" => "收货日期", "searchMode" => "date"));
             }
         // 是否有报单中心
        if($this -> userobj -> shopWhere != ''){
             $list -> addshow("报单中心" , array("row" => "[报单中心编号]", "searchMode" => "text"));
             }
         $list -> addshow("付款人" , array("row" => "[付款人编号]", "searchMode" => "text",));
         $list -> addshow("注册人" , array("row" => "[注册人编号]", "searchMode" => "text"));
         $list -> addshow("订单类别" , array("row" => "[报单类别]", "searchMode" => "text", "searchPosition" => "top", 'searchGet' => 'saletype', 'searchRow' => '[byname]', "searchSelect" => $select));
         $list -> addshow("报单金额" , array("row" => "[报单金额]", "searchMode" => "num", "sum" => "报单金额", "order" => "报单金额", "excelMode" => "#,###0.00"));
         $list -> addshow("实付款" , array("row" => "[实付款]", "searchMode" => "num", "sum" => "实付款", "order" => "实付款", "excelMode" => "#,###0.00"));
         // 有升级
        // if($this->userobj->haveUp()){
        // $list->addshow("原级别",array("row"=>array(array(&$this,'_printUserLevel'),'[old_lv]','','[报单类别]'),"searchMode"=>"num","css"=>"width:100px;"));
        // $list->addshow("新级别" ,array("row"=>array(array(&$this,'_printUserLevel'),'[升级数据]','','[报单类别]',"[id]"),"searchMode"=>"num","css"=>"width:100px;"));
        // }
        $this -> assign('list', $list -> getHtml());
         $this -> display();
         }
    
     public function ysUserLevel($levelname, $level){
         $ret = '';
         if($levelname != ''){
             $levels = X('levels@' . $levelname);
             foreach($levels -> getcon("con", array("name" => "", "lv" => "", "area" => "")) as $lvconf)
            {
                 if($level == $lvconf['lv'])
                {
                     $ret = $lvconf['name'];
                     break;
                     }
                 }
             }
         return $ret;
         }
    
     public function nlv($levelname, $level){
         $ret = '';
         if($levelname != ''){
             $levels = X('levels@' . $levelname);
             foreach($levels -> getcon("con", array("name" => "", "lv" => "", "area" => "")) as $lvconf)
            {
                 if($level == $lvconf['lv'])
                {
                     $ret = $lvconf['name'];
                     break;
                     }
                 }
             }
         return $ret;
         }
    
     // 产品订单列表
    public function proIndex(){
         // 订单类别
        $select = array();
         $logistic = false;
         foreach(X('sale_*') as $sale){
             if($sale -> productName){
                 $select[$sale -> byname] = $sale -> byname;
                 if($sale -> logistic) $logistic = true;
                 }
             }
         $setButton = array(
            "查看" => array("class" => "edit", "href" => __APP__ . "/Admin/Sale/view/id/{tl_id}", "target" => "dialog", "height" => "500", "width" => "800", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png'),
             "删除" => array("class" => "delete", "href" => "__URL__/delete/id/{tl_id}", "target" => "ajaxTodo", "mask" => "true"),
            );
         if(adminshow('baodan_wuliu_pro')){
             if(adminshow('kuaidi_pro')){
                 $setButton["发货"] = array("class" => "sended", "href" => '__URL__/send/id/{tl_id}/', "target" => "navTab", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 // 判断是否是豪华版 如果是豪华版的话自动快递查询
                if(C('VERSION_SWITCH') == '0'){
                     $setButton["快递查询"] = array("class" => "edit", "href" => "http://www.kuaidi100.com/frame/app/index2.html", "target" => "_blank");
                     }
                 $setButton["物流信息"] = array("class" => "sended", "href" => '__URL__/sendview/id/{tl_id}/', "target" => "dialog", "height" => "500", "width" => "800", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 }else{
                 $setButton["发货"] = array("class" => "sended", "href" => '__URL__/sended/id/{tl_id}/', "target" => "ajaxTodo", "mask" => "true", 'icon' => '/Public/Images/ExtJSicons/application/application_form_magnify.png');
                 }
             }
        
         $list = new TableListAction("报单");
         $list -> table("dms_报单 as a");
         $list -> join('dms_用户 as b on a.编号=b.编号') -> field('a.*,b.姓名,b.收货人,b.移动电话,b.地址');
         $list -> where(array("a.产品" => 1, "a.报单状态" => array('neq', '未确认')));
         $list -> setButton = $setButton; // 定义按钮显示
         $list -> order("购买日期 desc");
         $list -> addshow("订单ID" , array("row" => "[id]", "searchMode" => "text", "searchPosition" => "top", 'searchRow' => 'a.id', "css" => "width:80px;"));
         $list -> addshow($this -> userobj -> byname . "编号", array("row" => '<a href="' . __URL__ . '/view/id/[id]" target="dialog" height="500" width="800" mask="true" title="查看" rSelect="true">[编号]</a>', "excelMode" => "text", "order" => "a.编号", "searchMode" => "text", 'searchGet' => 'userid', "searchPosition" => "top", 'searchRow' => 'a.编号'));
         $list -> addshow("姓名", array("row" => "[姓名]", "css" => "width:100px", "searchMode" => "text", "excelMode" => "text"));
         $list -> addshow("订单状态" , array("row" => "[报单状态]", "searchPosition" => "top", "searchMode" => "text", "searchSelect" => array('未确认' => '未确认', '已确认' => '已确认', '已结算' => '已结算'), "order" => "报单状态"));
         $list -> addshow("付款日期" , array("row" => "[到款日期]", "searchPosition" => "top", "format" => "time", "order" => "到款日期", "searchMode" => "date", 'searchGetStart' => 'daytimestart', 'searchGetEnd' => 'daytimeend'));
         $list -> addshow("快递公司" , array("row" => "[快递公司]", "searchMode" => "text"));
         $list -> addshow("快递订单" , array("row" => "[快递订单]", "searchMode" => "text"));
         $list -> addshow("快递备注" , array("row" => "[快递备注]", "searchMode" => "text"));
         
         if(adminshow('baodan_wuliu_pro')){
             $list -> addshow("物流状态" , array("row" => "[物流状态]", 'searchGet' => 'sendstate', "searchPosition" => "top", "searchMode" => "text", "searchSelect" => array('未发货' => '未发货', '已发货' => '已发货', '已收货' => '已收货'),));
             $list -> addshow("发货日期" , array("row" => "[发货日期]", 'format' => 'time', "order" => "发货日期", "searchMode" => "date"));
             $list -> addshow("收货日期" , array("row" => "[收货日期]", 'format' => 'time', "order" => "收货日期", "searchMode" => "date"));
             }
         if($this -> userobj -> shopWhere != ''){
             $list -> addshow("报单中心" , array("row" => "[报单中心编号]", "searchMode" => "text"));
             }
        
         $list -> addshow("收货人" , array("row" => "[收货人]"));
         $list -> addshow("移动电话" , array("row" => "[移动电话]"));
         $list -> addshow("地址" , array("row" => "[地址]"));
         $list -> addshow("付款人" , array("row" => "[付款人编号]", "searchMode" => "text", "css" => "width:90px;"));
         $list -> addshow("注册人" , array("row" => "[注册人编号]", "searchMode" => "text", "css" => "width:90px;", 'searchRow' => 'a.注册人编号'));
         $list -> addshow("订单类别" , array("row" => "[报单类别]", "searchMode" => "text", "searchPosition" => "top", 'searchGet' => 'saletype', 'searchRow' => '[byname]', "searchSelect" => $select));
         $list -> addshow("报单金额" , array("row" => "[报单金额]", "searchMode" => "num", "sum" => "报单金额", "order" => "报单金额", "excelMode" => "#,###0.00"));
        
         $list -> addshow("购物金额" , array("row" => "[购物金额]", "searchMode" => "num", "sum" => "[购物金额]", 'order' => '购物金额'));
         if(adminshow('sale_pv')){
             $list -> addshow("购物PV" , array("row" => "[购物PV]" , "searchMode" => "num", "sum" => "[购物PV]", 'order' => '购物PV'));
             }
         $list -> addshow("实付款" , array("row" => "[实付款]", "searchMode" => "num", "sum" => "[实付款]", 'order' => '实付款'));
         if($logistic){
             // 添加物流费显示
            $list -> addshow("物流费" , array("row" => "[物流费]", "searchMode" => "num"));
             }
         // 级别信息
        foreach(X('levels') as $levels)
        {
             $_temp = array();
             foreach($levels -> getcon("con", array("name" => "", "lv" => "")) as $lvconf)
            {
                 $_temp[ $lvconf['name'] ] = $lvconf['lv'];
                 }
             // 自己添加原始级别 作显示作用
            $list -> addshow('原始' . $levels -> byname, array("row" => array(array(& $this, "ysUserLevel"), $levels -> name, "[原始" . $levels -> byname . "]"), "searchMode" => "num", "searchSelect" => $_temp));
             $list -> addshow($levels -> byname, array("row" => array(array(& $this, "nlv"), $levels -> name, "[" . $levels -> byname . "]"), "searchMode" => "num"));
             }
         $this -> assign('list', $list -> getHtml());
         $this -> display();
         }
    
     public function print_index(){
         foreach(X('sale_up') as $sale_up)
        {
             $name1 = $sale_up -> name;
             }
         $id = $_REQUEST ['id'];
         $where['id'] = $id;
         $vo = M('报单') -> where($where) -> find();
         $map['编号'] = $vo['编号'];
         $ho = M('用户') -> where($map) -> find();
         $nodelevels = X('sale_up');
         $nowlevel = "";
         $oldlevel = "";
         foreach($nodelevels as $nodelevel)
        {
             foreach($nodelevel -> getcon("con", array("name" => "", "lv" => 0)) as $level)
            {
                 if($level['lv'] == $vo['升级数据'])
                {
                     $nowlevel = $level['name'];
                     }
                 if($level['lv'] == $vo['old_lv'])
                {
                     $oldlevel = $level['name'];
                     }
                 }
             }
         if($vo['产品']){
             $productdata = M('产品订单') -> where(array('报单id' => $id)) -> select();
             // dump($productdata);
            $this -> assign('productdata', $productdata);
             }
        
         $this -> assign('modtime', date('Y-m-d', time()));
         $this -> assign('sale_up', $name1);
         $this -> assign('nowlevel', $nowlevel);
         $this -> assign('oldlevel', $oldlevel);
         $this -> assign('vo', $vo);
         $this -> assign('ho', $ho);
         $this -> display();
         }
     // 订单查看
    public function view(){
         if(strpos($_GET['id'], ',') !== false){
             $this -> error('参数错误!');
             }
         $model = M('报单');
         $id = $_REQUEST ['id'];
         $where['id'] = $id;
         $vo = $model -> where($where) -> find();
         if($vo['升级数据'] > 0){
             $nowlevel = $this -> _printUserLevel($vo['升级数据'], '', $vo['报单类别']);
             $oldlevel = $this -> _printUserLevel($vo['old_lv'], '', $vo['报单类别']);
             $this -> assign('nowlevel', $nowlevel);
             $this -> assign('oldlevel', $oldlevel);
             }
         if($vo['产品']){
             $productdata = M('产品订单') -> where(array('报单id' => $id)) -> select();
             $this -> assign('productdata', $productdata);
             }
         $this -> assign('vo', $vo);
         // 是否显示pv
        $this -> assign('pvshow', adminshow('sale_pv'));
        
         $this -> display();
         }
     // 未审核列表
    public function auth(){
         $nodelevels = X('levels');
         $user = X('user');
         $lvNodeName = '';
         foreach($nodelevels as $levels){
             $lvNodeName .= 'b.' . $levels -> name . ',';
             }
         $list = new TableListAction("报单");
         $list -> table('dms_报单 a');
         $list -> setButton = array(// 底部操作按钮显示定义
            // '确认审核'=>array("class"=>"edit","href"=>__URL__.'/accok/id/{tl_id}',"target"=>"ajaxTodo","mask"=>"true","title"=>"是否确认审核！"),
            // "删除"=>array("class"=>"delete","href"=>__URL__."/delete/id/{tl_id}","target"=>"ajaxTodo","mask"=>"true"),
            '确认审核' => array("class" => "edit", "href" => __URL__ . '/pre_accok/id/{tl_id}', "target" => "dialog", "mask" => "true"),
             "删除" => array("class" => "delete", "href" => __URL__ . "/pre_delete/id/{tl_id}", "target" => "dialog", "mask" => "true"),
            );
         $where = "a.报单状态 = '未确认'";
         // 推广链接审核
        if(adminshow('tj_tuiguang') && adminshow('order_tuiguang')) $where .= " and a.是否推广链接=0";
         $list -> join("dms_用户 as b on b.编号=a.编号") -> where($where);
         $list -> field($lvNodeName . "b.姓名,a.*");
         $list -> order("a.购买日期 desc");
         $list -> setShow = array(
            $user -> byname . "编号" => array("row" => "[编号]", "searchMode" => "text", "searchPosition" => "top", "searchRow" => 'b.编号'),
             "姓名" => array("row" => "[姓名]"),
             "添加时间" => array("row" => "[购买日期]", "format" => "time", "searchMode" => "date"),
            );
         foreach(X('levels') as $levels)
        {
             $_temp = array();
             foreach($levels -> getcon("con", array("name" => "", "lv" => "")) as $lvconf)
            {
                 $_temp[ $lvconf['name'] ] = $lvconf['lv'];
                 }
             $list -> addshow($levels -> byname, array("row" => array(array(& $this, "nlv"), $levels -> name, "[" . $levels -> byname . "]"), "searchMode" => "num", "searchSelect" => $_temp, "searchRow" => "user." . $levels -> name . "", "order" => 'user.' . $levels -> name));
             }
         $list -> addshow('报单金额', array("row" => "[报单金额]", "searchMode" => "num", 'order' => '报单金额'));
        
         $list -> addshow('购物金额', array("row" => "[购物金额]", "searchMode" => "num"));
         if(adminshow('sale_pv')){
             $list -> addshow("购物PV" , array("row" => "[购物PV]" , "searchMode" => "num", "sum" => "[购物PV]"));
             }
         if($this -> userobj -> haveUp() || $this -> userobj -> haveProUp()){
             $list -> addshow('升级数据', array("row" => array(array(& $this, "_printUserLevel"), "[升级数据]", "", "[报单类别]", "[id]")));
             }
         if($this -> userobj -> shopWhere != ''){
             $list -> addshow('报单中心', array("row" => "[报单中心编号]", "searchMode" => "text"));
             }
         $list -> addshow('报单状态', array("row" => "[报单状态]"));
         $list -> addshow('报单类别', array("row" => "[报单类别]"));
         $list -> addshow('申请备注', array("row" => "[申请备注]"));
         $this -> assign('list', $list -> getHtml());
         $this -> display();
         }
    
     // 推广链接审核订单
    public function tj_auth(){
         $levels = X('levels@');
         $user = X('user');
         $lvNodeName = 'b.' . $levels -> name . ',';
         $list = new TableListAction("报单");
         $list -> table('dms_报单 a');
         $list -> setButton = array(// 底部操作按钮显示定义
            '确认审核' => array("class" => "edit", "href" => __URL__ . '/tj_accok/id/{tl_id}', "target" => "ajaxTodo", "mask" => "true", "title" => "是否确认审核！"),
             "删除" => array("class" => "delete", "href" => __URL__ . "/pre_delete/id/{tl_id}", "target" => "dialog", "mask" => "true"),
            );
         $list -> join("dms_用户 as b on b.编号=a.编号") -> where("a.报单状态 = '未确认' and 是否推广链接='1'");
         $list -> field($lvNodeName . "a.id,b.编号,b.注册日期,b.推荐_上级编号,b.姓名,a.报单状态,a.报单金额,a.报单中心编号,a.购物金额,a.购物PV,a.报单类别");
         $list -> order("a.购买日期 desc");
         $list -> setShow = array(
            $user -> byname . "编号" => array("row" => "[编号]", "searchMode" => "text", "searchPosition" => "top", "searchRow" => 'b.编号'),
             "姓名" => array("row" => "[姓名]"),
             "注册时间" => array("row" => "[注册日期]", "format" => "time", "searchMode" => "date"),
            );
         $list -> addshow('推荐人编号', array("row" => "[推荐_上级编号]"));
        
         $_temp = array();
         foreach($levels -> getcon("con", array("name" => "", "lv" => "")) as $lvconf)
        {
             $_temp[ $lvconf['name'] ] = $lvconf['lv'];
             }
         $list -> addshow($levels -> byname, array("row" => array(array(& $this, "_printUserLevel"), "[" . $levels -> name . "]", $levels -> name), "searchMode" => "num", "searchSelect" => $_temp, "searchRow" => "[" . $levels -> name . "]"));
        
         $list -> addshow('报单金额', array("row" => "[报单金额]", "searchMode" => "num"));
        
         $list -> addshow('购物金额', array("row" => "[购物金额]", "searchMode" => "num"));
         if(adminshow('sale_pv')){
             $list -> addshow("购物PV" , array("row" => "[购物PV]" , "searchMode" => "num", "sum" => "[购物PV]"));
             }
         $list -> addshow('报单状态', array("row" => "[报单状态]"));
         $list -> addshow('报单类别', array("row" => "[报单类别]"));
         $this -> assign('list', $list -> getHtml());
         $this -> display();
         }
     // 审核确认前
    public function pre_accok(){
         $sdata = array();
         if(isset($_GET['id'])){
             $sdata = M("报单") -> where("id in(" . $_GET['id'] . ")") -> getField("id idkey,编号,购买日期,报单金额,购物金额,报单状态,报单类别");
             $this -> assign('ids', $_GET['id']);
             }
         foreach($sdata as $s => $k){
             $sdata[$s]['购买日期'] = date("Y-m-d", $k['购买日期']);
             }
         $this -> assign('sdata', $sdata);
         $this -> display();
         }
    
     // 审核确认
    public function accok(){
         set_time_limit(0);
         ini_set('memory_limit', '-1');
         $errMsg = array();
         $succNum = 0;
         $errNum = 0;
         foreach(explode(',', $_POST['ids']) as $saleid){
             if($saleid == '') continue;
             M() -> startTrans();
             // 用于锁用户表全表
            M('用户') -> lock(true) -> where('id<0') -> find();
             $sdata = M("报单") -> lock(true) -> where(array('id' => $saleid)) -> find();
             if($sdata['报单状态'] != '未确认') continue;
             $salename = $sdata['报单类别'];
             $userid = $sdata['编号'];
             $sale = X('sale_*@' . $salename);
             if($userid == '' || $sale === false){
                 $errNum++;
                 // $errMsg .= $userid.'：参数错误！<br/>';
                $errMsg[$saleid] = array('msg' => '参数错误');
                 continue;
                 }
             // 判断审核注册单是否扣款
            if($_POST['acc'] == 1){
                 $sale -> adminAccDeduct = true;
                 }else{
                 $sale -> adminAccDeduct = false;
                 }
             // 审核 扣款
            $return = $sale -> accok($sdata, true);
             if($return !== true){
                 $errNum++;
                 // $errMsg .= $userid.'：'.$return.'<br/>';
                $errMsg[$saleid] = array('msg' => $return);
                 M() -> rollback();
                 continue;
                 }
             $errMsg[$saleid] = array('msg' => '已确认');
             M() -> commit();
             M() -> startTrans();
             // 审核短信发送
            sendSms("accok", $sdata['编号'], $sale -> byname . '审核', $sdata);
             $this -> saveAdminLog("", "", '订单审核', "审核用户[" . $sdata['编号'] . "]" . date("Y-m-d", $sdata['购买日期']) . $sdata['报单类别'] . '订单');
             M() -> commit();
             $succNum++;
        }
         echo json_encode($errMsg);
        /**
         * if($errNum !=0){
         * $this->error("审核成功：".$succNum .'条记录；审核失败：'.$errNum .'条记录；<br/>'.$errMsg);
         * }else{
         * $this->success("审核成功：".$succNum .'条记录；');
         * }
         */
         }
    
     public function tj_accok(){
         set_time_limit(0);
         ini_set('memory_limit', '-1');
         $errMsg = '';
         $succNum = 0;
         $errNum = 0;
         foreach(explode(',', $_GET['id']) as $saleid){
             if($saleid == '') continue;
            
             M() -> startTrans();
             M('用户') -> where('id<0') -> lock(true) -> find();
             $sdata = M("报单") -> lock(true) -> where(array('id' => $saleid)) -> find();
             // $new_user = M('用户')->where(array('编号'=>$sdata['编号']))->find();
            // $upuser = M('用户')->where(array('编号'=>$new_user['推荐_上级编号']))->find();
            $salename = $sdata['报单类别'];
             $userid = $sdata['编号'];
             $sale = X('sale_*@' . $salename);
             if($userid == '' || $sale === false){
                 $errNum++;
                 $errMsg .= $userid . '：参数错误！<br/>';
                 continue;
                 }
             // 审核 扣款
            $return = $sale -> accok($sdata, true);
             if($return !== true){
                 $errNum++;
                 $errMsg .= $userid . '：' . $return . '<br/>';
                 M() -> rollback();
                 continue;
                 }
             M() -> commit();
             $succNum++;
             $this -> saveAdminLog("", "", '订单审核', "审核用户[" . $sdata['编号'] . "]" . date("Y-m-d", $sdata['购买日期']) . $sdata['报单类别'] . '订单');
             }
         if($errNum != 0){
             $this -> error("审核成功：" . $succNum . '条记录；审核失败：' . $errNum . '条记录；<br/>' . $errMsg);
             }else{
             $this -> success("审核成功：" . $succNum . '条记录；');
             }
         }
    
     // 用户注册页面
    public function reg(sale_reg $sale_reg){
         $require = explode(',', CONFIG('USER_REG_REQUIRED'));
         $show = explode(',', CONFIG('USER_REG_SHOW'));
         // 密保问题
        $SecretSafe = M('密保');
         $SecretSafelist = $SecretSafe -> order('id asc') -> select();
         $this -> assign('SecretSafelist', $SecretSafelist);
         $this -> assign('reg_safe', adminshow('mibao'));
        
         // 注册是否选产品--product.html
        $zkbool = false;
         $logistic = false;
         if($sale_reg -> productName){
             $proobj = X("product@" . $sale_reg -> productName);
             $productArr = $proobj -> getProductArray($sale_reg);
             $this -> assign('productArr', $productArr);
             $this -> assign('proobj', $proobj);
             // 是否有折扣
            $zkbool = $this -> userobj -> haveZhekou($sale_reg);
             // 是否有物流费
            if($sale_reg -> logistic) $logistic = true;
             }
         $this -> assign('zkbool', $zkbool);
         $this -> assign('logistic', $logistic);
         // 判断是否需要生成编号
        if($this -> userobj -> idAutoEdit){
             // 创建新编号
            M() -> startTrans();
             $newid = $this -> userobj -> getnewid();
             M() -> commit();
             // 如果不能编辑,则放到SESSION中
            if(!$this -> userobj -> idEdit){
                 // 赋值SESSION
                session('userid_reg', $newid);
                 }
             $this -> assign('userid', $newid);
             }
        
         $this -> assign('sale', $sale_reg);
         $this -> assign('alert', $sale_reg -> alert);
         $this -> assign('user', $this -> userobj);
        
         // 取得网体信息
        $nets = array();
         foreach(X('net_rec,net_place') as $net)
        {
             if(!$net -> regDisp)
                 continue;
             // 需要调用的其他连带表单
            $otherpost = '';
             if(isset($net -> fromNet) && $net -> fromNet != '')
                {
                 $otherpost .= ',net_' . $net -> getPos();
                 $otherpost .= ',net_' . X('net_rec@' . $net -> fromNet) -> getPos();
                 }
             $value = "";
             // $position			= $net->getRegion();
            if(isset($net -> setRegion) && $net -> setRegion == true)
                {
                 if(isset($_GET['pid']) && $_GET['pid'] != '')
                    {
                     $value = $_GET['pid'];
                     }
                 $otherpost = 'net_' . $net -> getPos() . "_Region";
                 }
             $nets[] = array("type" => 'text', "name" => $net -> name . "人编号", "inputname" => "net_" . $net -> getPos(), "otherpost" => $otherpost, "value" => $value, 'require' => $net -> mustUp);
             if(isset($net -> setRegion) && $net -> setRegion == true)
                {
                 $RegionSet = array();
                 foreach($net -> getRegion() as $Region)
                {
                     // 是否可以显示这个region
                    $regiondisp = true;
                     // 默认有where则关闭掉
                    if(isset($Region['where']) && $Region['where'] != '')
                        {
                         $regiondisp = false;
                         // 如果存在通过网络图点击得到的特定用户编号
                        if($value)
                        {
                             // 找到这个用户
                            $upuser = M('用户') -> where(array('编号' => $value)) -> find();
                             // 对显示区域的where做判断
                            if($upuser && transforms($Region['where'], $upuser))
                                {
                                 // 判断成功.这个区也可以显示
                                $regiondisp = true;
                                 }
                             }
                         }
                     if($regiondisp)
                    {
                         $RegionSet[] = $Region;
                         }
                     }
                 $nets[] = array("type" => 'select', "Region" => $RegionSet, "name" => $net -> byname . "人位置", "inputname" => "net_" . $net -> getPos() . "_Region", "otherpost" => 'net_' . $net -> getPos(), 'require' => $net -> mustUp);
                 }
            
             }
         $this -> assign('nets', $nets);
         // 取得级别信息
        $levels = X('levels@' . $sale_reg -> lvName);
         $this -> assign('levels', $levels);
         $levelsopt = array();
         foreach($levels -> getcon("con", array("name" => "", "lv" => 0, 'use' => '')) as $opt)
        {
             if($opt['use'] != 'false'){
                 $levelsopt[] = $opt;
                 }
             }
         // xml中的fun_select配置  如:配置是否显示服务中心 套餐等
        $fun_selectarr = array();
         foreach(X('fun_select') as $fun_select)
        {
             if($fun_select -> regDisp)
            {
                 $select_cons = $fun_select -> getcon('con', array('name' => '', 'val' => 0));
                 $select_pos = $fun_select -> getPos();
                 $fun_selectarr['select_' . $select_pos]['name'] = $fun_select -> name;
                 $fun_selectarr['select_' . $select_pos]['default'] = $fun_select -> default;
                     foreach($select_cons as $select_con)
                    {
                         $fun_selectarr['select_' . $select_pos]['con'][] = array('name' => $select_con['name'], 'val' => $select_con['val']);
                         }
                     }
                 }
             // xml中的附加配置注册显示字段  是否统一添加安智网
            $fun_arr = array();
             $funReg = array();
             foreach(X('fun_val') as $fun_val){
                 if($fun_val -> regDisp && $fun_val -> resetrequest != '')
                {
                     $fun_arr[$fun_val -> name] = 'fun_' . $fun_val -> getPos();
                     }
                 if($fun_val -> regDisp){
                     $funReg[] = $fun_val -> name;
                     if($fun_val -> required){
                         $require[] = $fun_val -> name;
                         }
                     }
                 }
             $Bank = M('银行卡');
             $banklist = $Bank -> order('id asc') -> select();
             $this -> assign('banklist', $banklist);
             $this -> assign('fun_val', $fun_arr);
             $this -> assign('funReg', $funReg);
             $this -> assign('fun_select', $fun_selectarr);
             $this -> assign('jsrequire', json_encode($require));
             $this -> assign('require', $require);
             $this -> assign('show', $show);
             $this -> assign('pwd3Switch', adminshow('pwd3Switch'));
             $this -> assign('levelsopt', $levelsopt);
             $this -> assign('haveuser', $this -> userobj -> have(''));
             // 空点回填模式
            $regtype = array(0 => "实点");
             // 有空点
            if(adminshow('admin_blank')){
                 $regtype[1] = "空点";
                 }
             // 有回填
            if(adminshow('admin_backfill')){
                 $regtype[2] = "空点回填";
                 }
             $this -> assign('regtype', $regtype);
             $this -> display();
             }
        
         public function regSave(sale_reg $sale_reg){
             $udata = $_POST;
             set_time_limit(0);
             ini_set('memory_limit', '-1');
             // 获得当前注册单节点
            $m_user = M('用户');
             $m_user -> startTrans();
             $m_user -> where('id<0') -> lock(true) -> count();

             // 如果编号为自动生成,并且不能编辑,则取得reg方法时生成的用户新编号
            if($this -> userobj -> idAutoEdit && !$this -> userobj -> idEdit){
                 $_POST["userid"] = session('userid_reg');
                 }
             // 空点或回填不用审核，如审核，需底层重构代码（报单中的各种值更改）
            if(isset($_POST['nullMode']) && $_POST['nullMode'] > 0) $sale_reg -> confirm = true;

             $checkResult = $sale_reg -> getValidate($_POST); //自动验证
             // 如果验证失败
            if($checkResult['error']){
                 // 输出错误内容
                $errorStr = '';
                 foreach($checkResult['error'] as $error){
                     $errorStr .= $error . '<br>';
                     }
                 $this -> error($errorStr);
                 }else{
                 // 执行注册操作
                $return = $sale_reg -> regSave($_POST);
                 if(gettype($return) == 'string')
                    {
                     $this -> error($return);
                     }
					 /*btx 同步注册慕悦集 start*/
                // $userinfo = M('用户')->where(array('编号'=>$return['userid']))->find();
                // $reg_data['username'] = $userinfo['编号'];
                // $reg_data['loginPwd'] = $userinfo['pass1'];
                // $reg_data['payPwd'] = $userinfo['pass2'];
                // $reg_data['tj_no'] = $userinfo['推荐_上级编号'];
                // $res = json_decode(cCurlInit(C('REGISTER_URL'),$reg_data));
                // if($res->code != 200){
                //     M()->rollback();
                //     $this->error('注册插入members失败，原因为' .$res->code);
                // }
                /*btx 同步注册慕悦集 end*/
                 $m_user -> commit();
                 $this -> saveAdminLog('', '', $_POST["userid"] . "注册成功");
                // 注册短信发送
                $user = M('用户');
                $user->startTrans();
                //$userinfo = M('用户')->where(array('编号'=>$return['userid']))->find();
                $userinfo['pass1'] = $udata['pass1'];
                $userinfo['pass2'] = $udata['pass2'];
                $sendresult = sendSms("reg", $return['userid'], $this->userobj->byname . '注册', $userinfo);
                $user->commit();
                 $this -> success('注册成功！');
                 }
             }
         public function regAjax(sale_reg $sale_reg)
        {
             // 如果编号为自动生成,并且不能编辑,则取得reg方法时生成的用户新编号
            if($this -> userobj -> idAutoEdit && !$this -> userobj -> idEdit){
                 $_POST["userid"] = session('userid_reg');
                 }
             $result = $sale_reg -> getValidate($_POST); //自动验证
             $errs = funajax($result['error'], $this -> userobj);
             $this -> assign('errs', $errs);
             $this -> display();
             }
        
         // 用户升级
        public function up(sale_up $sale_up)
        {
             // 是否选产品
            $zkbool = false;
             $logistic = false;
             if($sale_up -> productName){
                 $proobj = X("product@" . $sale_up -> productName);
                 $productArr = $proobj -> getProductArray($sale_up);
                 $this -> assign('productArr', $productArr);
                 $this -> assign('proobj', $proobj);
                 // 是否有折扣
                $zkbool = $this -> userobj -> haveZhekou($sale_up);
                 // 是否有物流费
                if($sale_up -> logistic) $logistic = true;
                 }
             $this -> assign('zkbool', $zkbool);
             $this -> assign('logistic', $logistic);
            
             // 是否选择回填
            $upBackFill = false; //回填开启并且升级选择回填开启
             if(adminshow('admin_backfill') && adminshow('admin_up_backfill')){
                 $name1 = X("sale_reg@") -> lvName; //第一种级别
                 if($sale_up -> lvName == $name1)$upBackFill = true;
                 }
             $levels = X('levels@' . $sale_up -> lvName);
             $area = array();
             foreach($levels -> getcon("con", array("name" => "", "lv" => 0, 'use' => '', 'area' => '')) as $opt)
            {
                 // 后台用户升级显示全部用户，前台根据use显示
                $levelsopt[] = $opt;
                 if($levels -> area && $opt['area'] != ''){
                     $area[$opt['area']] = $opt['name'];
                     }
                 }
             $this -> assign('sale', $sale_up);
             $this -> assign('user', $this -> userobj);
             $this -> assign('levels', $levels);
             $this -> assign('haveBackFill', $upBackFill);
             $this -> assign('levelsopt', $levelsopt);
             // 区域代理
            $this -> assign('area', $area);
             $this -> display();
             }
         public function upSave(sale_up $sale_up)
        {
             set_time_limit(0);
             ini_set('memory_limit', '-1');
             $userid = trim($_POST['userid']); //处理表单提交时两端的空白字符
             if($userid == ''){
                 $this -> error("请填写用户编号");
                 }
             M() -> startTrans();
             $userdata = $this -> userobj -> getuser(strval($userid));
             if(!$userdata){
                 $this -> error("未获取到用户信息");
                 }
             $oldlv = $userdata[$sale_up -> lvName];
             $newlv = $_POST['lv'];
             $level = X("levels@" . $sale_up -> lvName);
             if($oldlv == $_POST['lv'])
            {
                 if(!$level -> area) $this -> error('您选择的新级别和当前级别一致，无法操作');
                 else{ // 代理
                     foreach($level -> getcon("con", array("area" => "", "lv" => 0)) as $lvconf){
                         if($lvconf['lv'] == $_POST['lv'] && $lvconf['area'] == ''){
                             $this -> error('您选择的新级别和当前级别一致，无法操作');
                             }
                         }
                     }
                 }
             // 回填的不应该扣币或产生业绩
            /**
             * if(isset($_POST['backFill']) && $_POST['backFill']==1 && ((isset($_POST['point']) && $_POST['point']==0) || (isset($_POST['deduct_acc']) && $_POST['deduct_acc']==0))){
             * $this->error("回填请不要选择'产生业绩'或'扣除货币'");
             * }
             */
            // 回填不用审核，如审核，需底层重构代码（报单中的各种值更改），同上面的regsave
            if(isset($_POST['backFill']) && $_POST['backFill'] == 1)
                 $sale_up -> confirm = true;
            
             $checkResult = $sale_up -> getValidate($_POST); //自动验证
             if($checkResult['error']){
                 $errorStr = '';
                 foreach($checkResult['error'] as $error){
                     $errorStr .= $error . '<br/>';
                     }
                 $this -> error($errorStr);
                 }
             $return = $sale_up -> upSave($_POST);
             if(gettype($return) == 'string')
                {
                 $this -> error($return);
                 }
             M() -> commit();
             $oldlevel = $this -> _printUserLevel($oldlv, $sale_up -> lvName);
             $newlevel = $this -> _printUserLevel($newlv, $sale_up -> lvName);
             $this -> saveAdminLog(array($sale_up -> lvName => $oldlevel), array($sale_up -> lvName => $newlevel), X('user') -> byname . '升级', $userid . '升级成功');
             $this -> success('操作完成！');
             }
         public function upAjax(sale_up $sale_up)
        {
             $userid = trim(isset($_GET['userid'])?$_GET['userid']:''); //表单输入完时处理字符串两端的空格
             $levels = X('levels@' . $sale_up -> lvName);
             $levelsopt = $levels -> getcon("con", array("name" => "", "lv" => 0));
             $m = M('用户');
             $list = $m -> where(array("编号" => $userid)) -> find();
             $levelsopts = array();
             if($list)
            {
                 foreach($levelsopt as $key => $level)
                {
                     if($level['lv'] == $list[$levels -> name])
                    {
                         $levelsopts = array("name" => $level['name'], "lv" => $level['lv'], "姓名" => $list['姓名']);
                         }
                     }
                 $this -> ajaxReturn($levelsopts, '成功', 1);
                 }
            else
                {
                 $this -> ajaxReturn('', '失败', 0);
                 }
             }
        
         // 用户商务中心升级显示推荐人姓名
        public function upf(sale_up $sale_up){
             $userid = trim(isset($_GET['tjr'])?$_GET['tjr']:'');
             $tj = M('用户') -> where("编号='" . $userid . "'") -> find();
             if(!$tj){
                 $this -> ajaxReturn('', '失败', 0);
                 }else{
                 $this -> ajaxReturn($tj['姓名'], '成功', 1);
                 }
             }
        
         // 如果是二次升级商务中心 不需要再次填写推荐人
        public function sxt(sale_up $sale_up){
             $userid = trim(isset($_GET['userid'])?$_GET['userid']:'');
             $tj = M('用户') -> where("编号='" . $userid . "'") -> getField('服务中心推荐人');
             if(!$tj){
                 $this -> ajaxReturn('', '失败', 0);
                 }else{
                 $this -> ajaxReturn('', '成功', 1);
                 }
             }
         public function showinfo(sale_up $sale_up)
        {
             $userid = trim(isset($_GET['userid'])?$_GET['userid']:'');
             if($userid == '')
            {
                 $this -> ajaxReturn('编号不能为空', '失败', 0);
                 }
             $lv = M('用户') -> where(array("编号" => "$userid")) -> getField($sale_up -> lvName);
             if($lv)
            {
                 $this -> ajaxReturn($lv, '成功', 1);
                 }
            else
                {
                 $this -> ajaxReturn('', '失败', 0);
                 }
            
             }
         // 重复投资
        public function buy(sale_buy $sale_buy){
             // 是否选产品
            $zkbool = false;
             $logistic = false;
             if($sale_buy -> productName){
                 $proobj = X("product@" . $sale_buy -> productName);
                 $productArr = $proobj -> getProductArray($sale_buy);
                 $this -> assign('productArr', $productArr);
                 $this -> assign('productName', $sale_buy -> productName);
                 $this -> assign('proobj', $proobj);
                 // 是否有折扣
                $zkbool = $this -> userobj -> haveZhekou($sale_buy);
                 // 是否有物流费
                if($sale_buy -> logistic) $logistic = true;
                 }
             $this -> assign('zkbool', $zkbool);
             $this -> assign('logistic', $logistic);
            
             $this -> assign('sale', $sale_buy);
             $this -> assign('name', $sale_buy -> byname);
             $this -> assign('user', $this -> userobj);
             $this -> display();
             }
         public function buyAjax(sale_buy $sale_buy)
        {
             $userid = $_POST['userid'];
             $userinfo = M('用户') -> where(array("编号" => $userid)) -> getField("id");
             if(!$userinfo)
            {
                 $this -> ajaxReturn('', $this -> userobj -> byname . '编号不存在！', 0);
                 }
             }
         public function buySave(sale_buy $sale_buy)
        {
             set_time_limit(0);
             ini_set('memory_limit', '-1');
             $this -> buyAjax($sale_buy);
             M() -> startTrans();
             $checkResult = $sale_buy -> getValidate($_POST); //自动验证
             if($checkResult['error']){
                 $errorStr = '';
                 foreach($checkResult['error'] as $error){
                     $errorStr .= $error . '<br/>';
                     }
                 $this -> error($errorStr);
                 }
             $where['编号'] = $_REQUEST['userid'];
             $userdata = M('用户') -> where($where) -> find();
             $rswhere = $sale_buy -> iswhere($userdata);
             if($rswhere !== true){
                 $this -> error($rswhere);
                 }
             $return = $sale_buy -> buy($_POST);
             if(gettype($return) == 'string')
                {
                 $this -> error($return);
                 }
             M() -> commit();
             $this -> saveAdminLog($_POST, $_POST, $sale_buy -> name . '提交', $sale_buy -> name . '提交成功');
             $this -> success($sale_buy -> byname . "成功");
             }
         // 转正申请记录
        public function applist(){
             $setButton = array(
                "审核" => array("class" => "edit", "href" => "__URL__/applyview/id/{tl_id}", "target" => "dialog", "height" => "800", "width" => "800", "mask" => "true"),
                 "撤销" => array("class" => "delete", "href" => "__URL__/applydel/id/{tl_id}", "target" => "ajaxTodo", "mask" => "true", "title" => "是否确认撤销申请！"),
                 "转正用户" => array("class" => "edit", "href" => "__URL__/addapply", "target" => "navtab", "mask" => "true"),
                );
             $list = new TableListAction("报单");
             $list -> table("dms_报单 a");
             $list -> join("inner join (select * from dms_申请回填 where 申请状态='未审核') b on a.id=b.saleid");
             $list -> field('a.*,b.*');
             $list -> showPage = true; // 是否显示分页 默认显示
             $list -> setButton = $setButton;
             $list -> addshow("编号", array("row" => "[编号]", "css" => "width:100px", "searchRow" => 'a.编号', "searchMode" => "text", "searchPosition" => "top"));
             $list -> addshow("报单金额" , array("row" => "[报单金额]", "searchMode" => "num", "order" => "a.报单金额", "excelMode" => "#,###0.00"));
             foreach(X("sale_reg") as $sale_reg){
                 if($sale_reg -> user == "admin" && $sale_reg -> productName != ""){
                     $list -> addshow("购物金额", array("row" => "[购物金额]", "searchMode" => "num", 'order' => 'a.购物金额', "excelMode" => "#,###0.00"));
                     if(adminshow('sale_pv')){
                         $list -> addshow("购物PV", array("row" => "[购物PV]", "searchMode" => "num", 'order' => 'a.购物PV', "excelMode" => "#,###0.00"));
                         }
                     }
                 }
             $list -> addshow("回填金额", array("row" => "[回填金额]", "css" => "width:70px", "searchRow" => "a.回填金额", "searchMode" => "num", "order" => 'a.回填金额'));
             $list -> addshow("申请日期", array("row" => "[申请日期]", "format" => "time", "order" => "申请日期", "searchMode" => "date", 'searchGetStart' => 'daytimestart', 'searchGetEnd' => 'daytimeend', "searchRow" => "b.申请日期"));
             $list -> addshow("转正方式", array("row" => "[转正方式]", "searchMode" => "text", 'searchRow' => 'b.转正方式', "searchSelect" => array("回填转正" => "回填转正", "立即转正" => "立即转正"), "searchPosition" => "top"));
             $list -> addshow("审核日期", array("row" => "[审核日期]", "format" => "time", "order" => "审核日期", "searchMode" => "date", 'searchGetStart' => 'daytimestart', 'searchGetEnd' => 'daytimeend', "searchRow" => "b.审核日期"));
             $this -> assign('list', $list -> getHtml());
             $this -> display();
             }
         // 转正用户
        public function addapply(){
             $username = "";
             if(isset($_GET['uid'])){
                 $username = $_GET['uid'];
                 $map['报单状态'] = array("in", "空单,回填");
                 $map['编号'] = $username;
                 $saleData = M("报单") -> where($map) -> find();
                 if(!$saleData){
                     $this -> error("用户" . $_GET['uid'] . "没有要回填的订单");
                     }
                 $this -> assign('saleData', $saleData);
                 $this -> assign('adminshow', adminshow('sale_pv'));
                 if($saleData['产品'] == 1){
                     $productData = M('产品订单') -> where(array('报单id' => $_GET['id'])) -> select();
                     $this -> assign('productData', $productData);
                     }
                 // 奖金回填方案
                $this -> assign("backfill", X("prize_backfill"));
                 }
             foreach(X("fun_bank") as $fun_bank){
                 $banks[$fun_bank -> name] = $fun_bank -> byname;
                 }
             $this -> assign("banks", $banks);
             $this -> assign("username", $username);
             // 奖金回填方案
            $this -> assign("backfill", X("prize_backfill"));
             $this -> display();
             }
         public function applysave(){
             if(!isset($_POST['uid'])){
                 $this -> error("参数错误");
                 }
             M() -> startTrans();
             // 查询当前用户的空单
            $map['报单状态'] = array("in", "空单,回填");
             $map['编号'] = $_POST['uid'];
             $saleData = M("报单") -> where($map) -> lock(true) -> find();
             // 订单状态的判断
            if($saleData['报单状态'] != "空单"){
                 if($saleData['报单状态'] == "回填" && $_POST['type'] == "回填转正"){
                     $this -> error(L("报单已成为回填单"));
                     }
                 if($saleData['报单状态'] != "回填"){
                     $this -> error(L("报单已回填完成"));
                     }
                 }
             // 申请记录的状态判断
            $applydata = M("申请回填") -> where("(转正方式='" . $_POST['type'] . "' or 申请状态='未审核') and 编号='" . $saleData['编号'] . "' and saleid='" . $saleData['id'] . "'") -> find();
             if(isset($applydata)){
                 $this -> error(L("已有申请提交等待审核或者已申请过" . $_POST['type']));
                 }
             // 保存申请记录
            $data = array(
                "saleid" => $saleData['id'],
                 "编号" => $saleData['编号'],
                 "转正方式" => $_POST["type"],
                 "申请日期" => systemTime(),
                 "申请状态" => "未审核"
                );
             $pid = M("申请回填") -> add($data);
             $sale = X("@" . $saleData['报单类别']);
             $saleData['pid'] = $pid;
             $saleData['申请日期'] = $data["申请日期"];
             $saleData['转正方式'] = $_POST["type"];
             $saleData['申请状态'] = "未审核";
             $return = $sale -> applyok($saleData, $_POST['accbank']);
             if($return !== true){
                 $this -> error($return);
                 }
             M() -> commit();
             $this -> saveAdminLog("", "", '添加转正', "添加转正用户[" . $saleData['编号'] . "]" . date("Y-m-d", $saleData['申请日期']) . $saleData['转正方式']);
             $this -> success("操作完成");
             }
         // 撤销转正申请
        public function applydel(){
             if(!$_REQUEST['id']){
                 $this -> error("参数错误");
                 }
             $errMsg = '';
             $succNum = 0;
             $errNum = 0;
             foreach(explode(',', $_REQUEST['id']) as $id){
                 if(!$id) continue;
                 M() -> startTrans();
                 $apply = M('申请回填') -> table("dms_申请回填 as a") -> join('dms_报单 as b on b.编号=a.编号 and b.id=a.saleid') -> where(array("a.id" => $id)) -> lock(true) -> field("a.id as pid,a.saleid,a.编号,a.申请日期,a.申请状态,a.转正方式,b.*") -> find();
                 if(!$apply){
                     $errNum++;
                     $errMsg .= '转正申请：' . $id . '不存在<br/>';
                     M() -> rollback();
                     continue;
                     }
                 $result = M('申请回填') -> delete($id);
                 if(!$result){
                     $errNum++;
                     $errMsg .= $apply['编号'] . '转正申请：' . '撤销失败<br/>';
                     M() -> rollback();
                     continue;
                     }
                 M() -> commit();
                 $this -> saveAdminLog("", "", '转正撤销', "转正用户[" . $apply['编号'] . "]" . date("Y-m-d", $apply['申请日期']) . $apply['转正方式'] . '申请');
                 $succNum++;
                 }
             if($errNum != 0){
                 $this -> error("撤销成功：" . $succNum . '条记录；撤销失败：' . $errNum . '条记录；<br/>' . $errMsg);
                 }else{
                 $this -> success("撤销成功：" . $succNum . '条记录；');
                 }
             }
         // 审核信息
        public function applyview(){
             if(!$_REQUEST['id']){
                 $this -> error("参数错误");
                 }
             $applydatas = M('申请回填') -> table("dms_申请回填 as a") -> join('dms_报单 as b on b.编号=a.编号 and b.id=a.saleid') -> where(array("a.id" => array("in", $_REQUEST['id']))) -> field("a.id as pid,a.saleid,a.编号,a.申请日期,a.申请状态,a.转正方式,b.*") -> select();
             $this -> assign('applydatas', $applydatas);
             $this -> assign('adminshow', adminshow('sale_pv'));
             $this -> assign('idstrs', $_REQUEST['id']);
             foreach(X("fun_bank") as $fun_bank){
                 $banks[$fun_bank -> name] = $fun_bank -> byname;
                 }
             $this -> assign("banks", $banks);
             $this -> display();
             }
         public function applyok(){
             if(!$_REQUEST['idstrs']){
                 $this -> error("参数错误");
                 }
             $accbank = $_POST['accbank'];
             $errMsg = '';
             $succNum = 0;
             $errNum = 0;
             foreach(explode(',', $_REQUEST['idstrs']) as $id){
                 if(!$id) continue;
                 M() -> startTrans();
                 $applydata = M('申请回填') -> table("dms_申请回填 as a") -> join('dms_报单 as b on b.编号=a.编号 and b.id=a.saleid') -> where(array("a.id" => $id)) -> lock(true) -> field("a.id as pid,a.saleid,a.编号,a.申请日期,a.申请状态,a.转正方式,b.*") -> find();
                 $sale = X("@" . $applydata['报单类别']);
                 $return = $sale -> applyok($applydata, $accbank);
                 if($return !== true){
                     $errNum++;
                     $errMsg .= $applydata['编号'] . '转正：' . $return . '<br/>';
                     M() -> rollback();
                     continue;
                     }
                 M() -> commit();
                 $this -> saveAdminLog("", "", '转正审核', "转正用户[" . $applydata['编号'] . "]" . date("Y-m-d", $applydata['申请日期']) . $applydata['转正方式'] . '申请');
                 $succNum++;
                 }
             if($errNum != 0){
                 $this -> error("审核成功：" . $succNum . '条记录；审核失败：' . $errNum . '条记录；<br/>' . $errMsg);
                 }else{
                 $this -> success("审核成功：" . $succNum . '条记录；');
                 }
             }
         // 返回姓名
        public function realnameAjax()
        {
             $user = $this -> userobj -> getuser($_POST['userid']);
             if($user && $_POST['userid'] != '')
            {
                 $this -> ajaxReturn(array('姓名' => $user['姓名']), '成功', 1);
                 }
            else
                {
                 $this -> ajaxReturn('', '失败', 0);
                 }
             }
         // 订单删除前
        public function pre_delete()
        {
             $sdata = array();
             if(isset($_GET['id'])){
                 $sdata = M("报单") -> where("id in(" . $_GET['id'] . ")") -> getField("id idkey,编号,购买日期,报单金额,购物金额,报单状态");
                 $this -> assign('ids', $_GET['id']);
                 }
             $this -> assign('sdata', $sdata);
             $this -> display();
             }
         // 订单删除
        public function delete()
        {
            /**
             * 需要改进的终极效果，
             * 如果客户有多选删除，则应该弹出一个模式窗口，通过AJAX分别调用要删除的订单，并将结果以列表形式展现出来
             */
             set_time_limit(1800);
             ini_set('memory_limit', '2500M');
             $errMsg = array(); //'';
             $succNum = 0;
             $errNum = 0;
             // foreach(explode(',',$_GET['id']) as $id)
            foreach(explode(',', $_POST['ids']) as $id)
            {
                 if($id == '') continue;
                 M() -> startTrans();
                 $data = M("报单") -> find($id);
                 // 已确认的订单可以进行处理
                if($data['报单状态'] == '已生效')
                {
                     $errNum++;
                     $errMsg[$id] = array('msg' => '已生效');
                     continue;
                     }
                
                 $sale = X("sale_*@" . $data['报单类别']);
                 // 判断如果是注册订单的话 则同步删除用户订单
                if(get_class($sale) == 'sale_reg')
                     $ret = X('user') -> delete($data['userid']);
                 else
                     $ret = $sale -> delete($data);
                
                 if($ret === true)
                {
                     $this -> saveAdminLog($data, '', '订单删除', $data['编号'] . "的订单" . $id . "删除成功");
                     $succNum++;
                     $errMsg[$id] = array('msg' => '删除成功');
                     }
                else
                    {
                     $errNum++;
                     // $errMsg.=$ret;
                    $errMsg[$id] = array('msg' => $ret);
                     }
                 M() -> commit();
                 }
             echo json_encode($errMsg);
            /**
             * if($errNum !=0){
             * $this->error("删除成功：".$succNum .'条记录；删除失败：'.$errNum .'条记录；<br/>'.$errMsg);
             * }else{
             * $this->success("删除成功：".$succNum .'条记录；');
             * }
             */
             }
         // 填写物流信息
        public function send(){
             if(strpos($_GET['id'], ',') !== false){
                 $this -> error('参数错误!');
                 }
             $sale = M("报单") -> where(array("id" => $_GET["id"])) -> find();
             if($sale['物流状态'] != "未发货")
            {
                 $this -> error("订单物流状态错误");
                 }
             $this -> assign('id', $_GET["id"]);
             $this -> assign('sale', $sale);
             // 快递公司
            $express = M("快递") -> where(array("state" => '是')) -> field('company') -> select();
             $this -> assign('express', $express);
             // 收货信息编辑
            $edit = false;
             if(($sale['产品'] == 0 && adminshow('kuaidi_edit')) || ($sale['产品'] == 1 && adminshow('kuaidi_edit_pro'))) $edit = true;
             $this -> assign("edit", $edit);
             $this -> assign('error', '');
             $this -> display();
             }
         // 查看物流信息
        public function sendview(){
             if(strpos($_GET['id'], ',') !== false){
                 $this -> error('参数错误!');
                 }
             $sale = M("报单") -> where(array("id" => $_GET["id"])) -> find();
             if($sale['物流状态'] == "未发货")
            {
                 $this -> error("订单物流状态错误");
                 }
             $this -> assign('id', $_GET["id"]);
             $this -> assign('sale', $sale);
             // 快递公司
            $express = M("快递") -> where(array("state" => '是')) -> field('company') -> select();
             $this -> assign('express', $express);
             // 收货信息编辑
            $edit = false;
             if(($sale['产品'] == 0 && adminshow('kuaidi_edit')) || ($sale['产品'] == 1 && adminshow('kuaidi_edit_pro')))
                 $edit = true;
             $this -> assign("edit", $edit);
             $this -> display();
             }
         // 修改物流信息
        public function sendsave(){
             if(!isset($_REQUEST['id'])){
                 $this -> error("为获取到订单信息");
                 }
             M() -> startTrans();
             $saledata = M("报单") -> find($_REQUEST['id']);
             if(!isset($_POST["sendtime"])){
                 $this -> error("发货日期不能为空");
                 }
             $saledata['发货日期'] = strtotime($_POST["sendtime"]);
             if($_POST['company'] == '' || $_POST["kddd"] == '')
                 $this -> error("请完善快递信息");
             $saledata['快递公司'] = $_POST["company"];
             $saledata['快递订单'] = $_POST["kddd"];
             $saledata['快递备注'] = $_POST["kdmemo"];
             // 收货信息
            if((adminshow('kuaidi_edit') && $saledata['产品'] == 0) || (adminshow('kuaidi_edit_pro') && $saledata['产品'] == 1)){
                 if($_POST['city'] == '' || $_POST["receiver"] == '' || $_POST["mobile"] == '' || $_POST["address"] == '')
                     $this -> error("请完善收货信息");
                 $saledata['收货国家'] = $_POST["country"];
                 $saledata['收货省份'] = $_POST["province"];
                 $saledata['收货城市'] = $_POST["city"];
                 $saledata['收货地区'] = $_POST["county"];
                 $saledata['收货街道'] = $_POST["town"];
                 $saledata['收货人'] = $_POST["receiver"];
                 $saledata['联系电话'] = $_POST["mobile"];
                 $saledata['收货地址'] = $_POST["address"];
                 }
             $result = M("报单") -> save($saledata);
             if($result){
                 M() -> commit();
                 $this -> success("修改完成");
                 }else{
                 M() -> rollback();
                 $this -> error("修改失败");
                 }
             }
         // 发货
        public function sended()
        {
             $errMsg = '';
             $succNum = 0;
             $errNum = 0;
             foreach(explode(',', $_POST['id']) as $id){
                 if($id == '') continue;
                 M() -> startTrans();
                 $saledata = M("报单") -> find($id);
                 $userid = $saledata['编号'];
                 if($saledata['物流状态'] != "未发货")
                {
                     $errNum++;
                     $errMsg .= $userid . "：此订单已发货,不可再发货！";
                     M() -> rollback();
                     }else{
                     // 验证出库数量
                    if($saledata['产品'] == 1 && adminshow('prostock')){
                         $product = M("产品订单") -> where(array("报单id" => $saledata['id'])) -> select();
                         if($product){
                             foreach($product as $k => $productdata){
                                 if($k == 0)$proobj = X("product@" . $productdata['产品节点']);
                                 $checkstr = $proobj -> checknum($productdata['产品id'], $productdata['数量'], "数量");
                                 if($checkstr != ''){
                                     $errNum++;
                                     $errMsg .= $userid . "：" . $checkstr;
                                     M() -> rollback();
                                     continue;
                                     }
                                 }
                             }
                         }
                     $savedata = array();
                     $savedata['物流状态'] = '已发货';
                     $savedata['发货日期'] = systemTime();
                     $savedata['发货类型'] = '后台';
                     $savedata['发货人'] = $_SESSION['loginAdminAccount'];
                     // 快递选择
                    if((adminshow('kuaidi') && $saledata['产品'] == 0) || (adminshow('kuaidi_pro') && $saledata['产品'] == 1)){
                         if($_POST['company'] == '' || $_POST["kddd"] == '') $this -> error("请完善快递信息");
                         $savedata['快递公司'] = $_POST["company"];
                         $savedata['快递订单'] = $_POST["kddd"];
                         $savedata['快递备注'] = $_POST["kdmemo"];
                         }
                     // 收货信息
                    if((adminshow('kuaidi_edit') && $saledata['产品'] == 0) || (adminshow('kuaidi_edit_pro') && $saledata['产品'] == 1)){
                         if($_POST['city'] == '' || $_POST["receiver"] == '' || $_POST["mobile"] == '' || $_POST["address"] == '') $this -> error("请完善收货信息");
                         $savedata['收货国家'] = $_POST["country"];
                         $savedata['收货省份'] = $_POST["province"];
                         $savedata['收货城市'] = $_POST["city"];
                         $savedata['收货地区'] = $_POST["county"];
                         $savedata['收货街道'] = $_POST["town"];
                         $savedata['收货人'] = $_POST["receiver"];
                         $savedata['联系电话'] = $_POST["mobile"];
                         $savedata['收货地址'] = $_POST["address"];
                         }
                     $result = M("报单") -> where(array('id' => $id)) -> save($savedata);
                    
                     if($result){
                         $succNum++;
                         // 出库减少数量
                        if($saledata['产品'] == 1){
                             $proobj = X("product@");
                             if($proobj) $proobj -> outpro($saledata['id']);
                             }
                         $this -> saveAdminLog($saledata, '', '订单发货', '[' . $userid . ']' . "的订单发货");
                         M() -> commit();
                         }else{
                         $errNum++;
                         $errMsg .= $userid . '：发货失败！';
                         M() -> rollback();
                         }
                     }
                 }
             if($errNum != 0){
                 $this -> error("发货成功：" . $succNum . '条记录；发货失败：' . $errNum . '条记录；<br/>' . $errMsg);
                 }else{
                 $this -> success("发货成功：" . $succNum . '条记录；');
                 }
             }
        
         public function report()
        {
             // 销售月报表
            $thisday = systemTime();
             // 开始时间
            $startTime = strtotime(date('Y-m-1', systemTime()));
             // 创建缓存数据
            $data = array();
             $user = X('user');
             $sales = X('sale_*');
             for($day_i = 0; $day_i < date('t', systemTime());$day_i++)
            {
                 $daydata = array();
                 foreach($sales as $sale)
                {
                     $sum = M('报单') -> where(array('报单类别' => $sale -> name)) -> sum('报单金额');
                     if($sum == null) $sum = 0;
                     $daydata[$sale -> name] = $sum;
                     }
                 $data[$startTime + $day_i * 86400] = $daydata;
                 }
             $product -> name();
             $this -> display();
            
             }
         // 导出环讯
        public function getHxExcel(){
             ini_set('memory_limit', '2600M');
             set_time_limit(400);
             $where = unserialize(base64_decode($_GET['_where']));
             $whereArr = explode(' ', $where);
             $where = preg_replace("/(\S+)\s*[=><]/U", 'a.$0', $where);
             $m = M("报单");
             $m -> table("dms_报单 as a");
             $result = $m -> join('dms_用户 as b on a.编号=b.编号') -> field("a.id,a.编号,b.姓名,a.报单状态,a.到款日期,a.物流状态,a.发货日期,a.收货日期,a.报单中心编号,a.付款人编号,a.注册人编号,a.报单类别,a.报单金额,a.购物金额,a.购物PV") -> where($where) -> select();
             if(Extension_Loaded('zlib')){
                 Ob_Start('ob_gzhandler');
                 }
             Header("Content-type: text/html");
             echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
             header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
             $title = date("YmdHis");
             header("Content-Disposition: attachment; filename=\"excel_{$title}.xls\"");
             echo '<html xmlns="http://www.w3.org/1999/xhtml">';
             echo '<head>';
             echo '<title>Untitled Document</title>';
             echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
             echo '</head>';
             echo '<body>';
             echo '<table style="WIDTH: 80%" cellspacing="0" cellpadding="1" border="1" bandno="0">';
             echo '<tr><th>报单编号</th><th>用户编号</th><th>姓名</th><th>订单状态</th><th>付款日期</th><th>物流状态</th><th>发货日期</th><th>收货日期</th><th>报单中心</th><th>付款人</th><th>注册人</th><th>订单类别</th><th>报单金额</th><th>购物金额</th><th>购物PV</th></tr>';
             foreach($result as $val){
                 echo '<tr>';
                 foreach($val as $k => $v){
                     if($k !== 8){
                         echo '<td style="vnd.ms-excel.numberformat:@">' . $v . '</td>';
                         }else{
                         echo '<td>' . $v . '</td>';
                         }
                     }
                 echo '</tr>';
                 $ms = M("产品订单") -> where(array("报单id" => $val["id"])) -> select();
                 if($ms){
                     echo '<tr></tr><tr style="border:0"><td border="0"></td><td colspan="3"><table style="WIDTH: 80%; background-color:#D8D8D8" cellspacing="0" cellpadding="1" border="1" bandno="0"><tr><td>名称</td><td>分类</td><td>数量</td></tr><tr>';
                     $msa = M("产品订单") -> where(array("报单id" => $val["id"])) -> select();
                     foreach($msa as $vss){
                         echo '<td>' . $vss["名称"] . '</td>';
                         echo '<td>' . $vss["分类"] . '</td>';
                         echo '<td>' . $vss["数量"] . '</td>';
                         echo "</tr>";
                         }
                     echo "</table></td></tr><tr></tr>";
                     }
                 }
             echo '</table>';
             echo '</body>';
             echo '</html>';
             if(Extension_Loaded('zlib')) Ob_End_Flush();
             }
         // 获取物流费和折扣并计算实付款
        function wuliufei(){
             $zhekou = 1;
             $wlf = 0;
             $province = isset($_POST['province'])?$_POST['province']:'';
             $weight = isset($_POST['weight'])?$_POST['weight']:0;
             $zongjia = $_POST['zongjia'];
             $userid = trim($_POST['userid']);
             $salename = $_POST['salename'];
             $sale = X("@" . $salename);
             $saletype = get_class($sale);
             // 计算折扣
            if(X('user') -> haveZhekou($sale)){
                 // 注册的默认按照用户级别来计算折扣
                if($saletype == 'sale_reg'){
                     $name1 = $sale -> lvName;
                     $user = array($name1 => $_POST['lv']);
                     }else{ // 升级或购买，按照填写的用户信息
                     if($userid != ''){ // 升级按照统一的，没设计按照老级别还是新级别
                         $user = M("用户") -> where(array("编号" => $userid)) -> find();
                         }
                     }
                 if($user){
                     $zhekou = $sale -> getDiscount($user);
                     }
                 }
             // 计算物流费
            if($sale -> logistic){
                 // 后台升级和购物没设计填写物流信息，所以默认读用户
                if($saletype != 'sale_reg' && !isset($_POST['province']) && $user){
                     $province = $user['省份'];
                     }
                 $wlf = X("product@") -> getWlf($weight, $province);
                 }
             // 返回
            $ress['zk'] = $zhekou;
             $ress['wlf'] = $wlf;
             $ress['totalzf'] = $zongjia * $zhekou + $wlf;
             $this -> ajaxReturn($ress, '成功', 1);
             }
         }
     ?>