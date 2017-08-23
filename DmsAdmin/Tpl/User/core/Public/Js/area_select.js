/**
* 注意！！！！！！！！在使用本脚本时,要确定加载的jquery库
*
*/

/*
* 绑定省Select
*
* 当选择省时,自动更新市Select
*
*
* county,town  传值 则 自动清空,不传值 则不做处理
*
*/
function bind_province(province,city,county,town)
{
	$('#'+province).bind('change',function()
	{
		var pid = $(this).children(":selected").val();
		//alert(pid);
		$.get('/index.php?m=Public&a=loadArea&type=city&pid='+pid,function(data)
		{
			try
			{
				eval("var json="+data);
				var city_list = json.data;
				$('#'+city).empty().append("<option value=''>不限</option>");

				for(i=0;i<city_list.length;i++)
				{
					$('#'+city).append("<option value='"+city_list[i].cityID+"'>"+city_list[i].city+"</option>");
				}

				if(county) $('#'+county).empty().append("<option value=''>不限</option>");
				if(town) $('#'+town).empty().append("<option value=''>不限</option>");
			}
			catch(e)
			{
				alert(data);
				alert("网络异常!");
			}

		});
	});
}

/*
* 绑定市Select
*
* 当选择市时,自动更新县Select
*
*
* town  传值 则 自动清空,不传值 则不做处理
*
*/
function bind_city(city,county,town)
{
	$('#'+city).bind('change',function()
	{
		var pid = $(this).children(":selected").val();
		//alert(pid);
		$.get('/index.php?m=Public&a=loadArea&type=county&pid='+pid,function(data)
		{
			try
			{
				eval("var json="+data);
				var county_list = json.data;
				$('#'+county).empty().append("<option value=''>不限</option>");

				for(i=0;i<county_list.length;i++)
				{
					$('#'+county).append("<option value='"+county_list[i].countyID+"'>"+county_list[i].county+"</option>");
				}

				if(town) $('#'+town).empty().append("<option value=''>不限</option>");
			}
			catch(e)
			{
				alert(data);
				alert("网络异常!");
			}

		});
	});
}

/*
* 绑定县Select
*
* 当选择县时,自动更新镇Select
*
*/
function bind_county(county,town)
{
	$('#'+county).bind('change',function()
	{
		var pid = $(this).children(":selected").val();
		//alert(pid);
		$.get('/index.php?m=Public&a=loadArea&type=town&pid='+pid,function(data)
		{
			try{
				eval("var json="+data);
				var town_list = json.data;
				$('#'+town).empty();
				$('#'+town).append("<option value=''>不限</option>");
				for(i=0;i<town_list.length;i++)
				{
					$('#'+town).append("<option value='"+town_list[i].townID+"'>"+town_list[i].town+"</option>");
				}
			}catch(e)
			{
				alert(data);
				alert("网络异常!");
			}

		});
	});
}