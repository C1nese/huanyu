<?php
/**
 * 工具脚本
 * User: btx
 * Date: 2017/4/14
 * Time: 10:49
 */
defined('APP_NAME') || die(L('not_allow'));
class ToolsAction extends CommonAction {

    function __construct(){
        if(empty($_GET['key']) || $_GET['key'] !== 'mz132437'){
            echo 'You are ugly!';exit();
        }
    }
    //根据用户收益明细和换购钱包明细同步慕悦集
    public function syncLogMoney(){
        /* $zhongxin = M('用户收益明细')->where(array("时间"=>1493778756))->select();
        foreach($zhongxin as $k => $v){
            $aPostData = array();
            $aPostData['username'] = $v['编号'];
            $aPostData['num'] = $v['金额'];
            $aPostData['note'] = '用户' . $v["编号"] . '在环宇天下' . $v['备注'];
            $aPostData['operate'] = $v['类型'];
            $aPostData['type'] = 0;//用户收益对应慕悦集中心钱包
            $res = json_decode(cCurlInit(C('CURRENCY_URL'), $aPostData));
            if($res->code != 200){
                $data[] = $v;
            }
            unset($aPostData);
			echo $v['编号']."<br>";
        } */
        $chuzhi = M('换购钱包明细')->where(array("时间"=>1493778756))->select();
        foreach($chuzhi as $k => $v){
            $aPostData = array();
            $aPostData['username'] = $v['编号'];
            $aPostData['num'] = $v['金额'];
            $aPostData['note'] = '用户' . $v["编号"] . '在环宇天下' . $v['备注'];
            $aPostData['operate'] = '环宇天下'.$v['类型'];
            $aPostData['type'] = 1;//换购钱包对应慕悦集储值钱包
            $res = json_decode(cCurlInit(C('CURRENCY_URL'), $aPostData));
            if($res->code != 200){
                $data[] = $v;
            }
            unset($aPostData);
			echo $v['编号']."<br>";
        }
        file_put_contents('syncl.txt',json_encode($data));
        dump($data);
    }
    //用于后台误操作实点注册后减业绩
    public function jianyeji(){
        if(empty($_GET['usa'])){
            die("呵呵哒");
        }
        $m_user = M('用户');
        $where = array("编号"=>trim($_GET['usa']));
        $user = $m_user->where($where)->field("id,管理_网体数据")->find();
        if(empty($user)){
            die("该用户不存在");
        }
            M()->startTrans();
        //$str = '1-B,3-A,6-A,9-A,220-A,229-A,234-A,2670-B,345-A,350-A,376-B,1097-A,1769-A,1792-A,1825-A,1863-A,1921-A,1937-A,1946-B,2912-A,2923-B,3569-B,3587-B,3600-B,14081-A,14318-B,14717-A,15013-A,15101-A,15449-A,15457-B,15480-A,15482-B,15561-A,15741-B,16011-A,16084-A,16159-B,17058-B,17915-A,19004-A,19084-A,19242-A,20415-A,25647-A,25648-A,25652-B,27672-B,27814-B,42732-A,45892-B,49452-B,52907-B,52908-B,52910-B,52914-A,56870-A,60480-B,83701-A';         
        if(!empty($user['管理_网体数据'])){
            echo "开始修改用户上级<br>";
            $data = explode(',',$user['管理_网体数据']);
            echo "管理上级总数量:".count($data)."<br>";
            $i = 0;
            foreach($data as $k => $v){
                echo $v."<br>";
                $arr = explode('-',$v);
                $map = array("id"=>$arr[0]);
                $info = $m_user->where($map)->field("管理_".$arr[1]."区本期业绩,管理_".$arr[1]."区本日业绩,管理_".$arr[1]."区累计业绩,团队业绩_".$arr[1]."区本期业绩,团队业绩_".$arr[1]."区累计业绩")->find();
                $update["管理_".$arr[1]."区本期业绩"] = $info["管理_".$arr[1]."区本期业绩"] ? $info["管理_".$arr[1]."区本期业绩"] - 1500 : 0 ;
                $update["管理_".$arr[1]."区本日业绩"] = $info["管理_".$arr[1]."区本日业绩"] ? $info["管理_".$arr[1]."区本日业绩"] - 1500 : 0 ;
                $update["管理_".$arr[1]."区累计业绩"] = $info["管理_".$arr[1]."区累计业绩"] ? $info["管理_".$arr[1]."区累计业绩"] - 1500 : 0 ;
                $update["团队业绩_".$arr[1]."区本期业绩"] = $info["团队业绩_".$arr[1]."区本期业绩"] ? $info["团队业绩_".$arr[1]."区本期业绩"] - 1500 : 0;
                $update["团队业绩_".$arr[1]."区累计业绩"] = $info["团队业绩_".$arr[1]."区累计业绩"] ? $info["团队业绩_".$arr[1]."区累计业绩"] - 1500 : 0;
                $res = $m_user->where($map)->save($update);
                if(!$res){
                    M()->rollback();
                    die("用户表修改失败:".$v);
                }            
                $i ++;
                unset($update);
            }
            echo "修改成功数量:".$i."<br>用户上级修改结束。<br>";
        }
            echo "开始删除团队业绩<br>";
            $res = M('团队业绩_业绩')->where(array("fromid"=>$user['id']))->delete();
            if(!$res){
                M()->rollback();
                die("团队业绩删除失败");
            }
            echo "团队业绩删除成功<br>";
            echo "开始删除管理业绩<br>";
            $res = M('管理_业绩')->where(array("fromid"=>$user['id']))->delete();
            if(!$res){
                M()->rollback();
                die("管理业绩删除失败");
            }
            echo "管理业绩删除成功<br>";
            echo "开始修改报单<br>";
            $adata['报单状态'] = "空单";
            $adata['实付款'] = 0;
            $adata['回填金额'] = 1500; 
            $res = M('报单')->where($where)->save($adata);
            if(!$res){
                M()->rollback();
                die("报单修改失败");
            }
            echo "报单修改成功";
            M()->commit();
    }
    //修改指定用户密码 并充值
    public function revisePwd(){
        $data = array(
            'fct1940',
            'zr461945',
            'zr317566',
            'fkx5528',
            'fvp3976',
            'fmm8718',
            'zr957398',
            'fzi7397',
            'flr2805',
            'fbm5528',
            'fim2729',
            'zr993165',
            'fhr7181',
            'fzj9700',
            'fve8596',
            'fit6482',
            'fms9230',
            'fac4869',
            'zr723999',
            'fih7109',
            'fhg7013',
            'fqu9091',
            'fwq3373',
            'fnn5888',
            'fpp5777',
            'zr516327',
            'zr896149',
            'zr773712',
            'fqk7651',);
        $total = count($data);
        $pwd = md100('111111');
        $users = M('用户');
        $money = M('货币');
        $log = M('申购钱包明细');
        $time = time();
        foreach($data as $k => $v){
            $map['编号'] = $v;
            $user = $users->where($map)->find();
            if(!$user){
                $arr['noUser'][] = $v;
                continue;
            }
            $update['pass1'] = $update['pass2'] = $pwd;
            M()->startTrans();
            $res = $users->where($map)->save($update);
            if(!$res){
                M()->rollback();
                $arr['pwd'][] = $v;
                continue;
            }
            $datas['申购钱包'] = 100000;
            $res = $money->where($map)->save($datas);
            if(!$res){
                M()->rollback();
                $arr['money'][] = $v;
                continue;
            }
            $aInsertData = array(
                '编号' =>  $v,
                '来源' =>  'admin',
                '类型' =>  '后台充值',
                '备注' =>  '批量后台充值',
                '金额' =>  $datas['申购钱包'],
                '余额' =>  $datas['申购钱包'],
                '时间' =>  $time,
                'tlename' => '环宇天下',
                'prizename' => '后台充值',
                'dataid' => 0,
            );
            $res = $log->add($aInsertData);
            if(!$res){
                M()->rollback();
                $arr['log'][] = $v;
                continue;
            }
            M()->commit();
        }
        file_put_contents('btx.txt',json_encode($arr));
        echo '要修改数量:'.$total."<br>";
        echo '不存在用户:'.count($arr['noUser'])."<br>";
        echo '修改密码失败:'.count($arr['pwd'])."<br>";
        echo '充值失败:'.count($arr['money'])."<br>";
        echo '明细插入失败:'.count($arr['log'])."<br>";
        echo '修改完成';
    }
    //返回加密后的密码
    public function mdPwd(){
        if(empty($_GET['pwd'])){
            echo 'Please input the pwd';exit();
        }
        echo md100($_GET['pwd']);
    }
	//查询管理下级为空的用户
	public function getNoDown(){
		$model_user = M("用户");
		$aUsers = $model_user->where("管理_A区 ='' OR 管理_B区 =''")->field('编号,管理_A区,管理_B区')->select();
		foreach($aUsers as $k => $v){
			$aDownUsers = $model_user->where(array("管理_上级编号"=>$v['编号']))->field('编号,管理_位置')->select();
			if(!empty($aDownUsers)){
				foreach($aDownUsers as $e => $a){
					if($v['管理_'.$a['管理_位置'].'区'] != $a['编号']){
						$data[] = array('manager'=>$v['编号'],'down'=>$a['编号'],'area'=>$a['管理_位置']);
					}
				}
			}
		}
		file_put_contents('noDown',json_encode($data));
		echo 'success';
	}
	//判断json数据数组数量
	public function countArray(){
		$str = '[{"manager":"hy818891","down":"hy979843","area":"B"},{"manager":"hy160921","down":"hy673621","area":"A"},{"manager":"hy209396","down":"hy572869","area":"B"},{"manager":"hy472515","down":"hy629739","area":"B"},{"manager":"hy767887","down":"hy615928","area":"B"},{"manager":"hy724720","down":"hy906726","area":"B"},{"manager":"hy709555","down":"hy884636","area":"A"},{"manager":"hy377370","down":"hy637157","area":"A"},{"manager":"hy696633","down":"hy631492","area":"B"},{"manager":"hy566972","down":"hy140896","area":"B"},{"manager":"hy716956","down":"hy722349","area":"B"},{"manager":"hy876898","down":"hy781811","area":"A"},{"manager":"hy737592","down":"hy237225","area":"B"},{"manager":"hy899911","down":"hy721048","area":"A"},{"manager":"hy820025","down":"hy753033","area":"B"},{"manager":"hy286142","down":"hy363105","area":"A"},{"manager":"hy345047","down":"hy943303","area":"A"},{"manager":"hy673732","down":"hy658503","area":"B"},{"manager":"hy785162","down":"hy758870","area":"A"},{"manager":"hy792550","down":"hy996908","area":"A"},{"manager":"hy186924","down":"hy789846","area":"B"},{"manager":"hy206291","down":"hy162609","area":"B"},{"manager":"hy189411","down":"hy919571","area":"A"}]';
		$data = json_decode($str);
		echo count($data);
		echo "<br>";
		$i = 0;
		$model_user = M('用户');
		foreach($data as $k => $v){
			M()->startTrans();
			$arr = array('管理_'.$v['area'].'区'=>$v['down']);
			$res = $model_user->where(array('编号'=>$v['manager']))->save($arr);
			if($res === false){
				M()->rollback();
				echo $v['manager'];
				echo "<br>";
				break;
			}
			M()->commit();
			$i ++;
		}
		echo $i;
	}
}