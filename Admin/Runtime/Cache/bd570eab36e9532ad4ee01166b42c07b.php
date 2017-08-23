<?php if (!defined('THINK_PATH')) exit();?><form action="__URL__/save" method="post" class="pageForm required-validate" onsubmit="return validateCallback(this, navTabAjaxDoneReload)">
<div layoutH="0">
	<table class="list" style="width:1000px">
		<thead>
		   <tr>
		   <th colspan="6" style="text-align:left;"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;系统功能开关设置</th>
		   </tr>
		</thead>
		<tbody>
			<tr>
				<td width="13%" style="font-weight:bold;color:#483D8B">模块</td>
				<td width="22%" style="font-weight:bold;">功能</td>
				<td width="15%" style="font-weight:bold;color:#D2691E">后台</td>
				<td width="10%" style="font-weight:bold;color:#FF8C00">前台</td>
				<td width="10%" style="font-weight:bold;color:#DEB887">客户控制</td>
				<td width="30%" style="font-weight:bold;color:red">说明</td>
			</tr>
			<tr>
				<td rowspan="11" style="font-weight:bold;color:#483D8B">会员管理</td>
				<td>空点回填(转实单|正)：</td>
				<td>
					回填功能开启<input type="checkbox" name="admin_backfill" value="1" <?php if(in_array('admin_backfill',$viewarr) == true): ?>checked<?php endif; ?>/><br />
					空点功能开启<input type="checkbox" name="admin_blank" value="1" <?php if(in_array('admin_blank',$viewarr) == true): ?>checked<?php endif; ?>/><br />
					升级选择回填<input type="checkbox" name="admin_up_backfill" value="1" <?php if(in_array('admin_up_backfill',$viewarr) == true): ?>checked<?php endif; ?>/><br />
					是否货币转正<input type="checkbox" name="admin_bank_backfill" value="1" <?php if(in_array('admin_bank_backfill',$viewarr) == true): ?>checked<?php endif; ?>/> <br /><font color='red'>(可提前扣货币)</font>
				</td>
				<td>货币转正菜单<input type="checkbox" name="user_bank_backfill" value="1" <?php if(in_array('user_bank_backfill',$viewarr) == true): ?>checked<?php endif; ?>/> <br /><font color='red'>(提前扣货币)</font></td>
				<td>&nbsp;</td>
				<td style="color:red;"><a href='http://sc.yidong.9885.net:84/index.php?s=/Nodelc/view/id/10' target="_blank">回填流程（点击）</a></td>
			</tr>
			<tr>
				
				<td>未激活会员分离显示：</td>
				<td>
					<input type="checkbox" name="user_noacc" value="1" <?php if(in_array('user_noacc',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">数据量大或客户要求，和正式会员分开，便于直接审核</td>
			</tr>
			<tr>
				<td>修改会员编号功能：</td>
				<td>
					<input type="checkbox" name="user_id" value="1" <?php if(in_array('user_id',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td style="color:red;">后台开启或关闭修改会员编号功能</td>
			</tr>
			<tr>
				<td>是否可以删除已结算会员：</td>
				<td>
					<input type="checkbox" name="deluser" value="1" <?php if(in_array('deluser',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td style="color:red;">后台允许或禁止删除已计算会员功能</td>
			</tr>
			<tr>
				<td>服务中心单独显示：</td>
				<td>
					<input type="checkbox" name="user_shop" value="1" <?php if(in_array('user_shop',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">数据量大或客户要求，和正式会员分开，便于直接审核</td>
			</tr>
			<tr>
				<td>是否修改奖金比例：</td>
				<td><input type="checkbox" name="jiangjin_bili" value="1" <?php if(in_array('jiangjin_bili',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>	
				<td>&nbsp;</td>	
				<td>&nbsp;</td>
			</tr>
		   <tr>
				<td>批量升级：</td>
				<td><input type="checkbox" name="piliangshengji" value="1" <?php if(in_array('piliangshengji',$viewarr) == true): ?>checked<?php endif; ?>/></td>	
				<td>&nbsp;</td>	
				<td>&nbsp;</td>	
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>是否需要推广链接：</td>
				<td>&nbsp;</td>	
				<td>
					<input type="checkbox" name="tj_tuiguang" value="1" <?php if(in_array('tj_tuiguang',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			
			<tr>
				<td>只有服务中心才可以报单：</td>
				<td>&nbsp;</td>
				<td><input type="checkbox" name="USER_SHOP_SALEONLY" value="1" <?php if($USER_SHOP_SALEONLY == 1): ?>checked<?php endif; ?>/></td>
				<td><input type="checkbox" name="SHOW_SHOPSET" value="1" <?php if($SHOW_SHOPSET == 1): ?>checked<?php endif; ?>/>
				</td>
				<td style="color:red;">系统中有服务中心功能才会生效</td>
			</tr>
			<tr>
				<td>强制会员下线：</td>
				<td><input type="checkbox" name="mustout" value="1" <?php if(in_array('mustout',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td style="color:red;">&nbsp;</td>
				<td style="color:red;">&nbsp;</td>
			</tr>
		
			<tr>
				<td>主页业绩统计显示：</td>
				<td><input type="checkbox" name="user_yeji" value="1" <?php if(in_array('user_yeji',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>	
				<td>&nbsp;</td>	
				<td style="color:red;">统计本日，周，月，年的业绩</td>
			</tr>
		</tbody>
		<tbody>
			<tr style="background-color:#fff">
				<td rowspan="10" style="font-weight:bold;">订单管理</td>
				<td>购物PV显示：</td>
				<td><input type="checkbox" class="show_pv" name="sale_pv" value="pv" <?php if(in_array('sale_pv',$viewarr) == true): ?>checked<?php endif; ?>></td>
				<td><input type="checkbox" name="sale_pv_head" value="1" <?php if(in_array('sale_pv_head',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>推广链接单独审核：</td>
				<td>
					<input type="checkbox" name="order_tuiguang" value="1" <?php if(in_array('order_tuiguang',$viewarr) == true): ?>checked<?php endif; ?>/>
				<td>&nbsp;</td>	
				</td>
				<td>&nbsp;</td>
				<td style="color:red;">和其他订单的审核分离，会员的推广链接功能需开启</td>
			</tr>
			<tr>
				<td>订单发货：</td>
				<td>无产品<input type="checkbox" name="baodan_wuliu" value="1" <?php if(in_array('baodan_wuliu',$viewarr) == true): ?>checked<?php endif; ?>/>--有产品<input type="checkbox" name="baodan_wuliu_pro" value="1" <?php if(in_array('baodan_wuliu_pro',$viewarr) == true): ?>checked<?php endif; ?>/></td>	
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>发货选择快递：</td>
				<td>无产品<input type="checkbox" name="kuaidi" value="1" <?php if(in_array('kuaidi',$viewarr) == true): ?>checked<?php endif; ?>/>--有产品<input type="checkbox" name="kuaidi_pro" value="1" <?php if(in_array('kuaidi_pro',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td  style="color:red;">‘订单发货’功能需开启</td>
			</tr>
			<tr>
				<td>发货时收货信息编辑：</td>
				<td>无产品<input type="checkbox" name="kuaidi_edit" value="1" <?php if(in_array('kuaidi_edit',$viewarr) == true): ?>checked<?php endif; ?>/>--有产品<input type="checkbox" name="kuaidi_edit_pro" value="1" <?php if(in_array('kuaidi_edit_pro',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td  style="color:red;">‘订单发货’功能需开启</td>
			</tr>
			<tr>
				<td>选择发货方式：</td>
				<td>&nbsp;</td>
				<td>
					<input type="checkbox" name="sale_sendtype" value="1" <?php if(in_array('sale_sendtype',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>	
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>

			<tr>
				<td>产品出入库：</td>
				<td>
					<input type="checkbox" name="prostock" value="1" <?php if(in_array('prostock',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td>&nbsp;</td>	
			</tr>
		</tbody>
		<tbody>
			<tr>
				<td rowspan="4" style="font-weight:bold;color:#483D8B">网络管理</td>
				<td>网络图拖拽效果：</td>
				<td>
					<input type="checkbox" name="is_treeimg" value="1" <?php if(in_array('is_treeimg',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
			<tr>
				<td>网络修改：</td>
				<td><input type="checkbox" name="edit_wangluo" value="1" <?php if(in_array('edit_wangluo',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
			<tr>
				<td>网络打印：</td>
				<td><input type="checkbox" name="edit_wangluoprint" value="1" <?php if(in_array('edit_wangluoprint',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
			<tr>
				<td>会员网络删除：</td>
				<td><input type="checkbox" name="user_downnetdel" value="1" <?php if(in_array('user_downnetdel',$viewarr) == true): ?>checked<?php endif; ?>/></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
		</tbody>
		<tbody>
			<tr style="background-color:#fff">
				<td rowspan="2" style="font-weight:bold;color:#483D8B">财务管理</td>
				<td>添加汇款是否选择银行图片：</td>
				<td>&nbsp;</td>
				<td>
					<input type="checkbox" name="bankset" value="1" <?php if(in_array('bankset',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
			<tr>
				<td>添加汇款方式：</td>
				<td>
					<input type="checkbox" name="huikuan" value="1" <?php if(in_array('huikuan',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
		</tbody>
		<tbody>
			<tr>
				<td rowspan="2" style="font-weight:bold;color:#483D8B">信息管理</td>
				<td>会员之间是否可以互发邮件：</td>
				<td>&nbsp;</td>
				<td>
					<input type="checkbox" name="mailset" value="1" <?php if(in_array('mailset',$viewarr) == true): ?>checked<?php endif; ?>/>
				</td>
				<td>&nbsp;</td>	
				<td style="color:red;">&nbsp;</td>
			</tr>
		</tbody>
		</table>
		<table class="list" style="width:600px">
		<thead>
		   <tr>
		   <th colspan="3" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;短信邮件在线支付开关</th>
		   </tr>
		</thead>
		<tbody>
			<tr>
				<td width="200">短信功能：</td>
				<td>
					<input type="checkbox" name="smsSwitch" value="1" <?php if(in_array('smsSwitch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
			<tr>
				<td>在线支付：</td>
				<td>
					<input type="checkbox" name="payOnlineSwitch" value="1" <?php if(in_array('payOnlineSwitch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
			<tr>
				<td>邮件发送：</td>
				<td>
					<input type="checkbox" name="emailSwitch" value="1" <?php if(in_array('emailSwitch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
		</tbody>
		</table>
		<table class="list" style="width:600px">
		<thead>
		   <tr>
		   <th colspan="2" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;密码相关设置</th>
		   </tr>
		</thead>
		<tbody>
				<tr>
				<td width="200">三级密码：</td>
				<td>
					<input type="checkbox" name="pwd3Switch" value="1" <?php if(in_array('pwd3Switch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
			<tr>
				<td>开启密码找回功能：</td>
				<td>
					<input type="checkbox" name="mimazhaohui" value="1" <?php if(in_array('mimazhaohui',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
			<tr>
				<td>密保问题功能：</td>
				<td>
					<input type="checkbox" name="mibao" value="1" <?php if(in_array('mibao',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
		</tbody>
		</table>
		<table class="list" style="width:600px">
		<thead>
		   <tr>
		   <th colspan="2" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;语言相关设置</th>
		   </tr>
		</thead>
		<tbody>
			<tr>
				<td width="200px">简繁切换：</td>
				<td>
					<input type="checkbox" name="languageSwitch" value="1" <?php if(in_array('languageSwitch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
	   </tbody>
	   	</table>
	   	<table class="list" style="width:600px">
		<thead>
		   <tr>
		   <th colspan="2" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;备份还原结算相关设置</th>
		   </tr>
		</thead>
		<tbody>
			<tr>
				<td width="200px">CLI模式：</td>
				<td>
					<input type="checkbox" name="cliSwitch" value="1" <?php if(in_array('cliSwitch',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
	   </tbody>
	   	</table>
		<table class="list" style="width:600px">
	   	 <thead>
		   <tr>
		   <th colspan="2" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;登陆设置</th>
		   </tr>
		</thead>
		<tbody>
			<tr>
				<td  width="200px">登录手机版：</td>
				<td>
					<input type="checkbox" name="phone_auto" value="1" <?php if(in_array('phone_auto',$viewarr) == true): ?>checked<?php endif; ?>/>开启
				</td>	
			</tr>
			<!--
			未发现ACTION处理代码
			<tr>
				<td width="200px">二维码登陆：</td>
				<td>
					<input type="checkbox" name="dl_qrcode" value="1" <?php if(in_array('dl_qrcode',$viewarr) == true): ?>checked<?php endif; ?>/>开启
					<span style="color:red;padding-left:10px;">(开启后，登陆页显示二维码图标)</span>
				</td>	
			</tr>
			-->
			</tbody>
	</table>
	<table class="list" style="width:600px">
		<thead>
		   <tr>
		   <th colspan="2" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;系统日志设置</th>
		   </tr>
		</thead>
		<tbody>
			 <tr>
				 <td>调试模式：</td>
				 <td>
					 <input type="checkbox" name="APP_DEBUG" value="1" <?php if($appDebug == true): ?>checked<?php endif; ?>/>开启
				 </td>	
			 </tr>
			 <tr>
				 <td>状态：</td>
				 <td>
					 <input type="checkbox" name="LOG_RECORD" value="1" <?php if($logRecord == true): ?>checked<?php endif; ?>/>开启
				 </td>	
			 </tr>
			 <tr>
				 <td width="200px" align='left' style="padding:5px 0">日志级别：</td>
				 <td width="400px" colspan="2" style="text-align:left;padding:5px 0">
					 <input type="checkbox" name="LOG_LEVEL[]" value="EMERG" 
					 <?php if(in_array('EMERG',$logLevelArr)): ?>checked<?php endif; ?>/>
					 严重错误，导致系统崩溃无法使用<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="ALERT" 
					 <?php if(in_array('ALERT',$logLevelArr)): ?>checked<?php endif; ?>/>
					 警戒性错误， 必须被立即修改的错误<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="CRIT" 
					 <?php if(in_array('CRIT',$logLevelArr)): ?>checked<?php endif; ?>/>
					 临界值错误， 超过临界值的错误<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="ERR" 
					 <?php if(in_array('ERR',$logLevelArr)): ?>checked<?php endif; ?>/>
					 一般性错误<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="WARN" 
					 <?php if(in_array('WARN',$logLevelArr)): ?>checked<?php endif; ?>/>
					 警告性错误， 需要发出警告的错误<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="NOTICE" 
					 <?php if(in_array('NOTICE',$logLevelArr)): ?>checked<?php endif; ?>/>
					 通知，程序可以运行但是还不够完美的错误<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="INFO" 
					 <?php if(in_array('INFO',$logLevelArr)): ?>checked<?php endif; ?>/>
					 信息，程序输出信息<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="DEBUG" 
					 <?php if(in_array('DEBUG',$logLevelArr)): ?>checked<?php endif; ?>/>
					 调试，用于调试信息<br/>
					 <input type="checkbox" name="LOG_LEVEL[]" value="SQL" 
					 <?php if(in_array('SQL',$logLevelArr)): ?>checked<?php endif; ?>/>
					 SQL语句，该级别只在调试模式开启时有效
				 </td>	
			 </tr>
		</tbody>
	</table>
	<table class="list" style="width:600px">
	<tr>
				 <td colspan="6" style="vertical-align: middle;padding:5px 0" >
				   <div class="buttonActive" style="margin-left:250px;">
						  <div class="buttonContent" >
							<button type="submit">确定</button>
						  </div>
					</div>
				 </td>
			</tr>
</table>

</div>
</form>