<?php
/*在自己小区下边排列
    本类由net_place的autoset方法进行调用，用于处理特定的排网算法
	automode="min"        表示一直找自己的小区的小区的小区...
	automode="min 1"      表示找自己小区一层（这个是一个非终止的寻找）
	automode="min 1 左"   表示找到自己的一个小区，然后按照固区一直找到末尾
*/
class net_place_fillavg
{
	static function run($net,$user,$big='')
	{
		//如果是大公排,直接查顶点用户并返回结果
		if($big != '')
		{
			$user = M('用户')->where(array($net->name.'_层数'=>1))->find();
			return self::fillavg($net,$user);
		}
		//判断如果这个用户不是由其他的排列模块处理过的，则需要从其推荐人开始找起
		if(!isset($user['this']) || !$user['this'])
		{
			if($net->fromNet == '')
			{
				throw_exception($net->name.'在automode中使用min时，需要指定fromNet属性，以便确定对应的推荐网络');
			}
			$user = M('用户')->where(array('编号'=>$user[$net->fromNet.'_上级编号']))->find();
			if(!$user)
			{
				throw_exception($net->name.'在automode中使用fill时没有找到对应的'.$net->fromNet.'上级');
			}
		}
		return self::fillavg($net,$user);
	}
	static function fillavg($net,$user)
	{
		$downs  = M('用户','dms_')->lock(true)->where($net->name. "_网体数据 like '".$user[$net->name."_网体数据"]."%' and id<>".$user['id'])->getField('编号,'.$net->name.'_网体数据');
		foreach($downs as &$down)
		{
			if(strpos($down,$upuser[$net->name."_网体数据"])===0)
			{
				$down=substr($down,strlen($upuser[$net->name."_网体数据"]));
			}
			$down = preg_replace( "/[0-9]+-/",'',$down);
			$down = str_replace( ',','',$down);
		}
		$downs=array_flip($downs);
		$i=1;
		while($i<100000)
		{
			$regs = self::getstr($net,$i);
			if(!isset($downs[$regs]))
			{
				if(mb_strlen($regs,'utf-8')==1)
				{
					return array($user['编号'],$regs);
				}
				else
				{
					return array($downs[mb_substr($regs,0,-1,'utf-8')],mb_substr($regs,-1,1,'utf-8'));
				}
			}
			$i++;
		}	
	}
		static function getlayer($id,$num)
		{
			$x=1;
			$y=1;
			$l=0;
			while($y<$id+1)
			{
				$x=$x*$num;
				$y+=$x;
				$l++;
			}
			return $l;
		}
		static function getlrnum($layer,$num)
		{
			$maxnum=pow($num,$layer)+(1-pow($num,$layer))/(1-$num)-1;
			$minnum=$maxnum/$num;
			return array($minnum,$maxnum);
		}
		static function getstr($net,$id)
		{
			$region = $net->getBranch();
			$num=count($region);
			//得到层数
			$l=self::getlayer($id,$num);
			//得到本层左右数
			$lr = self::getlrnum($l,$num);
			//本层左侧用1作为起点
			$id -= ($lr[0]-1);
			$lr[1]-=($lr[0]-1);
			$lr[0]=1;
			//计算每条线占本层数
			$langth=$lr[1]/$num;
			//计算末尾一位的数字
			$ret=$region[(int)floor(($id-1)/$langth)];
			//计算吗
			$ret2='';
			while($lr[1]>$num)
			{
				//设置最新封顶区域
				$lr[1]=$langth;
				//设置新ID
				$id-=$langth*(int)floor(($id-1)/$langth);
				//设置新边长
				$langth=$lr[1]/$num;
				$ret2=$region[(int)floor(($id-1)/$langth)].$ret2;
			}
			return $ret2.$ret;
		}
}
?>