//ͨ�ü�����ʾ�ӿ�

//����������---------------------------------------------------
//----ǰ̨����ʵ����
function user_getTotalzf(salename){
	var postdata={};
	postdata['province'] = $("#province_id  option:selected").text();//ʡ��
 	postdata['weight'] = $("#totalweight").html();//������		
 	postdata['zongjia'] = $("#totalprice").html();
	postdata['userid'] = $("#userid").val();
	postdata['lv'] = $("#lv").val();
	postdata['salename'] = salename;//����
		
   	$.post('/index.php?s=/User/Sale/wuliufei',postdata,function(data){
		eval("var json="+data);
		var userinfo = json.data;
		$("#zk").html(userinfo['zk']*10);
		$("#wlf").html(userinfo['wlf']);
		$("#totalzf").html(userinfo['totalzf']);
	});
}

//----��̨����ʵ����
function admin_getTotalzf(salename){
	var postdata={};
	postdata['province'] = navTab.getCurrentPanel().find("#province_id  option:selected").text();//ʡ��
 	postdata['weight']   = navTab.getCurrentPanel().find("#totalweight").html();//������		
 	postdata['zongjia']  = navTab.getCurrentPanel().find("#totalprice").html();//��Ʒ�ܼ�
 	postdata['userid']   = navTab.getCurrentPanel().find("#userid").val();//���
 	postdata['lv']   = navTab.getCurrentPanel().find("#lv").val();//���
	postdata['salename'] = salename;//����

   	$.post('/index.php?s=/Admin/Sale/wuliufei',postdata,function(data){
		eval("var json="+data);
		var userinfo = json.data;
		navTab.getCurrentPanel().find("#zk").html(userinfo['zk']*10);
		navTab.getCurrentPanel().find("#wlf").html(userinfo['wlf']);
		navTab.getCurrentPanel().find("#totalzf").html(userinfo['totalzf']);
	});
}
//����������---------------------------------------------------