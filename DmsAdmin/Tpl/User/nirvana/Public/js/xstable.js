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
$(function(){
   $( "#sortable" ).sortable();
   $("#sortable li:even").css("background-color","#fff");        
   $("#sortable li").mousemove(function(){
	    var bg = $(this).css("background-color");
        $(this).css("background-color","#efefef");
   }).mouseout(function(){
        $(this).css("background-color","#fff");
   });
});
        
function resetfun(){
    $("#searchform input[type='text']").val("");
    $("#searchform input[type='checkbox']").attr("checked",false);
}
function closeEdit(){
    window.close();
}
function resetTitle(obj){
    $.post("/admin.php?m=TableList&a=resetTitle",{url:obj},function(data){
        if(data!=""){
            alert(data);
        }else{
            window.close();
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
    $.post("/admin.php?m=TableList&a=conformEditList",{url:obj,showList:showList,sortList:sortList,titleList:titleList},function(data){
        if(data!=""){
            alert(data);
        }else{
            window.close();
        }
    });
}

function xsOperation(obj){
    var frm=$("#xsOperation").attr("action",$("#xsOperation").attr("action")+obj);
    $("#xsOperation").submit();  
}
