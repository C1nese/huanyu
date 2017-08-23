<?php if (!defined('THINK_PATH')) exit();?><form action="__URL__/sysupdate" method="post" class="pageForm required-validate" onsubmit="return validateCallback(this, navTabAjaxDoneReload)">
<div layoutH="0">
<table class="list" style="width:700px">
<thead>
   <tr>
   <th colspan="4" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;前台模版标题显示</th>
   </tr>
   </thead>
   <tbody>
 <tr>
     <td width="120px" >标题文字:</td>
     <td colspan="3" style="text-align:left">
	  <input type="text" name="SYSTEM_TITLE" id="inpp" value="<?php echo ($SYSTEM_TITLE); ?>" style="width:300px;" />
      </td>
</tr>
<tr>
     <td width="120px" >公司名称:</td>
     <td colspan="3" style="text-align:left">
	  <input type="text" name="SYSTEM_COMPANY" id="inpp" value="<?php echo ($SYSTEM_COMPANY); ?>" style="width:300px;" />
      </td>
</tr>
<tr>
     <td width="120px" >公司副标题:</td>
     <td colspan="3" style="text-align:left">
	  <input type="text" name="SYSTEM_MEMO" id="inpp" value="<?php echo ($SYSTEM_MEMO); ?>" style="width:300px;" />
      </td>
</tr>
</tbody>
<?php if($Complete == true): ?><thead>
   <tr>
   <th colspan="4" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;QQ在线客服</th>
   </tr>
   </thead>
   <tbody>
   <tr>
     	<td width="120px" >客服QQ:</td>
     	<td colspan="3" style="text-align:left">
	 		<select name="TYPE_QQ" id="TYPE_QQ">
	 		<option value="0" <?php if(($TYPE_QQ) == "0"): ?>selected<?php endif; ?>>普通QQ</option>
	 		<option value="1" <?php if(($TYPE_QQ) == "1"): ?>selected<?php endif; ?>>营销QQ</option>
	 		</select>
     	</td>
	</tr>
	<tr id="SERVICE_QQ_show_0" class="SERVICE_QQ_show" <?php if($TYPE_QQ==1): ?>style="display:none;"<?php endif; ?>>
		<td width="120px" >&nbsp;</td>
		<td colspan="3" style="text-align:left">
			<input type="text" name="SERVICE_QQ_0" id="SERVICE_QQ_0" value="<?php echo ($SERVICE_QQ_0); ?>" style="width:300px;" /> 多个请用英文逗号','分隔
		</td>
	</tr>
	<tr id="SERVICE_QQ_show_1" class="SERVICE_QQ_show" <?php if($TYPE_QQ == 0 || $TYPE_QQ == ''): ?>style="display:none;"<?php endif; ?>>
		<td width="120px" >&nbsp;</td>
		<td colspan="3" style="text-align:left">
			<textarea name="SERVICE_QQ_1" id="SERVICE_QQ_1" style="width:555px;height:120px;resize: none;"><?php echo ($SERVICE_QQ_1); ?></textarea><a href="" target="_blank">生成代码流程</a>（友情提示：生成的图标不要被页面覆盖）
		</td>
	</tr>
</tbody><?php endif; ?>
<script>
$(function(){
	$('#TYPE_QQ').change(function(){
		var id="SERVICE_QQ_show_"+$('#TYPE_QQ').val();
		$('.SERVICE_QQ_show').hide();
		$('#'+id).show();
	});
})
</script>
<?php if($Complete == true): ?><thead>
   <tr>
   <th colspan="4" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;系统维护</th>
   </tr>
   </thead>
   <tbody>
 <tr>
     <td width="120px" >前台不能登入提示:</td>
     <td colspan="3" style="text-align:left">
	  <input type="text" name="SYSTEM_CLOSE_TITLE" id="SYSTEM_CLOSE_TITLE" value="<?php echo ($SYSTEM_CLOSE_TITLE); ?>"  style="width:300px;" /> 
      </td>
</tr>
</tbody><?php endif; ?>

<thead>
   <tr>
   <th colspan="4" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;系统状态设置</th>
   </tr>
   </thead>
   <tbody>
   
<?php if($Complete == true): ?><tr>
     <td width="120px" >选择当前的系统状态:</td>
     <td colspan="3" style="text-align:left">
		<select name="SYSTEM_STATE">
		<option value="1" <?php if(($SYSTEM_STATE) == "1"): ?>selected<?php endif; ?>>正常</option>
		<option value="2" <?php if(($SYSTEM_STATE) == "2"): ?>selected<?php endif; ?>>维护</option>
		<option value="3" <?php if(($SYSTEM_STATE) == "3"): ?>selected<?php endif; ?>>无法访问</option>
		</select></td>
</tr><?php endif; ?>
 <tr>
     <td width="120px" >开放时间:</td>
     <td colspan="3" style="text-align:center">
		<?php $weeks = array('周日','周一','周二','周三','周四','周五','周六'); ?>
		<?php if(is_array($weeks)): foreach($weeks as $key=>$week): echo ($week); ?>
			<select name="startOpenTime[]">
				<?php $__FOR_START__=0;$__FOR_END__=25;for($i=$__FOR_START__;$i < $__FOR_END__;$i+=1){ ?><option value="<?php echo ($i); ?>" <?php if($startOpenTime[$key] == $i): ?>selected<?php endif; ?>><?php echo ($i); ?>:00</option><?php } ?>
			</select>  - 
			<select name="endOpenTime[]">
				<?php $__FOR_START__=24;$__FOR_END__=0;for($i=$__FOR_START__;$i > $__FOR_END__;$i+=-1){ ?><option value="<?php echo ($i); ?>" <?php if($endOpenTime[$key] == $i): ?>selected<?php endif; ?>><?php echo ($i); ?>:00</option><?php } ?>
			</select><br/><?php endforeach; endif; ?>
	</td>
</tr>
</tbody>
</table>
<table class="list" style="width:700px">
  <thead>
    <tr>
     <th  colspan="7" style="text-align:left"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;前台数据设置</th>
    </tr>
  </thead>
  <tbody>
  <tr>
	<td width="100px">项目:</td>
	<td width="100px">注册可见项:</td>
	<td width="100px">注册必选项:</td>
    <td width="100px">前台可查看项:</td>
    <td width="100px">前台可修改项:</td>
    <td width="100px">唯一</td>
    <td width="100px">真实性</td>
  </tr>
  <tr>
	<td>姓名:</td>
	<td id="che"><input class="inp1" type="checkbox" name="show_name" value="name" <?php if(in_array('name',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input type="checkbox" class="show_name" name="require_name" value="name"  <?php if(in_array('name',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input type="checkbox" class="show_name" name="view_name" value="name" <?php if(in_array('name',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input type="checkbox" class="show_name" name="edit_name" value="name" <?php if(in_array('name',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>性别:</td>
	<td><input class="inp1" type="checkbox" name="show_sex" value="sex" <?php if(in_array('sex',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_sex" type="checkbox" name="require_sex" value="sex" <?php if(in_array('sex',$user['require']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_sex" type="checkbox" name="view_sex" value="sex" <?php if(in_array('sex',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_sex" type="checkbox" name="edit_sex" value="sex" <?php if(in_array('sex',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>昵称:</td>
	<td><input class="inp1" type="checkbox" name="show_alias" value="alias" <?php if(in_array('alias',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_alias" type="checkbox" name="require_alias" value="alias" <?php if(in_array('alias',$user['require']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_alias" type="checkbox" name="view_alias" value="alias" <?php if(in_array('alias',$user['view']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_alias" type="checkbox" name="edit_alias" value="alias" <?php if(in_array('alias',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>收货人:</td>
	<td><input class="inp1" type="checkbox" name="show_reciver" value="reciver" <?php if(in_array('reciver',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_reciver" type="checkbox" name="require_reciver" value="reciver" <?php if(in_array('reciver',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_reciver" type="checkbox" name="view_reciver" value="reciver" <?php if(in_array('reciver',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_reciver" type="checkbox" name="edit_reciver" value="reciver" <?php if(in_array('reciver',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>QQ:</td>
	<td><input class="inp1" type="checkbox" name="show_qq" value="qq" <?php if(in_array('qq',$user['show']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_qq" type="checkbox" name="require_qq" value="qq"  <?php if(in_array('qq',$user['require']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_qq" type="checkbox" name="view_qq" value="qq"  <?php if(in_array('qq',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_qq" type="checkbox" name="edit_qq" value="qq"  <?php if(in_array('qq',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>Email:</td>
	<td><input class="inp1" type="checkbox" name="show_email" value="email"  <?php if(in_array('email',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_email" type="checkbox" name="require_email" value="email" <?php if(in_array('email',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_email" type="checkbox" name="view_email" value="email" <?php if(in_array('email',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_email" type="checkbox" name="edit_email" value="email" <?php if(in_array('email',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>国家代码:</td>
	<td><input class="inp1" type="checkbox" name="show_country_code" value="country_code"                 <?php if(in_array('country_code',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_country_code" type="checkbox" name="require_country_code" value="country_code" <?php if(in_array('country_code',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_country_code" type="checkbox" name="view_country_code" value="country_code"    <?php if(in_array('country_code',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_country_code" type="checkbox" name="edit_country_code" value="country_code"    <?php if(in_array('country_code',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td>
	</td>
	<td></td>
	</tr>
	<tr>
	<td>移动电话:</td>
	<td><input class="inp1" type="checkbox" name="show_mobile" value="mobile" <?php if(in_array('mobile',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_mobile" type="checkbox" name="require_mobile" value="mobile" <?php if(in_array('mobile',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_mobile" type="checkbox" name="view_mobile" value="mobile" <?php if(in_array('mobile',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_mobile" type="checkbox" name="edit_mobile" value="mobile" <?php if(in_array('mobile',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td>
	<select name='only_mobile'>
		<option value='0' <?php if(($user["onlyMobile"]) == "0"): ?>selected<?php endif; ?>>不限</option>
		<option value='1' <?php if(($user["onlyMobile"]) == "1"): ?>selected<?php endif; ?>>1人</option>
		<option value='2' <?php if(($user["onlyMobile"]) == "2"): ?>selected<?php endif; ?>>2人</option>
		<option value='3' <?php if(($user["onlyMobile"]) == "3"): ?>selected<?php endif; ?>>3人</option>
		<option value='4' <?php if(($user["onlyMobile"]) == "4"): ?>selected<?php endif; ?>>4人</option>
		<option value='5' <?php if(($user["onlyMobile"]) == "5"): ?>selected<?php endif; ?>>5人</option>
		<option value='6' <?php if(($user["onlyMobile"]) == "6"): ?>selected<?php endif; ?>>6人</option>
		<option value='7' <?php if(($user["onlyMobile"]) == "7"): ?>selected<?php endif; ?>>7人</option>
		<option value='8' <?php if(($user["onlyMobile"]) == "8"): ?>selected<?php endif; ?>>8人</option>
		<option value='9' <?php if(($user["onlyMobile"]) == "9"): ?>selected<?php endif; ?>>9人</option>
		<option value='10' <?php if(($user["onlyMobile"]) == "10"): ?>selected<?php endif; ?>>10人</option>
	</select>
	</td>
	<td></td>
	</tr>
	<tr>
	<td>证件号码:</td>
	<td><input class="inp1" type="checkbox" name="show_id_card" value="id_card" <?php if(in_array('id_card',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_id_card" type="checkbox" name="require_id_card" value="id_card" <?php if(in_array('id_card',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_id_card" type="checkbox" name="view_id_card" value="id_card" <?php if(in_array('id_card',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_id_card" type="checkbox" name="edit_id_card" value="id_card" <?php if(in_array('id_card',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td>
	<select name='only_id_card'>
		<option value='0' <?php if(($user["onlyIdCard"]) == "0"): ?>selected<?php endif; ?>>不限</option>
		<option value='1' <?php if(($user["onlyIdCard"]) == "1"): ?>selected<?php endif; ?>>1人</option>
		<option value='2' <?php if(($user["onlyIdCard"]) == "2"): ?>selected<?php endif; ?>>2人</option>
		<option value='3' <?php if(($user["onlyIdCard"]) == "3"): ?>selected<?php endif; ?>>3人</option>
		<option value='4' <?php if(($user["onlyIdCard"]) == "4"): ?>selected<?php endif; ?>>4人</option>
		<option value='5' <?php if(($user["onlyIdCard"]) == "5"): ?>selected<?php endif; ?>>5人</option>
		<option value='6' <?php if(($user["onlyIdCard"]) == "6"): ?>selected<?php endif; ?>>6人</option>
		<option value='7' <?php if(($user["onlyIdCard"]) == "7"): ?>selected<?php endif; ?>>7人</option>
		<option value='8' <?php if(($user["onlyIdCard"]) == "8"): ?>selected<?php endif; ?>>8人</option>
		<option value='9' <?php if(($user["onlyIdCard"]) == "9"): ?>selected<?php endif; ?>>9人</option>
		<option value='10' <?php if(($user["onlyIdCard"]) == "10"): ?>selected<?php endif; ?>>10人</option>
	</select>
	</td>
	<td><input class="show_id_card" type="checkbox" name="truth_id_card" value="id_card" <?php if(in_array('id_card',$user['truth']) == true): ?>checked<?php endif; ?>></td>
	</tr>
	<tr>
	<td>地址:</td>
	<td><input class="inp1" type="checkbox" name="show_address" value="address" <?php if(in_array('address',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_address" type="checkbox" name="require_address" value="address" <?php if(in_array('address',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_address" type="checkbox" name="view_address" value="address"  <?php if(in_array('address',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_address" type="checkbox" name="edit_address" value="address"  <?php if(in_array('address',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>银行卡号:</td>
	<td><input class="inp1" type="checkbox" name="show_bank_card" value="bank_card" <?php if(in_array('bank_card',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_card" type="checkbox" name="require_bank_card" value="bank_card" <?php if(in_array('bank_card',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_card" type="checkbox" name="view_bank_card" value="bank_card" <?php if(in_array('bank_card',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_card" type="checkbox" name="edit_bank_card" value="bank_card" <?php if(in_array('bank_card',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td>
	<select name='only_bank_card'>
		<option value='0' <?php if(($user["onlyBankCard"]) == "0"): ?>selected<?php endif; ?>>不限</option>
		<option value='1' <?php if(($user["onlyBankCard"]) == "1"): ?>selected<?php endif; ?>>1人</option>
		<option value='2' <?php if(($user["onlyBankCard"]) == "2"): ?>selected<?php endif; ?>>2人</option>
		<option value='3' <?php if(($user["onlyBankCard"]) == "3"): ?>selected<?php endif; ?>>3人</option>
		<option value='4' <?php if(($user["onlyBankCard"]) == "4"): ?>selected<?php endif; ?>>4人</option>
		<option value='5' <?php if(($user["onlyBankCard"]) == "5"): ?>selected<?php endif; ?>>5人</option>
		<option value='6' <?php if(($user["onlyBankCard"]) == "6"): ?>selected<?php endif; ?>>6人</option>
		<option value='7' <?php if(($user["onlyBankCard"]) == "7"): ?>selected<?php endif; ?>>7人</option>
		<option value='8' <?php if(($user["onlyBankCard"]) == "8"): ?>selected<?php endif; ?>>8人</option>
		<option value='9' <?php if(($user["onlyBankCard"]) == "9"): ?>selected<?php endif; ?>>9人</option>
		<option value='10' <?php if(($user["onlyBankCard"]) == "10"): ?>selected<?php endif; ?>>10人</option>
	</select>
	</td>
	<td></td>
	</tr>
	<tr>
	<td>开户名:</td>
	<td><input class="inp1" type="checkbox" name="show_bank_name" value="bank_name"  <?php if(in_array('bank_name',$user['show']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_bank_name" type="checkbox" name="require_bank_name" value="bank_name" <?php if(in_array('bank_name',$user['require']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_bank_name" type="checkbox" name="view_bank_name" value="bank_name" <?php if(in_array('bank_name',$user['view']) == true): ?>checked<?php endif; ?> ></td>
	<td><input class="show_bank_name" type="checkbox" name="edit_bank_name" value="bank_name" <?php if(in_array('bank_name',$user['edit']) == true): ?>checked<?php endif; ?> ></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>开户地址:</td>
	<td><input class="inp1" type="checkbox" name="show_bank_apply_addr" value="bank_apply_addr" <?php if(in_array('bank_apply_addr',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_addr" type="checkbox" name="require_bank_apply_addr" value="bank_apply_addr" <?php if(in_array('bank_apply_addr',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_addr" type="checkbox" name="view_bank_apply_addr" value="bank_apply_addr" <?php if(in_array('bank_apply_addr',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_addr" type="checkbox" name="edit_bank_apply_addr" value="bank_apply_addr" <?php if(in_array('bank_apply_addr',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>开户银行:</td>
	<td><input class="inp1" type="checkbox" name="show_bank_apply_name" value="bank_apply_name" <?php if(in_array('bank_apply_name',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_name" type="checkbox" name="require_bank_apply_name" value="bank_apply_name" <?php if(in_array('bank_apply_name',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_name" type="checkbox" name="view_bank_apply_name" value="bank_apply_name" <?php if(in_array('bank_apply_name',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_bank_apply_name" type="checkbox" name="edit_bank_apply_name" value="bank_apply_name" <?php if(in_array('bank_apply_name',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>省市区域:</td>
	<td><input class="inp1" type="checkbox" name="show_area" value="area" <?php if(in_array('area',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_area" type="checkbox" name="require_area" value="area" <?php if(in_array('area',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_area" type="checkbox" name="view_area" value="area" <?php if(in_array('area',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_area" type="checkbox" name="edit_area" value="area" <?php if(in_array('area',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>注册时间:</td>
	<td></td>
	<td></td>
	<td><input class="show_time" type="checkbox" name="view_time" value="time" <?php if(in_array('time',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>一级密码:</td>
	<td><input class="inp1" type="checkbox" name="show_pass1" value="pass1" <?php if(in_array('pass1',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass1" type="checkbox" name="require_pass1" value="pass1" <?php if(in_array('pass1',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>一级密码确认:</td>
	<td><input class="inp1" type="checkbox" name="show_pass1c" value="pass1c" <?php if(in_array('pass1c',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass1c" type="checkbox" name="require_pass1c" value="pass1c" <?php if(in_array('pass1c',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>二级密码:</td>
	<td><input class="inp1" type="checkbox" name="show_pass2" value="pass2" <?php if(in_array('pass2',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass2" type="checkbox" name="require_pass2" value="pass2" <?php if(in_array('pass2',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>二级密码确认:</td>
	<td><input class="inp1" type="checkbox" name="show_pass2c" value="pass2c" <?php if(in_array('pass2c',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass2c" type="checkbox" name="require_pass2c" value="pass2c" <?php if(in_array('pass2c',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<?php if(($pwd3Switch) == "true"): ?><tr>
	<td>三级密码:</td>
	<td><input class="inp1" type="checkbox" name="show_pass3" value="pass3" <?php if(in_array('pass3',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass3" type="checkbox" name="require_pass3" value="pass3" <?php if(in_array('pass3',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<tr>
	<td>三级密码确认:</td>
	<td><input class="inp1" type="checkbox" name="show_pass3c" value="pass3c" <?php if(in_array('pass3c',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_pass3c" type="checkbox" name="require_pass3c" value="pass3c" <?php if(in_array('pass3c',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr><?php endif; ?>
	<tr>
	<td>微信号:</td>
	<td><input class="inp1" type="checkbox" name="show_weixin" value="weixin"  <?php if(in_array('weixin',$user['show']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_weixin" type="checkbox" name="require_weixin" value="weixin" <?php if(in_array('weixin',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_weixin" type="checkbox" name="view_weixin" value="weixin" <?php if(in_array('weixin',$user['view']) == true): ?>checked<?php endif; ?>></td>
	<td><input class="show_weixin" type="checkbox" name="edit_weixin" value="weixin" <?php if(in_array('weixin',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	</tr>
	<?php if($shop == true): ?><tr>
	<td>服务中心:</td>
	<td><input class="inp1" type="checkbox" name="show_shop"  value="shop"  checked ></td>
	<td><input class="show_shop" type="checkbox" name="require_shop" value="shop" <?php if(in_array('shop',$user['require']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	<td></td>
	<td></td>
	</tr><?php endif; ?>
	
	<tr>
	<td>头像:</td>
	<td></td>
	<td></td>
	<td></td>
	<td><input class="show_headimg" type="checkbox" name="edit_headimg" value="headimg" <?php if(in_array('headimg',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	
	<tr>
	<td>个性签名:</td>
	<td></td>
	<td></td>
	<td></td>
	<td><input class="show_qian" type="checkbox" name="edit_qian" value="qian" <?php if(in_array('qian',$user['edit']) == true): ?>checked<?php endif; ?>></td>
	<td></td>
	<td></td>
	</tr>
	
	
	<?php if($Complete == true): ?><tr>
		<td>编号生成规则设置:</td>
		<td colspan="6" style="text-align:left">
		<p><input type="checkbox" name="idEdit" value="1" <?php if($user["idEdit"] == true): ?>checked<?php endif; ?>>编 辑<span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>在编辑状态下,用户编号可自行修改填写</span></p>
		<p><input type="checkbox" name="idAutoEdit" value="1" <?php if($user["idAutoEdit"] == true): ?>checked<?php endif; ?>>自 动<span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>系统内部自动产生用户注册编号</span></p>
		<p><input type="checkbox" name="idInDate" value="1" <?php if($user["idInDate"] == true): ?>checked<?php endif; ?>>日 期<span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>若自动获取用户编号时于自动获取的数字前面加上当天年月日</span></p>
		<p><input type="checkbox" name="idRand" value="1" <?php if($user["idRand"] == true): ?>checked<?php endif; ?>>随 机<span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>若自动获取用户编号时会出现随机不规则排列的数字</span></p>
		<p>序 号:&nbsp&nbsp<input style="width:50px;" type="text" id="inpp" name="idSerial" value="<?php echo ($user["idSerial"]); ?>"><span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>自动而不随机则根据此数字与位数形成注册编号</span></p>
		<p>前 缀:&nbsp&nbsp<input style="width:50px;" type="text" id="inpp" name="idPrefix" value="<?php echo ($user["idPrefix"]); ?>">
		位 数:&nbsp&nbsp<input style="width:50px" type="text" id="inpp" name="idLength" value="<?php echo ($user["idLength"]); ?>"><span><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>自动获取时产生前缀+设定位数数字</span></p>
		<p><span style="color:red"><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>特别说明：若选择日期，则建议位数不为空值或选择编辑；不选择任何默认自行填写</span></p>
		<p><span style="color:red"><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>特别说明：自动不编辑获取编号注册时以注册后的编号为准</span></p>
		</td>
	</tr><?php endif; ?>
	<tr>
		<td>默认密码设置</td>
		<td colspan="6" style="text-align:left">
			<p>一级密码：<input name="DEFAULT_USER_PASS1" value="<?php echo ($DEFAULT_USER_PASS1); ?>"></p>
			<p>二级密码：<input name="DEFAULT_USER_PASS2" value="<?php echo ($DEFAULT_USER_PASS2); ?>"></p>
			<?php if(($pwd3Switch) == "true"): ?><p>三级密码：<input name="DEFAULT_USER_PASS3" value="<?php echo ($DEFAULT_USER_PASS3); ?>"></p><?php endif; ?>
			<p><span style="color:red"><img src="__PUBLIC__/Images/notice.png" style="vertical-align:middle"/>密码未填写状态下，未设置状态下注册默认为编号</span></p>
		</td>
	</tr>
</table>


<table class="list" style="width:700px">
<?php if($Complete == true): ?><thead>
    <tr>
     <th  style="text-align:left" colspan="5"><img style="vertical-align:middle" src="__PUBLIC__/Images/cog.png" />&nbsp;&nbsp;用户安全设置</th>
    </tr>
	</thead>
    <tbody>
	<tr>
		<td width="150px" colspan='2'>前台登陆验证码:</td>
		<td colspan="4" style="text-align:left">
		<select name="USER_LOGIN_VERIFY">
		<option value="0" <?php if($USER_LOGIN_VERIFY == 0): ?>selected<?php endif; ?>>关闭</option>
		<option value="1" <?php if($USER_LOGIN_VERIFY == 1): ?>selected<?php endif; ?>>登陆失败三次后自动开启</option>
		<option value="2" <?php if($USER_LOGIN_VERIFY == 2): ?>selected<?php endif; ?>>一直开启</option>
		</select>
		</td>
	</tr>
	</tbody><?php endif; ?>
    
	<tr>
	 <td colspan="6" style="vertical-align: middle;" >
	   <div class="buttonActive" style="margin-left:290px;">
			  <div class="buttonContent" >
				<button type="submit">确定</button>
			  </div>
		</div>
	 </td>
	</tr> 
   </tbody> 
   
</table>
</div>
<script>
$(".inp1",navTab.getCurrentPanel()).each(function(j,m){
	if(this.checked){
	}else{
	  var name=this.name;
	  var my=this;
	  $("."+name,navTab.getCurrentPanel()).each(function(i,n){
	  if(my.checked){
			$(this).attr('disabled',false);
		}else{
			$(this).attr('disabled',true);
		} });
	  }
});
$(".inp1",navTab.getCurrentPanel()).click(function(){
	var my=this;
	var name=$(this).attr('name');
	$("."+name,navTab.getCurrentPanel()).each(function(i,n){
		if(my.checked){
			$(this).attr('disabled',false);
		}else{
			$(this).attr('disabled',true);
		}   
	});
	});
	
    function openaddfun(){
        var chk_value = "";
        $("input[name='openweek']:checked").each(function(){    
           chk_value +=$(this).val()+',';
        });
        chk_value = chk_value.substr(0,chk_value.lastIndexOf(','));
        var startDate = $("input[name='startopenDate']").val();
        var endDate =  $("input[name='endopenDate']").val();
        if(startDate!=""||endDate!=""){
            if(startDate==""){startDate="任意时间"}
            if(endDate==""){endDate="以后"}
            newOPtion =startDate+'至'+endDate+'   '+chk_value;
        }else{
            newOPtion =chk_value;
        }
        if(newOPtion == ""){
            alertMsg.error('请选择添加范围！');
        }else{
            var value=startDate+';'+endDate+';'+chk_value;
            if($("#opendateSet option[value='"+value+"']").length>0){
                alertMsg.error('该数据已经存在！');
            }else{
                $("#opendateSet").append('<option value="'+value+'">'+newOPtion+'</option>');
                $("input[name='openweek']").attr("checked",false);
                $("input[name='startopenDate']").val("");
                $("input[name='endopenDate']").val("");
            }
            var opendateValue="";
            $("#opendateSet option").each(function(){
                opendateValue += $(this).val()+'|';
            });
            $("input[name='opendateValue']").val(opendateValue);
        }
        
    }
    function opendelfun(){
        var removeOption = $("#opendateSet").val();
        if(removeOption==null){
            alertMsg.error('请先选择要删除的数据！');
        }else{
            $("#opendateSet option:selected").remove();
            var opendateValue="";
            $("#opendateSet option").each(function(){
                opendateValue += $(this).val()+'|';
            });
            $("input[name='opendateValue']").val(opendateValue);
            $("input[name='openweek']").attr("checked",false);
            $("input[name='startopenDate']").val("");
            $("input[name='endopenDate']").val("");
        }
    }
    function openeditfun(){
        var removeOption = $("#opendateSet").val();
        if(removeOption==null){
            alertMsg.error('请先选择修改的数据！');
        }else{
            var chk_value = "";
            $("input[name='openweek']:checked").each(function(){    
               //chk_value.push($(this).val());
                 chk_value +=$(this).val()+',';
            });
            chk_value = chk_value.substr(0,chk_value.lastIndexOf(','));
            //alert(chk_value);
            var startDate = $("input[name='startopenDate']").val();
            var endDate =  $("input[name='endopenDate']").val();
            //alert(startDate);
            //alert(endDate)
            if(startDate!=""||endDate!=""){
                if(startDate==""){startDate="任意时间"}
                if(endDate==""){endDate="以后"}
                newOption =startDate+'至'+endDate+'   '+chk_value;
                
            }else{
                newOption =chk_value;
            }
            
            $("#opendateSet option:selected").html(newOption);
            $("#opendateSet option:selected").attr("value",startDate+';'+endDate+';'+chk_value);
            var opendateValue="";
            $("#opendateSet option").each(function(){
                opendateValue += $(this).val()+'|';
            });
            $("input[name='opendateValue']").val(opendateValue);
        }
    }
    function openchangeSet(obj){
        $("input[name='openweek']").attr("checked",false);
        $("input[name='startopenDate']").val("");
        $("input[name='endopenDate']").val("");
        var thisval = $(obj).val();
        var s1=thisval.split(';');
        //alert(s10)
        if(s1[0] == "任意时间"){s1[0]=""}
        if(s1[1] == "以后"){s1[1]=""}
        $("input[name='startopenDate']").val(s1[0]);
        $("input[name='endopenDate']").val(s1[1]);
        //alert(s1[1]);
        s12 = s1[2].split(',');
        for(var i=0;i<s12.length;i++){
            //alert(s11[i]);
            $("input[name='openweek'][value='"+s12[i]+"']").attr("checked",true);
        }
    }

</script>
</form>