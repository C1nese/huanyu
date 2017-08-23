<?php if (!defined('THINK_PATH')) exit();?><script language="JavaScript">
$.area_default_show = true; //显示默认区域
$.area_select_bind( 'country_id' , 'province_id' , 'city_id' , 'county_id', 'town_id' );

var vd;
var lastname;
function getInfo(e){
    var thisname=e.name;
    if(lastname == thisname){
   		clearTimeout(vd);
		vd = setTimeout("regAjax('"+e.id+"')",600);
	}else{
		regAjax(e.id);
		lastname=thisname;
		clearTimeout(vd);
	}
}
function regAjax(name){
	var postname	= name;
	var postdata = {postname:name};
	$("input",navTab.getCurrentPanel()).each(function(i,n){
	 var postname	= n.name;
	 var value  = n.value;
	 postdata[postname]	= value;
	});
	$("select",navTab.getCurrentPanel()).each(function(i,n){
	 var postname	= n.name;
	 var value  = n.value;
	 postdata[postname]	= value;
	});

    $.ajax({
       url:"__APP__/Admin/Sale/regAjax:__XPATH__",
       type:"POST",
       data:postdata,
       dataType:"script",
       global:false,
       success:function(data){
		   if(data == ''){
			  $("#state_"+name,navTab.getCurrentPanel()).html('');
		   }else{
			  data;
		   }
       }
    });
}
function regAjaxall(){
	$('#state_productCountMoney',navTab.getCurrentPanel()).text("");
	var arr=<?php echo ($jsrequire); ?>;$('[id^=state_]').text("");
	for(var i in arr){
       $('#state_'+arr[i],navTab.getCurrentPanel()).text("*");
    }

	//把表单全部提交到regAjax中校验，如果有返回内容，则执行返回内容，并返回false,如果内容为空，则返回TRUE
	var postdata	= {};
	$("input",navTab.getCurrentPanel()).each(function(i,n){
		var postname	= n.name;
		var value  = n.value;
		postdata[postname]	= value;
	});
	$("select",navTab.getCurrentPanel()).each(function(i,n){
		var postname	= n.name;
		var value  = n.value;
		postdata[postname]	= value;
	});
	//alert(postdata[postname]);
	$.post('__URL__/regAjax:__XPATH__',postdata,function(data){
	//alert(data);
	if(!data)
	{
		<?php if(($alert == true)): ?>alertcheck();
		<?php else: ?>
			$('#form',navTab.getCurrentPanel()).submit();<?php endif; ?>
	}
	else
	{
		eval(data);
		return false;
	}
	});	
}
function alertcheck(){
	var alertstr ='<table class="list listmy" style="width:100%;">';
		alertstr+='<tr><td>'+'<?php echo ($levels->byname); ?>'+'</td>'
		alertstr+='<td>'+$("#lv option:selected",navTab.getCurrentPanel()).text()+'</td></tr>';
	<?php if(is_array($nets)): foreach($nets as $key=>$net): if(($net["type"] == 'text')): ?>alertstr+='<tr><td class="tbkey"><?php echo ($net["name"]); ?></td>';
		alertstr+='<td>'+$("#<?php echo ($net["inputname"]); ?>",navTab.getCurrentPanel()).val()+'</td></tr>';<?php endif; endforeach; endif; ?>
		alertstr+='<tr><td colspan="2">一旦注册，将不能修改，确定请点击确认</td></tr>';
		alertstr+='</table>';
		alertstr+='<style>.alert .confirm .alertInner {background: #fff;}.alert .alertInner .msg {margin: 0px;}';
		alertstr+='table.listmy td {border: solid 1px #adc5eb;padding:3px;}</style>';
	alertMsg.confirm(alertstr, {
		okCall: function(){
			$('#form',navTab.getCurrentPanel()).submit();
		}
	});
} 
</script>
<div layoutH="0">
<form action="__URL__/regSave:__XPATH__" method="post" id="form" onsubmit="return validateCallback(this, navTabAjaxDoneOpen)">
<input type="hidden" name="callbackType" value="closeCurrent" />
<input type="hidden" name="navTabId" value="<?php echo md5(__APP__.'/Admin/User/index');?>"/>
<input type="hidden" name="forwardUrl" value="__APP__/Admin/User/index"/>
<input type="hidden" name="navTabTitle" value="<?php echo ($user->byname); ?>查询"/>
<table style="width:600px;" class="list" >
<thead>
  <tr>
    <th colspan="3" style="text-align:left;">&nbsp;<img style="vertical-align:middle" src="__PUBLIC__/Images/user_add.png" />&nbsp;&nbsp;<?php echo ($user->byname); ?>注册</th>
  </tr>
</thead>
<tbody>
  
  <tr>
    <td width="31%" style="height:25px;vertical-align: middle;"><?php echo ($user->byname); ?>编号：</td>
    <td width="31%" style="height:25px;text-align:left;">
		<?php if(($user->idAutoEdit == true) AND ($user->idEdit == true)): ?><input type="text" value="<?php echo ($userid); ?>" size="20" name="userid" id="userid"/>
		<?php elseif(($user->idAutoEdit == true)): ?>
		<?php echo ($userid); ?>
		<input type="hidden" value="<?php echo ($userid); ?>" size="20" name="userid" id="userid"/>
		<?php else: ?>
		<input type="text" value="" size="20" name="userid" id="userid"/><?php endif; ?>
    </td>
	<td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_userid"></span>
    </td>
  </tr>
  
  <?php if(($sale->setLv == true)): ?><tr>
    <td style="height:25px;vertical-align: middle;"><?php echo ($levels->byname); ?>：</td>
    <td style="height:25px;text-align:left;">
		<select name='lv' id='lv' <?php if($zkbool == true): ?>onChange="admin_getTotalzf('<?php echo ($sale->name); ?>');"<?php endif; ?>>
		<?php if(is_array($levelsopt)): foreach($levelsopt as $key=>$level): ?><option value="<?php echo ($level["lv"]); ?>"><?php echo ($level["name"]); ?></option><?php endforeach; endif; ?>
		</select>
    </td>
	<td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_lv"></span> 
    </td>
  </tr>
  <?php else: ?>
  <input type="hidden" name="lv" id="lv" value="<?php echo ($sale->defaultLv); ?>" /><?php endif; ?>
  
  <?php if(($sale->setNumber == true)): ?><tr>
    <td width="20%" style="height:25px;vertical-align: middle;">单数：</td>
    <td style="height:25px;text-align:left;">
    <input type="text" value="" size="20" name="setNumber" >
    </td>
	<td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_setNumber">*</span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(($sale->setMoney == true)): ?><tr>
    <td width="20%" style="height:25px;vertical-align: middle;">报单金额：</td>
    <td style="height:25px;text-align:left;">
    <input type="text" value="" size="20" name="setMoney" >
    </td>
	<td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_setMoney">*</span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('name',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">姓名：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="name" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_name"><?php if(in_array('name',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('sex',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">性别：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="radio" name="sex" value="男" checked/>&nbsp;男&nbsp;<input type="radio" name="sex" value="女" />&nbsp;女</span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_sex"><?php if(in_array('sex',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('alias',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">昵称：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="alias" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_alias"><?php if(in_array('alias',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('id_card',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">证件号码：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="id_card" id="id_card"/></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_id_card"><?php if(in_array('id_card',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('email',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">Email：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="email" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_email"><?php if(in_array('email',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('qq',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">QQ：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="qq" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_qq"><?php if(in_array('qq',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('weixin',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">微信号：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="weixin" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_weixin"><?php if(in_array('weixin',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('country_code',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">国家区号：</td>
    <td style="height:25px;text-align:left;">
     <span>
         <!--data-pattern 正则表达式-->
     <select name='country_code' id="country_code" style="width: 152px;height: 21px">
     	<option value="86" data-pattern="^(86){0,1}1d{10}$">中国大陆(+86)</option>
        <option value="886" data-pattern="^(00){0,1}(886){1}0{0,1}[6,7,9](?:d{7}|d{8}|d{10})$">台湾(+886)</option>
        <option value="852" data-pattern="^(00){0,1}(852){1}0{0,1}[1,5,6,9](?:d{7}|d{8}|d{12})$">香港(+852)</option>
        <option value="60" data-pattern="^(00){0,1}(60){1}1d{8,9}$">马来西亚(+60)</option>
        <option value="65" data-pattern="^(00){0,1}(65){1}[13689]d{6,7}$">新加坡(+65)</option>
        <option value="81" data-pattern="^(00){0,1}(81){1}0{0,1}[7,8,9](?:d{8}|d{9})$">日本(+81)</option>
        <option value="82" data-pattern="^(00){0,1}(82){1}0{0,1}[7,1](?:d{8}|d{9})$">韩国(+82)</option>
        <option value="1us" data-pattern="^(00){0,1}(1){1}d{10,12}$">美国(+1)</option>
        <option value="1ca" data-pattern="^(00){0,1}(1){1}d{10}$">加拿大(+1)</option>
        <option value="61" data-pattern="^(00){0,1}(61){1}4d{8,9}$">澳大利亚(+61)</option>
        <option value="64" data-pattern="^(00){0,1}(64){1}[278]d{7,9}$">新西兰(+64)</option>
        <option value="54" data-pattern="^(00){0,1}(54){1}d{6,12}$">阿根廷(+54)</option>
        <option value="971" data-pattern="^(00){0,1}(971){1}d{6,12}$">阿联酋(+971)</option>
        <option value="353" data-pattern="^(00){0,1}(353){1}d{6,12}$">爱尔兰(+353)</option>
        <option value="20" data-pattern="^(00){0,1}(20){1}d{6,12}$">埃及(+20)</option>
        <option value="372" data-pattern="^(00){0,1}(372){1}d{6,12}$">爱沙尼亚(+372)</option>
        <option value="43" data-pattern="^(00){0,1}(43){1}d{6,12}$">奥地利(+43)</option>
        <option value="853" data-pattern="^(00){0,1}(853){1}6d{7}$">澳门(+853)</option>
        <option value="1242" data-pattern="^(00){0,1}(1242){1}d{6,12}$">巴哈马(+1242)</option>
        <option value="507" data-pattern="^(00){0,1}(507){1}d{6,12}$">巴拿马(+507)</option>
        <option value="55" data-pattern="^(00){0,1}(55){1}d{6,12}$">巴西(+55)</option>
        <option value="375" data-pattern="^(00){0,1}(375){1}d{6,12}$">白俄罗斯(+375)</option>
        <option value="359" data-pattern="^(00){0,1}(359){1}d{6,12}$">保加利亚(+359)</option>
        <option value="32" data-pattern="^(00){0,1}(32){1}d{6,12}$">比利时(+32)</option>
        <option value="48" data-pattern="^(00){0,1}(48){1}d{6,12}$">波兰(+48)</option>
        <option value="501" data-pattern="^(00){0,1}(501){1}d{6,12}$">伯利兹(+501)</option>
        <option value="45" data-pattern="^(00){0,1}(45){1}d{6,12}$">丹麦(+45)</option>
        <option value="49" data-pattern="^(00){0,1}(49){1}1(d{5,6}|d{9,12})$">德国(+49)</option>
        <option value="7" data-pattern="^(00){0,1}(7){1}[13489]d{9,11}$">俄罗斯(+7)</option>
        <option value="33" data-pattern="^(00){0,1}(33){1}[168](d{5}|d{7,8})$">法国(+33)</option>
        <option value="63" data-pattern="^(00){0,1}(63){1}[24579](d{7,9}|d{12})$">菲律宾(+63)</option>
        <option value="358" data-pattern="^(00){0,1}(358){1}d{6,12}$">芬兰(+358)</option>
        <option value="57" data-pattern="^(00){0,1}(57){1}d{6,12}$">哥伦比亚(+57)</option>
        <option value="31" data-pattern="^(00){0,1}(31){1}6d{8}$">荷兰(+31)</option>
        <option value="996" data-pattern="^(00){0,1}(996){1}d{6,12}$">吉尔吉斯斯坦(+996)</option>
        <option value="855" data-pattern="^(00){0,1}(855){1}d{6,12}$">柬埔寨(+855)</option>
        <option value="974" data-pattern="^(00){0,1}(974){1}d{6,12}$">卡塔尔(+974)</option>
        <option value="370" data-pattern="^(00){0,1}(370){1}d{6,12}$">立陶宛(+370)</option>
        <option value="352" data-pattern="^(00){0,1}(352){1}d{6,12}$">卢森堡(+352)</option>
        <option value="40" data-pattern="^(00){0,1}(40){1}d{6,12}$">罗马尼亚(+40)</option>
        <option value="960" data-pattern="^(00){0,1}(960){1}d{6,12}$">马尔代夫(+960)</option>
        <option value="976" data-pattern="^(00){0,1}(976){1}d{6,12}$">蒙古(+976)</option>
        <option value="51" data-pattern="^(00){0,1}(51){1}d{6,12}$">秘鲁(+51)</option>
        <option value="212" data-pattern="^(00){0,1}(212){1}d{6,12}$">摩洛哥(+212)</option>
        <option value="52" data-pattern="^(00){0,1}(52){1}d{6,12}$">墨西哥(+52)</option>
        <option value="27" data-pattern="^(00){0,1}(27){1}d{6,12}$">南非(+27)</option>
        <option value="234" data-pattern="^(00){0,1}(234){1}d{6,12}$">尼日利亚(+234)</option>
        <option value="47" data-pattern="^(00){0,1}(47){1}d{6,12}$">挪威(+47)</option>
        <option value="351" data-pattern="^(00){0,1}(351){1}d{6,12}$">葡萄牙(+351)</option>
        <option value="46" data-pattern="^(00){0,1}(46){1}[124-7](d{8}|d{10}|d{12})$">瑞典(+46)</option>
        <option value="41" data-pattern="^(00){0,1}(41){1}d{6,12}$">瑞士(+41)</option>
        <option value="381" data-pattern="^(00){0,1}(381){1}d{6,12}$">塞尔维亚(+381)</option>
        <option value="248" data-pattern="^(00){0,1}(248){1}d{6,12}$">塞舌尔(+248)</option>
        <option value="966" data-pattern="^(00){0,1}(966){1}d{6,12}$">沙特阿拉伯(+966)</option>
        <option value="94" data-pattern="^(00){0,1}(94){1}d{6,12}$">斯里兰卡(+94)</option>
        <option value="66" data-pattern="^(00){0,1}(66){1}[13456789]d{7,8}$">泰国(+66)</option>
        <option value="90" data-pattern="^(00){0,1}(90){1}d{6,12}$">土耳其(+90)</option>
        <option value="216" data-pattern="^(00){0,1}(216){1}d{6,12}$">突尼斯(+216)</option>
        <option value="58" data-pattern="^(00){0,1}(58){1}d{6,12}$">委内瑞拉(+58)</option>
        <option value="380" data-pattern="^(00){0,1}(380){1}[3-79]d{8,9}$">乌克兰(+380)</option>
        <option value="34" data-pattern="^(00){0,1}(34){1}d{6,12}$">西班牙(+34)</option>
        <option value="30" data-pattern="^(00){0,1}(30){1}d{6,12}$">希腊(+30)</option>
        <option value="36" data-pattern="^(00){0,1}(36){1}d{6,12}$">匈牙利(+36)</option>
        <option value="39" data-pattern="^(00){0,1}(39){1}[37]d{8,11}$">意大利(+39)</option>
        <option value="972" data-pattern="^(00){0,1}(972){1}d{6,12}$">以色列(+972)</option>
        <option value="91" data-pattern="^(00){0,1}(91){1}d{6,12}$">印度(+91)</option>
        <option value="62" data-pattern="^(00){0,1}(62){1}[2-9]d{7,11}$">印度尼西亚(+62)</option>
        <option value="44" data-pattern="^(00){0,1}(44){1}[347-9](d{8,9}|d{11,12})$">英国(+44)</option>
        <option value="1284" data-pattern="^(00){0,1}(1284){1}d{6,12}$">英属维尔京群岛(+1284)</option>
        <option value="962" data-pattern="^(00){0,1}(962){1}d{6,12}$">约旦(+962)</option>
        <option value="84" data-pattern="^(00){0,1}(84){1}[1-9]d{6,9}$">越南(+84)</option>
        <option value="56" data-pattern="^(00){0,1}(56){1}d{6,12}$">智利(+56)</option>
     </select>
     </span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="country_state"><?php if(in_array('country_code',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  <?php if(in_array('mobile',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">移动电话：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="mobile" id="mobile" onblur="checkMobile(this.value)"/></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_mobile"><?php if(in_array('mobile',$require) == true ): ?>*<?php endif; ?></span>
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('pass1',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">一级密码：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="password" value="" name="pass1" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_pass1"><?php if(in_array('pass1',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('pass1c',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">一级密码确认：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="password" value="" name="pass1c" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_pass1c"><?php if(in_array('pass1c',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('pass2',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">二级密码：</td>
    <td style="height:25px;text-align:left;">
		<span><input type="password" value="" name="pass2"/></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
		&nbsp;<span id="state_pass2"><?php if(in_array('pass2',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('pass2c',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">二级密码确认:</td>
    <td style="height:25px;text-align:left;">
		<span><input type="password" value="" name="pass2c" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_pass2c"><?php if(in_array('pass2c',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  <?php if(($pwd3Switch) == "true"): ?>
  <?php if(in_array('pass3',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">三级密码：</td>
    <td style="height:25px;text-align:left;">
		<span><input type="password" value="" name="pass3"/></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
		&nbsp;<span id="state_pass3"><?php if(in_array('pass3',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('pass3c',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">三级密码确认:</td>
    <td style="height:25px;text-align:left;">
		<span><input type="password" value="" name="pass3c" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_pass3c"><?php if(in_array('pass3c',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; endif; ?>
  
  <?php if($reg_safe == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">密保问题：</td>
    <td style="height:25px;text-align:left;">
		<select name="secretsafe_name">
		<option value="">请选择</option>
		<?php if(is_array($SecretSafelist)): foreach($SecretSafelist as $key=>$SecretSafe): ?><option value="<?php echo ($SecretSafe["密保问题"]); ?>"><?php echo ($SecretSafe["密保问题"]); ?></option><?php endforeach; endif; ?>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
      &nbsp;<span id="state_secretsafe_name">*</span>   
    </td>
  </tr>
   <tr>
    <td style="height:25px;vertical-align: middle;">密保答案：</td>
    <td style="height:25px;text-align:left;">
		<input type="text" value="" name="secretanswer" />
    </td>
    <td style="height:25px;vertical-align: middle;">
         &nbsp;<span id="state_secretanswer">*</span>   
    </td>
  </tr><?php endif; ?>
  
  <?php if($haveuser == true): ?>
  <?php if(!empty($fun_val)): if(is_array($fun_val)): foreach($fun_val as $key=>$fun): ?><tr>
    <td style="height:25px;vertical-align: middle;"><?php echo ($key); ?>：</td>
    <td style="height:25px;text-align:left;">
		<input type="text" name="<?php echo ($fun); ?>" value="" />
    </td>
	<td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_<?php echo ($fun); ?>"></span> 
    </td>
  </tr><?php endforeach; endif; endif; ?>
  
  <?php if(is_array($nets)): foreach($nets as $key=>$net): if(($net["type"] == 'text')): ?><tr>
    <td style="height:25px;vertical-align: middle;"><?php echo ($net["name"]); ?>：</td>
    <td style="height:25px;text-align:left;">
		<span><input type="text" size="20" name="<?php echo ($net["inputname"]); ?>" otherpost='<?php echo ($net["otherpost"]); ?>' onkeyup="getInfo(this)" id="<?php echo ($net["inputname"]); ?>" autocomplete="off" value="<?php echo ($net["value"]); ?>"/></span>
    </td>
    <td width="38%" style="height:25px;vertical-align: left;">
        &nbsp;<span id="state_<?php echo ($net["inputname"]); ?>"><?php if(($net["require"] == true)): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(($net["type"] == 'select')): ?><tr>
    <td style="height:25px;vertical-align: middle;"><?php echo ($net["name"]); ?>：</td>
    <td style="height:25px;text-align:left;">
	    <select name='<?php echo ($net["inputname"]); ?>'  otherpost='<?php echo ($net["otherpost"]); ?>' id="<?php echo ($net["inputname"]); ?>" onchange="getInfo(this)">
	    <?php if(is_array($net["Region"])): foreach($net["Region"] as $key=>$Region): ?><option value='<?php echo ($Region["name"]); ?>' <?php if(isset($_GET['position']) and $_GET['position']==$key): ?>selected<?php endif; ?>><?php echo ($Region["byname"]); ?></option><?php endforeach; endif; ?>
	    </select>
	</td>
	<td style="height:25px;vertical-align: middle;">
	    &nbsp;<span id="state_<?php echo ($net["inputname"]); ?>"><?php if(($net["require"] == true)): ?>*<?php endif; ?></span> 
	</td>
  </tr><?php endif; endforeach; endif; ?>
  
  <?php if(!empty($sale->fromNoName)): ?><tr>
    <td style="height:25px;vertical-align: middle;">商务中心：</td>
    <td style="height:25px;text-align:left;">
		<span><input type="text" value="" name="shop"  onkeyup="getInfo(this)" id="shop" autocomplete="off"/></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_shop"><?php if(in_array('shop',$require) == true): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; endif; ?>
  
  <?php if(!empty($fun_select)): if(is_array($fun_select)): foreach($fun_select as $key=>$select): ?><tr>
    <td width="20%" style="height:25px;vertical-align: middle;"><?php echo ($select["name"]); ?>：</td>
    <td style="height:25px;text-align:left;">
    <select name="<?php echo ($key); ?>">
    <?php if(is_array($select["con"])): foreach($select["con"] as $key=>$con): ?><option value="<?php echo ($con["val"]); ?>" <?php if(($con["val"]) == "select.default"): ?>selected<?php endif; ?>><?php echo ($con["name"]); ?></option><?php endforeach; endif; ?>
    </select>
    </td>
  </tr><?php endforeach; endif; endif; ?>
  
  <?php if(($sale->nullMode != true) and (count($regtype) > 1)): ?><tr>
    <td style="height:25px;vertical-align: middle;">空点：</td>
    <td style="height:25px;text-align:left;">
		<select name="nullMode" style="padding:0px;width:100px" id="nullMode">
		<?php if(is_array($regtype)): foreach($regtype as $key=>$type): ?><option value="<?php echo ($key); ?>"><?php echo ($type); ?></option><?php endforeach; endif; ?>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">&nbsp;
    </td>
  </tr>
  <?php elseif($sale->nullMode == true): ?>
	<input name="nullMode" value="1" type="hidden"/><?php endif; ?>
  
  <?php if(in_array('area',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">国家：</td>
    <td style="height:25px;text-align:left;">
		<select name="country" style="padding:0px;width:100px" id="country_id">
		<option value="">请选择</option>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_country"><?php if((in_array('area',$require) == true)): ?>*<?php endif; ?></span> 
    </td>
  </tr>
  
  <tr>
    <td style="height:25px;vertical-align: middle;">省/州：</td>
    <td style="height:25px;text-align:left;">
		<select name="province" style="padding:0px;width:100px" id="province_id" <?php if($logistic == true): ?>onChange="admin_getTotalzf('<?php echo ($sale->name); ?>');"<?php endif; ?>>
		<option value="">请选择</option>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_province"><?php if(in_array('area',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr>
  
  <tr>
    <td style="height:25px;vertical-align: middle;">城市：</td>
    <td style="height:25px;text-align:left;">
	    <select name="city" style="padding:0px;width:100px" id="city_id">
		<option value="">请选择</option>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_city"><?php if(in_array('area',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr>
  
  <tr>
    <td style="height:25px;vertical-align: middle;">区县：</td>
    <td style="height:25px;text-align:left;">
		<select name="county" style="padding:0px;width:100px" id="county_id">
		<option value="">请选择</option>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_county"><?php if(in_array('area',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr>
  <tr>
    <td style="height:25px;vertical-align: middle;">街道：</td>
    <td style="height:25px;text-align:left;">
		<select name="town" style="padding:0px;width:100px" id="town_id">
		<option value="">请选择</option>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_town"><?php if(in_array('area',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('address',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">地址：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="address" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_address"><?php if(in_array('address',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('reciver',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">收货人：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="reciver" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_reciver"><?php if(in_array('reciver',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('bank_apply_name',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">开户行：</td>
    <td style="height:25px;text-align:left;">
		<select name="bank_apply_name">
		<option value="">请选择</option>
		<?php if(is_array($banklist)): foreach($banklist as $key=>$bank): ?><option value="<?php echo ($bank["开户行"]); ?>"><?php echo ($bank["开户行"]); ?></option><?php endforeach; endif; ?>
		</select>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_bank_apply_name"><?php if(in_array('bank_apply_name',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('bank_card',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">银行卡号：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="bank_card" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_bank_card"><?php if(in_array('bank_card',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('bank_name',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">开户名：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="bank_name" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_bank_name"><?php if(in_array('bank_name',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(in_array('bank_apply_addr',$show) == true): ?><tr>
    <td style="height:25px;vertical-align: middle;">开户地址：</td>
    <td style="height:25px;text-align:left;">
     <span><input type="text" value="" name="bank_apply_addr" /></span>
    </td>
    <td style="height:25px;vertical-align: middle;">
        &nbsp;<span id="state_bank_apply_addr"><?php if(in_array('bank_apply_addr',$require) == true ): ?>*<?php endif; ?></span> 
    </td>
  </tr><?php endif; ?>
  
  <?php if(isset($funReg)): if(is_array($funReg)): foreach($funReg as $key=>$fun): ?><tr>
	  <td style="height:25px;vertical-align: middle;"><?php echo ($fun); ?>：</td>
	  <td style="height:25px;text-align:left;">
		  <span><input type="text" value="" name="<?php echo ($fun); ?>" /></span>
	  </td>
	  <td style="height:25px;vertical-align: middle;">
		  &nbsp;<span id="state_<?php echo ($fun); ?>"><?php if(in_array($fun,$require) == true ): ?>*<?php endif; ?></span> 
      </td>
	</tr><?php endforeach; endif; endif; ?>
  <!--基本信息结束-->
 
</tbody>
</table>
 
	<?php if(isset($productArr)): ?><div id="state_productCountMoney" style="padding-top:10px;padding-left:370px;height:20px;line_height:20px"></div>
  <table style="width:700px;margin-top:20px" class="list">
  <thead>
	<tr><td colspan="8" style="height:25px;text-align:left;padding-left:5px;font-weight:bold"><?php echo ($sale->productName); ?>选购</td></tr>
	<tr style="background:#D5DDDF;height:30px;">
		<td colspan="8" style="height:30px;text-align:left;padding-left:15px;">
		<?php $i=1; ?>
			<?php if(is_array($productArr)): foreach($productArr as $key=>$product): ?><div style="<?php if(($i) == "1"): ?>background:#fff;<?php endif; ?>float:left;width:80px;text-align:center;padding-top:8px;height:20px;cursor:pointer;font-weight:bold" id="productCategory_<?php echo ($i); ?>" productCategoryid="<?php echo ($i); ?>">
					<?php echo ($key); ?>
				</div>
		<?php $i++; endforeach; endif; ?>
		</td>
	</tr>
	<tr>
		<td style="width:4%">序号</td>
		<td style="width:15%">产品名称</td>
		<td style="width:10%">图片</td>
		<td style="width:8%">数量</td>
		<td style="width:10%"><?php echo ($sale->productMoney); ?></td>
		<?php if(($sale->productPV) == "true"): ?><td style="width:10%">PV</td><?php endif; ?>
		<?php if($logistic == true): ?><td style="width:10%">重量</td><?php endif; ?>
		<?php if(($proobj->productnumCheck == true) or (adminshow('prostock') == true)): ?><td style="width:10%">库存</td><?php endif; ?>
	</tr>
  </thead>
	<?php $ii=1; ?>
	<?php if(is_array($productArr)): foreach($productArr as $fenlei=>$product): ?><tbody id="productTbody_<?php echo ($ii); ?>" style="<?php if(($ii) != "1"): ?>display:none<?php endif; ?>">
		<?php if(is_array($product)): $i = 0; $__LIST__ = $product;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
				<td><?php echo ($key+1);?></td>
				<td><?php echo ($vo["名称"]); ?></td>
				<td>
					<?php $imgstr=$vo['图片']; ?>
					<?php if((strlen($imgstr) == 0)): ?>无
					<?php $productimg='无'; ?>
					<?php else: ?>
					<img src="<?php echo ($vo["图片"]); ?>" width='120px' />
					<?php $productimg='<img src="'.$vo['图片'].'" width="120px"/>'; endif; ?>
				</td>
				<td><input type="text" name="productNum[<?php echo ($vo["id"]); ?>]"  id="productNum_<?php echo ($vo["id"]); ?>" productNumInfo="<?php echo ($vo["id"]); ?>_<?php echo ($fenlei); ?>_<?php echo ($vo["名称"]); ?>_<?php echo ($vo[$sale->productMoney]); ?>_<?php echo ($vo["PV"]); ?>_<?php echo ($vo["重量"]); ?>" productimg='<?php echo ($productimg); ?>' pronum="<?php echo ($vo["可订购数量"]); ?>" style="width:35px"></td>
				<td><?php echo ($vo[$sale->productMoney]); ?></td>
				<?php if(($sale->productPV) == "true"): ?><td><?php echo ($vo["PV"]); ?></td><?php endif; ?>	
				<?php if($logistic == true): ?><td><?php echo ($vo["重量"]); ?></td><?php endif; ?>
				<?php if(($proobj->productnumCheck == true) or (adminshow('prostock') == true)): ?><td><?php echo ($vo["可订购数量"]); ?></td><?php endif; ?>
			</tr><?php endforeach; endif; else: echo "" ;endif; ?>
	</tbody>
	<?php $ii++; endforeach; endif; ?>
  </table>
	
  <table style="width:700px;margin-top:20px" class="list">
   <thead>
	<tr><td colspan="8" style="height:25px;text-align:left;padding-left:5px;font-weight:bold">已选产品</td></tr>
	<tr><td style="width:4%">序号</td>
	<td style="width:10%">类别</td>
	<td style="width:15%">产品名称</td>
	<td style="width:10%">图片</td>
	<td style="width:8%">总计数量</td>
	<td style="width:10%">总金额</td>	
	<?php if(($sale->productPV) == "true"): ?><td style="width:10%">总PV</td><?php endif; ?>
	<?php if($logistic == true): ?><td style="width:10%">总重量</td><?php endif; ?>
	</tr>	
   </thead>
	<tbody id="selectedProduct">
	</tbody>

	<tr>
		<td colspan="4" style="text-align:right">汇总：</td>
		<td id="totalnum">0</td>
		<td id="totalprice">0</td>
		<?php if(($sale->productPV) == "true"): ?><td id="totalpv">0</td><?php endif; ?>
		<?php if($logistic == true): ?><td id="totalweight">0</td><?php endif; ?>
	</tr>
	
	<?php if(($logistic == true) or ($zkbool == true)): ?><tr>
		<td colspan="4" style="font-weight:bold;">
			<span style="display:inline-block;text-align:left;width:80%">
			<?php if($zkbool == true): ?>折扣：<span id="zk"></span>折<?php endif; ?>
			&nbsp;&nbsp;&nbsp;
			<?php if($logistic == true): ?>物流费：<span id="wlf"></span><?php endif; ?>
			</span>
			<span>实际支付：</span>
		</td>
		<td id="totalzf" colspan="4"  style="text-align:center;font-weight:bold;">0</td>
	</tr><?php endif; ?>
  </table>
<?php if(($logistic == true) or ($zkbool == true)): ?><script language="javascript" src="__PUBLIC__/js/cal.js"></script><?php endif; ?>
<script language="javascript">
$(function(){
	//开启库存
	var productStock = <?php if(($proobj->productnumCheck == true) or (adminshow('prostock') == true)): ?>true;<?php else: ?>false;<?php endif; ?>
	//点击哪个分类显示哪个分类下的产品
	navTab.getCurrentPanel().find('[id^=productCategory_]').click(function(){
		navTab.getCurrentPanel().find('[id^=productTbody_]').hide();
		navTab.getCurrentPanel().find('#productTbody_'+$(this).attr('productCategoryid')).show();
		navTab.getCurrentPanel().find('[id^=productCategory_]').css({background:''});
		$(this).css('background','#fff');
	});
	//输入数量时
	navTab.getCurrentPanel().find("[id^=productNum_]").keyup(function(){
		var product =$(this).attr('productNumInfo').split("_");
		var productimg = $(this).attr('productimg');
		//数量只能填写数字
		var num = $(this).val();
		num = parseInt(num.replace(/b(0+)/gi,""));
		if(!(num > 0 && (!isNaN(num)))){
			$(this).val('');
		}
		//开启库存时，不能超过库存量
		var realnum=$(this).attr('pronum');
		if(productStock && num > realnum){
			num =  parseInt(realnum);
			$(this).val(num);
		}
		
		//定义序号
		var k=0;
		navTab.getCurrentPanel().find("#selectedProduct > tr").each(function(i,v){
			if($(v).attr('selectedProductid') == product[0]){
				$(this).remove();//删除
			}else{
				k=parseInt($(this).find("td:first").html());
			}
		});
		//0id_1分类_2名称_3价格_4pv_5重量
		if((!isNaN(num)) && num > 0){
			navTab.getCurrentPanel().find("#selectedProduct").append('<tr selectedProductid="'+product[0]+'" style="border-bottom:1px solid #EDEDED;"><td>'+(parseInt(k)+1)+'</td><td>'+product[1]+'</td><td>'+product[2]+'</td><td>'+productimg+'</td><td id="selnum_'+product[0]+'">'+num+'</td><td id="selprice_'+product[0]+'">'+(num*product[3]).toFixed(2)+'</td><?php if(($sale->productPV) == "true"): ?><td id="selpv_'+product[0]+'">'+(num*product[4]).toFixed(2)+'</td><?php endif; if($logistic == true): ?><td  id="selweight_'+product[0]+'">'+(num*product[5]).toFixed(2)+'</td><?php endif; ?></tr>');
		}
		//统计
		var countNum=0;
		var countMoney = 0;
		var countPV = 0;
		var countWeight=0;
		navTab.getCurrentPanel().find("#selectedProduct > tr").each(function(i,v){
			var proid=$(this).attr('selectedProductid');
			countNum +=parseFloat($(this).find('#selnum_'+proid).html());
			countMoney +=parseFloat($(this).find('#selprice_'+proid).html());
			<?php if(($sale->productPV) == "true"): ?>countPV +=parseFloat($(this).find('#selpv_'+proid).html());<?php endif; ?>
			<?php if(($logistic) == "true"): ?>countWeight +=parseFloat($(this).find('#selweight_'+proid).html());<?php endif; ?>
		})
		//输出显示
		navTab.getCurrentPanel().find("#totalnum").html(countNum);
		navTab.getCurrentPanel().find("#totalprice").html(countMoney.toFixed(2));
		<?php if(($sale->productPV) == "true"): ?>navTab.getCurrentPanel().find("#totalpv").html(countPV.toFixed(2))<?php endif; ?>;
		<?php if($logistic == true): ?>navTab.getCurrentPanel().find("#totalweight").html(countWeight.toFixed(2));<?php endif; ?>
		//计算实付款并显示
		<?php if(($logistic == true) or ($zkbool == true)): ?>admin_getTotalzf('<?php echo ($sale->name); ?>');<?php endif; ?>
	});
});
</script><?php endif; ?>
	<div id="state_lockcon" style="padding-left:400px;color:red"></div>
	<div class="buttonActive" style="margin-left:290px;margin-top:5px;">
          <div class="buttonContent" >
            <button type="button" onclick="regAjaxall()">确定</button>
          </div>
    </div>
 </form>
</div>
<script>
    //手机号失去焦点后验证
    $('#mobile').blur(function () {
        if (test()) {
            $('#state_mobile').html('输入正确');
        } else {
            $('#state_mobile').html('输入有误！');
        }
    });
    $('#id_card').blur(function () {
        if (isCardNo()) {
            $('#state_id_card').html('输入正确');
        } else {
            $('#state_id_card').html('输入有误！');
        }
    });
    function isCardNo() {
    	var card=document.getElementById('id_card').value;
   		// 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X  
   		var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
   		if(reg.test(card) === false){ 
		       return  false;  
		}
		return true;
    }
    function test() {
		var str=document.getElementById('mobile').value;
	    var re = /^1\d{10}$/
	    if (re.test(str)) {
	        return true;
	    } else {
	        return false;
	    }
    }
</script>