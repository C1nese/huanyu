function showForbid(id) {
    //document.getElementById("forbid").style.visibility = "visible";
    
    var ieVersion = window.navigator.appVersion;
    if (ieVersion.substr(22, 1) == "6") {
        document.getElementById("forbid").style.width = document.body.clientWidth;

        document.getElementById("forbid").style.height = document.body.clientHeight+400;
    }
    document.getElementById("allmenu").style.display = "block";
    for(i=1;i<9;i++)
    {
        if(parseInt(i)==parseInt(id))
        {
            document.getElementById("menu"+id+"").style.display = "block";
        }else
        {
        if(document.getElementById("menu"+i+"")){
        document.getElementById("menu"+i+"").style.display = "none";}
        }
    }

    //var strid = "menu" + id;
    //setCookie('memberCurModule', strid, 100);
}
function MakeMarket(id,str,str1)
{
    var strid = "menu" + id;
    setCookie('memberCurModule', strid, 100);
    setCookie('memberCurTitle', str, 100);
    setCookie('memberModuleTitle', str1, 100);
}
function hideForbid() {
    document.getElementById("forbid").style.visibility = "hidden";
}
function getCookie(name) {
    start = document.cookie.indexOf(name + "=");
    len = start + name.length + 1;
    if ((!start) && (name != document.cookie.substring(0, name.length))) {
        return null;
    }
    if (start == -1) return null;
    end = document.cookie.indexOf(';', len);
    if (end == -1) end = document.cookie.length;
    return unescape(document.cookie.substring(len, end));
}
function setCookie(objName, objValue, objHours) {//添加cookie
    str = objName + "=" + escape(objValue);
    if (objHours > 0) {//为0时不设定过期时间，浏览器关闭时cookie自动消失
        var date = new Date();
        var ms = objHours * 3600 * 1000;
        date.setTime(date.getTime() + ms);
        str = str + "; expires=" + date.toGMTString() + "; path=/";
    }
    document.cookie = str;
}
function delCookie(name) {//为了删除指定名称的cookie，可以将其过期时间设定为一个过去的时间
    var date = new Date();
    date.setTime(date.getTime() - 10000);
    document.cookie = name + "=a; expires=" + date.toGMTString();
}

 function LoadMenu()
 {
     getCookie('memberCurModule');
     getCookie('memberCurTitle');
     getCookie('memberModuleTitle');
     var divzmenu = getCookie("memberCurModule");
     var pagetitle = getCookie("memberCurTitle");
     var moduletitle = getCookie("memberModuleTitle");
     
     var divObj = document.getElementById(divzmenu);
     if (divzmenu != "menu1" && divzmenu != null) { 
       document.getElementById("menu1").style.display = "none";
     }
     if (pagetitle != null) { 
       document.getElementById("lblSitemap").innerHTML = pagetitle;
     }else{document.getElementById("lblSitemap").innerHTML = Lang_Center;}
     
    if (moduletitle != null) { 
       document.getElementById("lblSitemap2").innerHTML = moduletitle;
     }else{document.getElementById("lblSitemap2").innerHTML = Lang_Index;}
     
     if (divObj != null) {
         divObj.style.display = "block";
     }
 }



