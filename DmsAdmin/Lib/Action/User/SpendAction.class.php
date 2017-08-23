<?php
defined('APP_NAME') || die(L('not_allow'));
class SpendAction extends CommonAction{

    private $authCode = "nimadashabi3721nidieyeshidashabi";

    public function _initialize() {
        if(empty($_GET['authCode']) || $_GET['authCode'] !== $this->authCode) {
            die(json_encode(array("code" => "1400001", "msg" => "AuthCode is wrong")));
        }
    }

    //慕悦集福家卡消费接口
    public function index(){
        if(empty($_GET['username'])) {
            die(json_encode(array("code" => "1400002", "msg" => "Username is null")));
        }
        $username = addslashes(trim(stripslashes($_GET['username'])));
        $coin = M("货币");
        $where = "编号='".$username."'";
        $customer = $coin->where($where)->find();
        if(empty($customer)){
            echo json_encode(array("code" => "1400005", "msg" => "User is not exists"));
        }
        if ($_GET['freeze'] != 0){
            $sql = "UPDATE dms_货币 SET 福家卡 = 福家卡 + ".$_GET['money'].", 福家卡冻结 = 福家卡冻结 + ".$_GET['freeze'].",time=".time()."  WHERE ".$where." LIMIT 1";
        }else {
            $sql = "UPDATE dms_货币 SET 福家卡 = 福家卡 + ".$_GET['money'].",time=".time()."   WHERE ".$where." LIMIT 1";
        }
        M()->startTrans();
        $res = M("")->execute($sql);
        if($res){
            $strs = empty($_GET["desc"]) ? "" : urldecode($_GET["desc"]);
            $money = $customer["福家卡"] + $_GET['money'];
            $data = array(
                "时间" => time(),
                "编号" => $username,
                "来源" => "",
                "类型" => "慕悦集消费",
                "金额" => $_GET['money'],
                "余额" => $money,
                "备注" => $strs,
                "tlename" => "",
                "prizename" => "",
                "dataid" => "-2",
                "adminuser" => ""
            );
            $ress = M("福家卡明细")->add($data);
            if($ress){
                M()->commit();
                echo json_encode(array("code" => "1400007", "msg" => "Operation Success"));
            }else{
                M()->rollback();
                echo json_encode(array("code" => "1400008", "msg" => "Operation Fail"));
            }
        }else{
            M()->rollback();
            echo json_encode(array("code" => "1400009", "msg" => "Operation error"));
        }
    }
    
	//福家卡消费明细
	public function detail(){
		if(empty($_GET["username"])){
			die(json_encode(array("code" => "1500002", "msg" => "Username is null")));
		}
        $username = addslashes(trim(stripslashes($_GET['username'])));
		$coin = M("福家卡明细");
        $where = "编号='".$username."'";
        $details = $coin->where($where)->order("时间 desc")->select();
		if(is_array($details) && count($details)){
			echo json_encode(array("code" => "1500003", "msg" => $details));
		}else{
			echo json_encode(array("code" => "1500004", "msg" => "No data"));
		}
	}
}