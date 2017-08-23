<?php
/**
 * 慕悦集同步接口
 * User: btx
 * Date: 2017/4/11
 * Time: 18:49
 */
defined('APP_NAME') || die(L('not_allow'));
class MyjAction extends CommonAction {
    //同步修改密码
    public function modPwd(){
        if(empty($_POST['username']) || (empty($_POST['loginPwd']) && empty($_POST['payPwd']))){
            $this->output('缺少参数');
        }
        $oUser = M('用户');
        $map = array("编号"=>trim($_POST['username']));
        $aUser = $oUser->where($map)->find();
        if(empty($aUser)){
            $this->output('用户不存在',200);
        }
        $aUpdate = array();
        if(!empty($_POST['loginPwd'])){
            $aUpdate['pass1'] = md100($_POST['loginPwd']);
        }
        if(!empty($_POST['payPwd'])){
            $aUpdate['pass2'] = md100($_POST['payPwd']);
        }
        $oUser->startTrans();
        $res = $oUser->where($map)->save($aUpdate);
        if(!$res){
            $oUser->rollback();
            $this->output('修改失败');
        }
        $oUser->commit();
        $this->output('修改成功',200);
    }
    /**
     * 数据返回统一方法
     * @param string $message   提示信息
     * @param int $code         提示编号
     */
    private function output($message = '',$code = 202){
        $res['code'] = $code;
        $res['message'] = $message;
        echo json_encode($res);exit();
    }
    /**
     * 接收慕悦集的货币同步
     * @param string    $username   编号
     * @param float     $money      金额 提现、扣款用负数
     * @param string    $mode       变动类型 例：用户提现、商城购物等
     * @param string    $memo       备注
     */
    public function modMoney(funbank $funbank){
        if(empty($_POST['username']) || empty($_POST['money']) || empty($_POST['mode']) || empty($_POST['memo'])){
            $this->output('缺少参数');
        }
        if(!is_numeric($_POST['money'])){
            $this->output('参数错误');
        }
        $oUser = M('用户');
        $where['编号'] = trim($_POST['username']);
        $aUserInfo = $oUser->where($where)->find();
        if(empty($aUserInfo)){
            $this->output('用户不存在',200);
        }
        $type = $funbank->byname;
        $oMoney = M('货币');
        $aMoneys = $oMoney->where($where)->find();
        if($aMoneys[$type] + $_POST['money'] < 0){
            $this->output('余额不足');
        }
        M()->startTrans();
        $res = $funbank->set($_POST['username'],$_POST['username'],$_POST['money'],$_POST['mode'],$_POST['memo'],'','',-1,0);
        if(!is_numeric($res)){//btx增加返回结果判断
            M()->rollback();
            $this->output('操作失败');
        }
        M()->commit();
        $this->output('成功',200);
    }
}