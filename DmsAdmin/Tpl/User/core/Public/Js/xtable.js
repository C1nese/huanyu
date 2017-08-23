var input_eventSrc=null
function setStyle() {
    var tab = document.getElementById("true_content");
    var tr = tab.getElementsByTagName("tr");
    for (var i = 1; i < tr.length; i++) {
        if (i % 2 == 0) {
            tr[i].style.backgroundColor = "#ffffff";
            tr[i].onmouseover = function() {
                this.style.background = "#efefef"
            };
            tr[i].onmouseout = function() {
                this.style.background = "#ffffff"
            }
        } else {
            tr[i].style.backgroundColor = "#fafafa";
            tr[i].onmouseover = function() {
                this.style.background = "#efefef"
            };
            tr[i].onmouseout = function() {
                this.style.background = "#fafafa"
            }
        }
    }
    $("#true_content th:last-child").css("border-right","0");
    $("#true_content td:last-child").css("border-right","0");

    $("#true_content input[type='checkbox']").attr("checked",false);
}
window.onload = setStyle;

function sortBy(obj){
    var url=window.location.href;
    url=url.substr(0,url.indexOf("?"))+'?sort='+obj;
    location.href = url;
}
function order(obj1,obj2,obj3,obj4){
    //var url=window.location.href;
    if(obj4.indexOf("?") > 0){
        url=obj4+"&"+obj3+'ordername='+obj1+'&ordermode='+obj2+'&p=1';
    }else{
        url=obj4+"?&"+obj3+'ordername='+obj1+'&ordermode='+obj2+'&p=1';
    }
    //url=url+'ordername='+obj1+'&ordermode='+obj2;
    location.href = url;
}
var sumcon=0;
var checkedarr=new Array();
var mohuarr=new Array();
$(function(){    
    $("#closeBtn").click(function(){
        $("#gsBox").css("display","none");
    });
    
    $("*").click(function(event){
        event = event? event: window.event
        var thissrc=event.srcElement ? event.srcElement:event.target
        if(input_eventSrc != thissrc){
            $("#other_select").css("display","none");   
        }
        if(!$(thissrc).parents("#gsBox")[0]&&thissrc.id!='gs_1'&& !$(thissrc).parents("#other_select")[0])
        {
            $("#gsBox").css("display","none");
        };
    });
   
    for(var i=0;i<$("#sum").children().length;i++){
        var sumtdHtml=$("#sum").children(":eq("+i+")").html();
        if(sumtdHtml!="&nbsp;"){
            if(i!=0){
                $("#sum").children(":eq("+(i-1)+")").html("汇总 :");
                $("#sum").children(":eq("+(i-1)+")").css("text-align","right");
                for(var j=0;j<i-1;j++){
                    $("#sum").children(":eq("+j+")").css("border-right","0");
                }
            }
            break;
        }
    }
    for(var i=0;i<($("#sum").children().length-1);i++){
        var sumtdHtml=$("#sum").children(":eq("+i+")").html();
        var sumtdHtml1=$("#sum").children(":eq("+(i+1)+")").html();
        if(sumtdHtml=="&nbsp;" && sumtdHtml1=="&nbsp;"){
            $("#sum").children(":eq("+i+")").css("border-right","0");
        }
    }
    $("#true_content tr").not($("#trueContentTitle")).click(function(){
        var text;  
        if(navigator.appName=="Microsoft Internet Explorer"){  
            text=document.selection.createRange().text;  
        }else{  
            text=window.getSelection();  
        }
        if(text == ""){ 
            if($(this).children().attr("bgColor")!=undefined && $(this).children().attr("bgColor")!=""){
                $(this).children().removeAttr("bgColor");
                $(this).children(0).children(0).attr("checked",false);
            }else{
                $(this).children().attr("bgColor","#B7CCE6");
                $(this).children(0).children(0).attr("checked",true);
            }
        }

        //alert($(this).html())
    });
});
function searchfun(event,obj){
    
    $("#gsBox").css("left",$(".xtab").width()- $("#gsBox").width());
    $("#gsBox").css("top",$(obj).offset().top+29);
    $("#gsBox").css("display","block");
    $("#move_arrow").css("left",$("#gsBox").width()+40-($(".xtab").offset().left+$(".xtab").width()-$(obj).offset().left));
}

function selectstr(event){
    event = event? event: window.event
    input_eventSrc = event.srcElement ? event.srcElement:event.target;
    var select_name=$(input_eventSrc).attr("select_name");
    var pleft=$(input_eventSrc).offset().left;
    var ptop=$(input_eventSrc).offset().top;
    //alert(pleft);
    var width=$(input_eventSrc).width();
    $("#other_select").html($("#"+select_name).html())
    $("#other_select").css("display","block");
    $("#other_select").css("top",ptop+24);
    $("#other_select").css("left",pleft);
    $("#other_select").css("width",width+5);
    $("#other_select li").mouseover(function(){
        $(this).css("background-color","#9dc5e8");
        $(this).css("color","#FFF");
    }).mouseout(function(){
        $(this).css("background-color","#FFF");
        $(this).css("color","#000");
    });
}
function selectfun1(obj1,obj2){
    $("input[id='s"+obj2+"']").val(obj1);
    $("#other_"+obj2).hide();
}
        
function resetfun(){
    $("#searchform input[type='text']").val("");
    $("#searchform input[type='checkbox']").attr("checked",false);
}
function editfun(obj,obj2){
    $(obj2).offset().left
    $("#editList").css("left",$(obj2).offset().left-$("#editList").width()+40);
    $("#editList").css("top",$(obj2).offset().top+40)
    $("#editList").css("display","block");
    $.post("/admin.php?m=TableList&a=editList",{url:obj},function(data){
        $("#editList>div").html(data);
        $( "#sortable" ).sortable();
        $("#sortable li:even").css("background-color","#fafafa");        
        $("#sortable li").mousemove(function(){
            $(this).css("background-color","#efefef");
        }).mouseout(function(){
            $(this).css("background-color","#fff");
        });
    });
    
}
function closeEdit(){
    $("#editList").css("display","none");
}
function resetTitle(obj){
    $.post("/admin.php?m=TableList&a=resetTitle",{url:obj},function(data){
        if(data!=""){
            alert(data);
        }else{
            window.location.href=window.location.href;
        }
    });
}
function setTitleName(obj,obj1){
    $("#title_"+obj1).css("display",'block');
    $("#span_"+obj1).html("");
    $("#title_"+obj1).select();
}
function setListStatus(obj,obj1){
    var status=$(obj).html();
    var setStatus=$("#titleStatus_"+obj1).html();
    if(setStatus=="显示"){
        $("#titleStatus_"+obj1).css("color","red");
    }else{
        $("#titleStatus_"+obj1).css("color","green");
    }    
    $(obj).html(setStatus);
    $("#titleStatus_"+obj1).html(status);
}
function outTitleName(obj){
    $("#title_"+obj).css("display",'none');
    $("#span_"+obj).html($("#title_"+obj).val())
}
function conformEditList(obj){
    var con=$("#sortable").children().length;
    var showList="";var sortList="";var titleList="";
    for(var i=0;i<con;i++){
        if($("#titleStatus_"+i).html()=="显示"){
            showList+=',1';
        }else{
            showList+=',0';
        }  
        sortList +=','+$("#sortable").children(":eq("+i+")").attr('id').substring(10); 
        titleList += ','+$("#title_"+i).val()
        
    }
   // alert(sortList)
    $.post("/admin.php?m=TableList&a=conformEditList",{url:obj,showList:showList,sortList:sortList,titleList:titleList},function(data){
        if(data!=""){
            alert(data);
        }else{
            window.location.href=window.location.href;
        }
    });
}

function xsOperation(obj){
    var frm=$("#xsOperation").attr("action",$("#xsOperation").attr("action")+obj);
    $("#xsOperation").submit();  
}
