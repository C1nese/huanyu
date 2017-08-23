String.prototype.format = function() {
    var args = arguments;
    return this.replace(/\{(\d+)\}/g, function(m, i) {
        return args[i];
    });
};
function showhint(iconid, str)
{
	var imgUrl='../images/hint.gif';
	if (iconid != 0)
	{
		imgUrl = '../images/warning.gif';
	}
	document.write('<div style="background:url(' + imgUrl + ') no-repeat 20px 10px;border:1px dotted #DBDDD3; background-color:#FDFFF2; margin-bottom:10px; padding:10px 10px 10px 56px; text-align: left; font-size: 12px;">');
	document.write(str + '</div><div style="clear:both;"></div>');
}

function showloadinghint(divid, str)
{
	if (divid=='')
	{
		divid='PostInfo';
	}
	document.write('<div id="' + divid + ' " style="display:none;position:relative;border:1px dotted #DBDDD3; background-color:#FDFFF2; margin:auto;padding:10px" width="90%"  ><img border="0" src="../images/ajax_loading.gif" /> ' + str + '</div>');
}

function dosuccess(res){
    $("success").style.display = "block";
    SetDialogTop("success");
     var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
    window.onresize = function() { SetDialogTop("success");SetDialogTop("BOX_overlay"); }
    window.onscroll = function() { SetDialogTop("success");SetDialogTop("BOX_overlay"); }
    res=res.replace('green','white');
    if (res.toString().length > 0) { $("successtitle").innerHTML = res } else { $("successtitle").innerHTML = "操作成功!"; }
    setTimeout("$(\"success\").style.display = \"none\";ShowTag();closediv();", 1500);
}
function doerror(res) {
    $("error").style.display = "block";
    SetDialogTop("error");
    var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
    window.onresize = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }
    window.onscroll = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }

    res=res.replace('green','white');
    if (res.toString().length > 0) {$("errtitle").innerHTML = res; } else {$("errtitle").innerHTML = "操作失败!"; }
}
function doerror_hid(res,t){
    $("error").style.display = "block";
    SetDialogTop("error");
      var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
    window.onresize = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }
    window.onscroll = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }
    res=res.replace('green','white');
    if (res.toString().length > 0) { $("errtitle").innerHTML = res; } else { $("errtitle").innerHTML = "操作失败!<br />"; }
    setTimeout("closediv();",t);
}
function doshoinfo(res) {
    $("error").style.display = "block";
    SetDialogTop("error");
      var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
    window.onresize = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }
    window.onscroll = function() { SetDialogTop("error");SetDialogTop("BOX_overlay"); }
    res=res.replace('green','white');
    if (res.toString().length > 0) { $("errtitle").innerHTML = "<img border='0' alt='关闭提示' style='cursor:pointer;' onclick=\"javascript:closediv();\" src='/manage/images/icons/icon1.jpg'  />" + res + ""; } else { $("errtitle").innerHTML = "操作失败!<br />"; }
}
function ShowOverlay_Msg(msg) {
         var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
   $("loadingtitle").innerHTML = msg;
    $('loading').style.display = "block";
    window.onresize = function() { SetDialogTop("loading");SetDialogTop("BOX_overlay"); }
    window.onscroll = function() { SetDialogTop("loading");SetDialogTop("BOX_overlay"); }

}
function CloseOverlay_Msg() {
    if($('loading')){$('loading').style.display = "none";}
    if($('BOX_overlay')){$('BOX_overlay').style.display = "none";}
    ShowTag();
}
function CheckByName(form,tname,checked)
{
    for (var i=0;i<form.elements.length;i++)
    {
        var e = form.elements[i];
        if(e.name == tname)
        {
            e.checked = checked;
        }
    }
}

function ShowOverlay_Msg(msg) {
         var scrollLeft = (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var scrollTop = (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    var clientWidth = document.documentElement.clientWidth;
    var clientHeight = document.documentElement.clientHeight;
    var bo = $('BOX_overlay');
    bo.style.left = scrollLeft+'px';
    bo.style.top = scrollTop+'px';
    bo.style.width = clientWidth+'px';
    bo.style.height = clientHeight+'px';
    bo.style.display=""
   $("loadingtitle").innerHTML = msg;
    $('loading').style.display = "block";

}
function CloseOverlay_Msg() {
    if($('loading')){$('loading').style.display = "none";}
    if($('BOX_overlay')){$('BOX_overlay').style.display = "none";}
    ShowTag();
}
function CheckAll(form)
{
  for (var i=0;i<form.elements.length;i++)
    {
        var e = form.elements[i];
        if (e.type=="checkbox" && e.name != 'chkall' && e.name !='deleteMode')
        {
           e.checked = form.chkall.checked;
        }
    }
}

//function SH_SelectOne()
//{
//	var obj = window.event.srcElement;
//	if( obj.checked == false)
//	{
//		$('chkall').checked = obj.chcked;
//		
//	}
//}


  function   selectall(s)
  {   
  var   obj=document.getElementsByTagName("input");   
  for(i=0;i<obj.length;i++)
  {
  if(obj[i].id=="id"+s)   
 {
  obj[i].checked=window.event.srcElement.checked ; 
 }
  }   
}


function SH_SelectOne(obj)
{
	//var obj = window.event.srcElement;
	if( obj.checked == false)
	{
		$('chkall').checked = obj.chcked;
	}
}


//function togetherpi(obj)
//{
//if($("id"+obj).checked == true)
//{
//$("pid"+obj).checked =true;
//else
//$("pid"+obj).checked =false;
//}





function isMaxLen(o)
{
	var nMaxLen=o.getAttribute? parseInt(o.getAttribute("maxlength")):"";
	if(o.getAttribute && o.value.length>nMaxLen)
	{
		o.value=o.value.substring(0,nMaxLen)
	}
}
    
/*
function Pause(obj,iMinSecond){ 
 if (window.eventList==null) window.eventList=new Array(); 
 var ind=-1; 
 for (var i=0;i<window.eventList.length;i++){ 
  if (window.eventList[i]==null) { 
   window.eventList[i]=obj; 
   ind=i; 
   break; 
  } 
 } 
  
 if (ind==-1){ 
  ind=window.eventList.length; 
  window.eventList[ind]=obj; 
 } 
 setTimeout("GoOn(" + ind + ")",iMinSecond); 
} 


function GoOn(ind){ 
 var obj=window.eventList[ind]; 
 window.eventList[ind]=null; 
 if (obj.NextStep) obj.NextStep(); 
 else obj(); 
} 


function Test(name){ 
 alert(name); 
 Pause(this,10000);//调用暂停函数 
 this.NextStep=function hello(name){ 
  alert('hello'+name); 
} 
} 

Test('dai');
*/

//权限按行选函数
function selectRow(rowId,check)
{
	$("viewperm" + rowId).checked = check;
	$("postperm" + rowId).checked = check;
	$("replyperm" + rowId).checked = check;
	$("getattachperm" + rowId).checked = check;
	$("postattachperm" + rowId).checked = check;
}
//权限按列选函数
function seleCol(colPerfix,check)
{
	var obj;
	var i = 1;
	while(true)
	{
		obj = $(colPerfix + i);
		if(obj == null) break;
		obj.checked = check;
		i++;
	}
}
var MyElements = document.documentElement;

function SetDialogTop(o) {
    var tagNames = ["select","object"];
    hiddenTags = GetHiddenTagArray(tagNames);
    if (hiddenTags.length > 0) {
        for (var i = 0; i < hiddenTags.length; i++) {
            var hiddenTag = hiddenTags[i];
            hiddenTag.style.visibility = "hidden";
        }
        hiddenTags = GetShowTagArray(tagNames, o);
        for (var i = 0; i < hiddenTags.length; i++) {
            var hiddenTag = hiddenTags[i];
            hiddenTag.style.visibility = "";
        }
    }

    var top = (parseInt((document.documentElement.clientHeight - $(o).clientHeight - 140) / 2));

    top = parseInt((document.documentElement.clientHeight - $(o).clientHeight - 140) / 2);
    if (top < 0) {
        top = 0;
    }
    top += MyElements.scrollTop;
    $(o).style.top = top + "px";


}
function HideTags(obj) {

    var tagNames = ["select","object"];
    hiddenTags = GetHiddenTagArray(tagNames);
    for (var i = 0; i < hiddenTags.length; i++) {
        var hiddenTag = hiddenTags[i];
        //alert(hiddenTag.id);
        if(hiddenTag!=obj){hiddenTag.style.visibility = "hidden";}
    }
}

function GetHiddenTagArray(tagNames) {
    if (!$("menulist")) { return new Array(); }
    if (!tagNames.length) { return new Array(); }
    var array = new Array();
    //获取大容器中的对象列表,添加至数组中
    for (var i = 0; i < tagNames.length; i++) {
        var x = $("menulist"); //指定获取对象的域
        var elements = x.getElementsByTagName(tagNames[i]); //遍历指定的标签
        for (var j = 0; j < elements.length; j++) {
            var element = elements[j];
            array.push(element);
        }
    }
    return array;
   }
   
   function HideTags2() {

    var tagNames = ["select","object"];
    hiddenTags = GetHiddenTagArray2(tagNames);
    for (var i = 0; i < hiddenTags.length; i++) {
        var hiddenTag = hiddenTags[i];
        //alert(hiddenTag.id);
        hiddenTag.style.visibility = "hidden";
    }
}

function GetHiddenTagArray2(tagNames) {
    if (!tagNames.length) { return new Array(); }
    var array = new Array();
    //获取大容器中的对象列表,添加至数组中
    for (var i = 0; i < tagNames.length; i++) {
        var x = document.body; //指定获取对象的域
        var elements = x.getElementsByTagName(tagNames[i]); //遍历指定的标签
        for (var j = 0; j < elements.length; j++) {
            var element = elements[j];
            array.push(element);
        }
    }
    return array;
   }
   
   function GetShowTagArray(tagNames,o) {
       if (!tagNames.length) { return new Array(); }
       var array = new Array();
       if ($(o)) {
           //获取大容器中属可操作的对象列表,从数组中移除
           for (var i = 0; i < tagNames.length; i++) {
               var x = $(o); //指定获取对象的域
               var elements = x.getElementsByTagName(tagNames[i]); //遍历指定的标签
               for (var j = 0; j < elements.length; j++) {
                   var element = elements[j];
                   array.push(element);
               }
           }
       }
       return array;
   }
function changeDeleteModeState(item,form)
{
	switch(item)
	{
	
		case 1:
			$("chkall").disabled = false;
			$("deleteNum").disabled = $("deleteFrom_deleteFrom").disabled = true;
			enableCheckBox(false,form);
			$("deleteNum").value = "";
			$("deleteFrom_deleteFrom").value = "";
			break;
		case 2:
			$("deleteNum").disabled = false;
			$("chkall").disabled = $("deleteFrom_deleteFrom").disabled = true;
			enableCheckBox(true,form);
			$("chkall").checked = false;			
			$("deleteFrom_deleteFrom").value = "";
			break;
		case 3:
			$("deleteFrom_deleteFrom").disabled = false;
			$("chkall").disabled = $("deleteNum").disabled = true;
			enableCheckBox(true,form);
			$("chkall").checked = false;			
			$("deleteNum").value = "";
			break;
	}
}  

function enableCheckBox(b,form)
{
	for (var i=0;i<form.elements.length;i++)
	{
		var e = form.elements[i];
		if (e.type == "checkbox")
		{
			e.disabled = b;
			e.checked = false;
		}
	}
} 

function isie()
{
   if(navigator.userAgent.toLowerCase().indexOf('msie') != -1)
   {
       return true;
   }
   else
   {
       return false;
   }
}  


//显示提示层
function showhintinfo(obj, objleftoffset,objtopoffset, title, info , objheight, showtype ,objtopfirefoxoffset)
{
   //HideTags(obj);
   var p = getposition(obj);
   
   if((showtype==null)||(showtype =="")) 
   {
       showtype =="up";
   }
   $('hintiframe'+showtype).style.height= objheight + "px";
   $('hintinfo'+showtype).innerHTML = info;
   $('hintdiv'+showtype).style.display='block';
   
   if(objtopfirefoxoffset != null && objtopfirefoxoffset !=0 && !isie())
   {
        $('hintdiv'+showtype).style.top=p['y']+parseInt(objtopfirefoxoffset)+"px";
   }
   else
   {
        if(objtopoffset == 0)
        { 
			if(showtype=="up")
			{
			       $('hintdiv'+showtype).style.left=p['x']+objleftoffset-60+"px";
				    $('hintdiv'+showtype).style.top=p['y']-$('hintinfo'+showtype).offsetHeight-35+"px";
			}
			else
			{
			     $('hintdiv'+showtype).style.left=p['x']+objleftoffset-40+"px";
				 $('hintdiv'+showtype).style.top=p['y']+obj.offsetHeight-0+"px";
			}
        }
        else
        {
           $('hintdiv'+showtype).style.left=p['x']+objleftoffset-60+"px";
			$('hintdiv'+showtype).style.top=p['y']+objtopoffset+"px";
        }
   }

}



//隐藏提示层
function hidehintinfo()
{
ShowTag();
    $('hintdivup').style.display='none';
    $('hintdivdown').style.display='none';
}



//得到字符串长度
function getLen( str) 
{
   var totallength=0;
   
   for (var i=0;i<str.length;i++)
   {
     var intCode=str.charCodeAt(i);   
     if (intCode>=0&&intCode<=128)
     {
        totallength=totallength+1; //非中文单个字符长度加 1
	 }
     else
     {
        totallength=totallength+2; //中文字符长度则加 2
     }
   } 
   return totallength;
}   
   


function getposition(obj)
{
	var r = new Array();
	r['x'] = obj.offsetLeft;
	r['y'] = obj.offsetTop;
	while(obj = obj.offsetParent)
	{
		r['x'] += obj.offsetLeft;
		r['y'] += obj.offsetTop;
	}
	return r;
}

  

function cancelbubble(obj)
{
    //<textarea style="width:400px"></textarea>
    //var log = document.getElementsByTagName('textarea')[0];
	var all = obj.getElementsByTagName('*');
	
	for (var i = 0 ; i < all.length; i++)
	{
	    //log.value +=  all[i].nodeName +":" +all[i].id + "\r\n";
		all[i].onmouseover = function(e)
		{
    		if (e) //停止事件冒泡
	    	    e.stopPropagation();
		    else
			    window.event.cancelBubble = true;
			
			obj.style.display='block';
			//this.style.border = '1px solid white';
			//log.value = '鼠标现在进入的是： ' + this.nodeName + "_" + this.id;
		};
		
		all[i].onmouseout = function(e)
		{
		    if (e) //停止事件冒泡
			    e.stopPropagation();
		    else
			    window.event.cancelBubble = true;
			
	 
			if(this.nodeName == "DIV")
			{
			    obj.style.display='none';
			}
//			else
//			{
//			    obj.style.display='none';
//			}
			//this.style.border = '1px solid white';
			//log.value = '鼠标现在离开的是：' + this.nodeName + "_" + this.id;
	    };
	}

}

//当指定name的复选框选中时，激活相应的按钮
//arguments[0]为指定form，arguments[1]为复选框的name，arguments[2]～arguments[arguments.length - 1]为要激活的按钮
function checkedEnabledButton()
{
    for (var i = 0; i < arguments[0].elements.length; i++)
    {
        var e = arguments[0].elements[i];
        if (e.name == arguments[1] && e.checked)
        {
            for(var j = 2; j < arguments.length; j++)
            {
                $(arguments[j]).disabled = false;
            }
            return;
        }
    }
    for(var j = 2; j < arguments.length; j++)
    {
        $(arguments[j]).disabled = true;
    }
}

function isNumber(str)
{
    return (/^[+|-]?\d+$/.test(str));
}
//返回对象
function $(id)
{
	var obj = document.all ? document.all[id] : document.getElementById(id);
	return obj;
}
function $2(id)
{
	var obj = parent.document.all ? parent.document.all[id] : parent.document.getElementById(id);
	return obj;
}
function quanjiao2Banjiao(str) {
    var i;
    var result = '';
    for (i = 0; i < str.length; i++) {
        code = str.charCodeAt(i);
        if (code >= 65281 && code < 65373) {
            result += String.fromCharCode(str.charCodeAt(i) - 65248);
        }
        else {
            result += str.charAt(i);
        }
    }
    return result;
}
//去除空格
function JavaTrim(str) {return str.replace(/(^\s*)|(\s*$)/g, "");}

//返回对象的值
function $$(id){return JavaTrim($(id).value); 
}

function changeinfomsgstatus(type){
    if(type==1)
    {
        $('menutooltip').style.background='url(images/priority_high.gif) no-repeat 20px 15px;border:1px dotted #FDFFF2';
        //$('menutooltip').style.background='';
        $('infotext').style.display='none';
        $('iImg').src ="images/eye.gif";
        $('iImg').title='展开提示信息';
        $('iImg').onclick=function () { changeinfomsgstatus(2);};
    }
    else
    {
         //当前正处于关闭状态,应该恢复提示信息的显示
        $('menutooltip').style.background='url(images/hint.gif) no-repeat 20px 15px;border:1px dotted #FDFFF2';
       $('infotext').style.display='block';
       $('iImg').src ="images/cancel.gif";
       $('iImg').title='收起提示信息';
       $('iImg').onclick=function () { changeinfomsgstatus(1);};
    }
}
function ShowTag() {
    var tagNames = ["applet", "select","object"];
    hiddenTags = GetHiddenTagArray(tagNames);
    for (var i = 0; i < hiddenTags.length; i++) {
        var hiddenTag = hiddenTags[i];
        hiddenTag.style.visibility = "";
    }
    ShowTag2();
}
function ShowTag2() {
    var tagNames = ["applet", "select","object"];
    hiddenTags = GetHiddenTagArray2(tagNames);
    for (var i = 0; i < hiddenTags.length; i++) {
        var hiddenTag = hiddenTags[i];
        hiddenTag.style.visibility = "";
    }
}
function closediv(){
    BOX_remove('error');
    CloseOverlay_Msg();
    ShowTag();

}
//过滤输入内容：仅允许数字，长度则由maxlength属性设定
function checknum(obj){
    obj.value=obj.value.replace(/[^\d]/g,'')
}
function checknum2(obj) {
    obj.value = obj.value.replace(/[^\d\-]/g, '')
}
function checkfloat(obj) {
    obj.value = obj.value.replace(/[^\d\.]/g, '');

}
function checkfloat3(obj) {
    obj.value =obj.value.replace(/[^.\d]/g,"").replace(/^(\d+\.\d{0,3}).*/,"$1").replace(/\.+/,".");
}
function checkebalance(balance){ 
var Expression = /^([0-9]{1}\d{0,3})?$|^([0-9]{1}\d{0,3}\.\d{1,3})?$/; 
var objRex = new RegExp(Expression); 
if(objRex.test(balance)){ 
    return true; 
}else{ 
    return false; 
} 

} 
//重置表单元素的值，传值为数组，如：id1|值,id2|值,……
function ClearInputValue(a) { var temp = a.split(","); for (i = 0; i < temp.length; i++) { var temp_v = temp[i].split("|"); if (temp_v[0]) { $(temp_v[0]).value = temp_v[1]; } } }
Date.prototype.format = function(format) {

    /*
    * eg:format="YYYY-MM-dd hh:mm:ss";
    */
    var o = {
        "M+": this.getMonth() + 1, // month
        "d+": this.getDate(), // day
        "h+": this.getHours(), // hour
        "m+": this.getMinutes(), // minute
        "s+": this.getSeconds(), // second
        "q+": Math.floor((this.getMonth() + 3) / 3), // quarter
        "S": this.getMilliseconds()
        // millisecond
    }

    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "")
                .substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k]
                    : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
}
var PageHeight="0";
function resizeFrame(){
    try{
        var frames=parent.document.documentElement.getElementsByTagName("iframe");
        var ThisHeight=parseFloat(document.body.scrollHeight)+60;
        if(parseInt(ThisHeight)<800){ThisHeight=800;}
        if(ThisHeight!=PageHeight){
        //当且仅当系统检测到不同高度时生效，重置框架页高度
        PageHeight=ThisHeight;
        if(parseInt(ThisHeight)>1200){ThisHeight=1200;}
        frames[0].style.height=ThisHeight+"px";
        }
    }catch(e){
       return;
    }
    }
  function KeyDown()
{

if ((window.event.altKey)&&
    ((window.event.keyCode==37)||   //屏蔽 Alt+ 方向键 ←
    (window.event.keyCode==39)))   //屏蔽 Alt+ 方向键 →
{
  alert("不准你使用ALT+方向键前进或后退网页！");
  event.returnValue=false;
}
  /* 注：这还不是真正地屏蔽 Alt+ 方向键，
  因为 Alt+ 方向键弹出警告框时，按住 Alt 键不放，
  用鼠标点掉警告框，这种屏蔽方法就失效了。以后若
  有哪位高手有真正屏蔽 Alt 键的方法，请告知。*/
if ((event.keyCode==8) ||           //屏蔽退格删除键
    (event.keyCode==116)||           //屏蔽 F5 刷新键
    (event.ctrlKey && event.keyCode==82)){ //Ctrl + R
  event.keyCode=0;
  event.returnValue=false;
  }
if (event.keyCode==122){event.keyCode=0;event.returnValue=false;} //屏蔽F11
if (event.ctrlKey && event.keyCode==78) event.returnValue=false;   //屏蔽 Ctrl+n
if (event.shiftKey && event.keyCode==121)event.returnValue=false; //屏蔽 shift+F10
if (window.event.shiftKey){
	  alert("管理中心");
    window.event.returnValue = false;         //屏蔽 shift 加鼠标左键新开一网页
}
if (window.event.srcElement.tagName == "A" && window.event.shiftKey){
	  alert("管理中心");
    window.event.returnValue = false;         //屏蔽 shift 加鼠标左键新开一网页
}
if ((window.event.altKey)&&(window.event.keyCode==115))         //屏蔽Alt+F4
{
    window.showModelessDialog("about:blank","","dialogWidth:1px;dialogheight:1px");
    return false;
}
}


var qTipTag = "a"; var qTipX = 0; var qTipY = 45;
tooltip = { name: "qTip", offsetX: qTipX, offsetY: qTipY, tip: null }
tooltip.init = function() {
    var tipNameSpaceURI = "http://www.w3.org/1999/xhtml";
    if (!tipContainerID) { var tipContainerID = "qTip"; }
    var tipContainer = parent.document.getElementById(tipContainerID);
    if (!tipContainer) {
        tipContainer = document.createElementNS ? parent.document.createElementNS(tipNameSpaceURI, "div") : parent.document.createElement("div");
        tipContainer.setAttribute("id", tipContainerID);
        parent.document.getElementsByTagName("body").item(0).appendChild(tipContainer);
    }
    if (!document.getElementById) return;
    this.tip = parent.document.getElementById(this.name);
    if (this.tip) document.onmousemove = function(evt) { tooltip.move(evt) };
}
tooltip.move = function(evt) {
resizeFrame();
    var x = 0, y = 0;
    if (document.all) {//IE
        x = (document.documentElement && document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft;
        y = (document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop;
        x += window.event.clientX;
        y += window.event.clientY;

    } else {
        x = evt.pageX;
        y = evt.pageY;
    }
    this.tip.style.left = (x + this.offsetX+11) + "px";
    //this.tip.style.right = (this.offsetY+31+document.body.scrollLeft) + "px";
    //this.tip.style.right = (document.body.clientWidth - x) + "px";
    //this.tip.style.left = window.event.clientX+10 + "px";
    itop = (y + this.offsetY - 45 - this.tip.clientHeight);
    this.tip.style.top = parseInt(itop) < 0 ? "100px" : itop + "px";
    //this.tip.style.top = window.event.clientY + 10 + "px";
}
tooltip.show = function(text) {
    if (!this.tip) return;
    
    this.tip.innerHTML = "<font color=blue>"+text+"</font>";
    this.tip.style.display = "block";
}
tooltip.hide = function() {
    if (!this.tip) return;
    this.tip.innerHTML = "";
    this.tip.style.display = "none";
}

window.onload = function() {

    tooltip.init();
}
var oNowTop = "0";
function SetLoading()
{
    if($("loading")){
    var top = (parseInt((document.body.clientHeight - $("loading").clientHeight - 43) / 2));

    top = parseInt((document.body.clientHeight - $("loading").clientHeight - 43) / 2);
    if (top < 0) {
        top = 0;
    }
    top += MyElements.scrollTop;
    if(top!=oNowTop && isNaN(top)==false ){
    $("loading").style.top = top + "px";
    oNowTop=top;
    }}
}
setInterval("SetLoading();",100);