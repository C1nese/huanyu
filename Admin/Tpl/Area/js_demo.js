
/**
* 注意！！！！！！！！在使用本脚本时,要确定加载的jquery库
*
*/
try
{
	if ( !$ ) 
	{
		alert("未找到jquery库,无法应用area扩展!");
	}
	else
	{
		$.extend(
		{
			//默认国家
			area_default_country:'{$default_country}',

			//默认省份/州
			area_default_province:'{$default_province}',

			//默认城市
			area_default_city:'{$default_city}',

			//默认区县
			area_default_county:'{$default_county}',

			//默认乡镇
			area_default_town:'{$default_town}',


			//区域数据json
			area_json:eval({$area_data}),

			//是否显示默认的国、省、市、区县
			area_default_show:false,

			//区域选择绑定
			area_select_bind:function(countryId,provinceId,cityId,countyId,townId)
			{
				$.area_init_country(countryId);
				$.area_bind_country(countryId,provinceId,cityId,countyId,townId);
				$.area_bind_province(countryId,provinceId,cityId,countyId,townId)
				$.area_bind_city(countryId,provinceId,cityId,countyId,townId);
				$.area_bind_county(countryId,provinceId,cityId,countyId,townId);
				
				//选中默认国家
				if( $.area_default_show && $.area_default_country != '' && countryId )
				{
					$('#'+countryId).children("option[value='"+$.area_default_country+"']").attr('selected',true);
					$('#'+countryId).change();
				}
				
				//选中默认省份
				if( $.area_default_show && $.area_default_province != '' && provinceId )
				{
					$('#'+provinceId).children("option[value='"+$.area_default_province+"']").attr('selected',true);
					$('#'+provinceId).change();
				}

				//选中默认城市
				if( $.area_default_show && $.area_default_city != '' && cityId )
				{
					$('#'+cityId).children("option[value='"+$.area_default_city+"']").attr('selected',true);
					$('#'+cityId).change();
				}

				//选中默认区县
				if( $.area_default_show && $.area_default_county != '' && countyId )
				{
					$('#'+countyId).children("option[value='"+$.area_default_county+"']").attr('selected',true);
					$('#'+countyId).change();
				}

				//选中默认乡镇
				if( $.area_default_show && $.area_default_town != '' && townId )
				{
					$('#'+townId).children("option[value='"+$.area_default_town+"']").attr('selected',true);
					//$('#'+townId).change();
				}
			},

			/*
			* 初始化国家 Select 控件
			*
			* countryId		国家Select控件 的 ID 
			*/
			area_init_country:function(countryId)
			{
				$('#'+countryId).empty();
				$('#'+countryId).append("<option value=''>不限</option>");

				for( var country in $.area_json )
				{

					$('#'+countryId).append("<option value='" + country + "'>" + country + "</option>");
				}
			},

			/*
			* 绑定国家Select
			*
			* 当选择国家时,自动更新省/州Select
			*
			* countryId		国家Select控件 的 ID 
			*
			* provinceId	省份Select控件 的 ID 
			*
			* cityId		城市Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			* countyId		区县Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			* townId		乡镇Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			*/
			area_bind_country:function(countryId,provinceId,cityId,countyId,townId)
			{
				$('#'+countryId).bind('change',function()
				{
					var country = $(this).children(":selected").val();

					//清空省Select
					$('#'+provinceId).empty();
					$('#'+provinceId).append("<option value=''>不限</option>");

					//如果数据有效
					if( $.area_json[ country ] ) 
					{
						//加载国家对应的省
						for( var province in $.area_json[ country ] )
						{
							$('#'+provinceId).append("<option value='" + province + "'>" + province + "</option>");
						}
					}

					if(cityId) $('#'+cityId).empty().append("<option value=''>不限</option>");
					if(countyId) $('#'+countyId).empty().append("<option value=''>不限</option>");
					if(townId) $('#'+townId).empty().append("<option value=''>不限</option>");
				});
			},


			/*
			* 绑定省Select
			*
			* 当选择省时,自动更新市Select
			*
			* countryId		国家Select控件 的 ID
			*
			* provinceId	省份Select控件 的 ID 
			*
			* cityId		城市Select控件 的 ID
			*
			* countyId		区县Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			* townId		乡镇Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			*/
			area_bind_province:function(countryId,provinceId,cityId,countyId,townId)
			{
				$('#'+provinceId).bind('change',function()
				{
					var country  = $('#'+countryId).children(":selected").val();
					var province = $(this).children(":selected").val();
					
					//清空城市Select
					$('#'+cityId).empty();
					$('#'+cityId).append("<option value=''>不限</option>");

					//如果数据有效
					if( $.area_json[ country ] && $.area_json[ country ][ province ] ) 
					{
						//加载 省 对应的 城市
						for( var city in $.area_json[ country ][ province ] )
						{
							$('#'+cityId).append("<option value='" + city + "'>" + city + "</option>");
						}
					}

					if(countyId) $('#'+countyId).empty().append("<option value=''>不限</option>");

					if(townId) $('#'+townId).empty().append("<option value=''>不限</option>");

				});
			},


			/*
			* 绑定市Select
			*
			* 当选择市时,自动更新县Select
			*
			* countryId		国家Select控件 的 ID
			*
			* provinceId	省份Select控件 的 ID 
			*
			* cityId		城市Select控件 的 ID
			*
			* countyId		区县Select控件 的 ID
			*
			* townId		乡镇Select控件 的 ID,传值则自动清空,不传值则不处理
			*
			*/
			area_bind_city:function(countryId,provinceId,cityId,countyId,townId)
			{
				$('#'+cityId).bind('change',function()
				{
					var country  = $('#'+countryId).children(":selected").val();
					var province = $('#'+provinceId).children(":selected").val();
					var city = $(this).children(":selected").val();

					//清空区县Select
					$('#'+countyId).empty();
					$('#'+countyId).append("<option value=''>不限</option>");

					//如果数据有效
					if( $.area_json[ country ] && $.area_json[ country ][ province ] && $.area_json[ country ][ province ][ city ] ) 
					{
						//加载 城市 对应的 区县
						for( var county in $.area_json[ country ][ province ][ city ] )
						{
							$('#'+countyId).append("<option value='" + county + "'>" + county + "</option>");
						}
					}

					if(townId) $('#'+townId).empty().append("<option value=''>不限</option>");
				});
			},

			/*
			* 绑定区县Select
			*
			* 当选择区县时,自动更新乡镇Select
			*
			* countryId		国家Select控件 的 ID
			*
			* provinceId	省份Select控件 的 ID 
			*
			* cityId		城市Select控件 的 ID
			*
			* countyId		区县Select控件 的 ID
			*
			* townId		乡镇Select控件 的 ID
			*
			*/
			area_bind_county:function(countryId,provinceId,cityId,countyId,townId)
			{
				$('#'+countyId).bind('change',function()
				{
					var country		= $('#'+countryId).children(":selected").val();
					var province	= $('#'+provinceId).children(":selected").val();
					var city		= $('#'+cityId).children(":selected").val();
					var county		= $('#'+countyId).children(":selected").val();
					var town		= $(this).children(":selected").val();

					//清空乡镇Select
					$('#'+townId).empty();
					$('#'+townId).append("<option value=''>不限</option>");

					//如果数据有效
					if( $.area_json[ country ] && $.area_json[ country ][ province ] && $.area_json[ country ][ province ][ city ] && $.area_json[ country ][ province ][ city ][ county ] ) 
					{
						//加载 县区 对应的 乡镇
						for( var town in $.area_json[ country ][ province ][ city ][ county ] )
						{
							$('#'+townId).append("<option value='" + town + "'>" + town + "</option>");
						}
					}
				});
			}
		})
	}
}
catch(e)
{
	alert("未找到jquery库,无法应用area扩展!");
}