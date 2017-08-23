<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<!-- META SECTION -->
	<title><?php echo ($SYSTEM_TITLE); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<link rel="icon" href="__TMPL__Public/favicon.ico" type="image/x-icon" />
	<!-- END META SECTION -->

	<!-- CSS INCLUDE -->
	
	
	<link rel="stylesheet" type="text/css" id="theme" href="__TMPL__Public/css/theme-default.css"/>
	
	<link rel="stylesheet" type="text/css" href="__TMPL__Public/css/style.css"/>
	<!-- EOF CSS INCLUDE -->
	<!-- START PRELOADS -->
	<!-- <audio id="audio-alert" src="__TMPL__Public/audio/alert.mp3" preload="auto"></audio>
	<audio id="audio-fail" src="__TMPL__Public/audio/fail.mp3" preload="auto"></audio> -->
	<!-- END PRELOADS -->

	<!-- START SCRIPTS -->
	<!-- START PLUGINS -->
	<script type="text/javascript" src="__TMPL__Public/js/plugins/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="__TMPL__Public/js/plugins/jquery/jquery-ui.min.js"></script>
	<script type="text/javascript" src="__TMPL__Public/js/plugins/bootstrap/bootstrap.min.js"></script>
	<script type="text/javascript" src="__TMPL__Public/js/plugins/jquery/jquery-migrate.min.js"></script>
	<script type="text/javascript" src="__TMPL__Public/js/plugins/scrolltotop/scrolltopcontrol.js"></script>
	<script type="text/javascript" src="__TMPL__Public/js/btx.js"></script>
	<!-- START THIS PAGE PLUGINS-->
	<!-- END THIS PAGE PLUGINS-->
	<!-- END PLUGINS -->
	<script src="__TMPL__Public/js/transfer_plus.js"></script>
	<script src="__TMPL__Public/js/main.js"></script>
	<style>
		.head-table{
			color:#FFF;
			font-size:14px;
		}
		.btn.btn-rounded{
			color:#FFF
		}
		.theme-settings .ts-button{
			border-radius:0
		}
		.ts-button{
			border-bottom:1px solid #908e8e
		}
	</style>
</head>
<body>
<div style="display:none">
<script language="javascript" type="text/javascript" src="//js.users.51.la/19173246.js"></script>
	<noscript style="display:none">
		<a href="//www.51.la/?19173246" target="_blank">
		<img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="//img.users.51.la/19173246.asp" style="display:none" />
		</a>
	</noscript>
</div>
<script type="text/javascript">
	$(function(){
		$('.ts-button').each(function(){
			$(this).click(function(){
				if($(this).attr('cont') == 'close'){
					$('.theme-settings').removeClass('active');
					$('.ts-button[cont="kind"]').css('border-bottom-left-radius','4px');
					$(this).hide();
				}else{
					$('.theme-settings').addClass('active');
					$('.ts-body').hide();
					$('.ts-button[cont="kind"]').css('border-bottom-left-radius','');
					$('.'+$(this).attr('cont')).show();
					$('.ts-button[cont="close"]').show();
				}
			});
		});
		$('button').each(function(){
			$(this).click(function(){
				$(this).parent().parent().children('.list').hide();
				$(this).parent().parent().children('.'+$(this).attr('href')).show();
			});
		});
		$('.x-navigation-minimize').click(function(){
			var logo = $("#nlogo");
			if(logo.text().length>2){
				logo.text('');
			}else{
 				logo.text('商 业 联 盟 推 广 事 业 中 心');
			}
		});
	})
</script>

<!-- START PAGE CONTAINER -->
<div class="page-container">

	<!-- START PAGE SIDEBAR -->
	<div class="page-sidebar">
		<!-- START X-NAVIGATION -->
		<ul id='btx_nav' class="x-navigation">
			<li class="xn-logo">
				<a href="__GROUP__/Index/index" id="nlogo">商 业 联 盟 推 广 事 业 中 心</a>
				<a href="#" class="x-navigation-control"></a>
			</li>
			<li class="xn-profile">
				<a href="__GROUP__/User/edit" class="profile-mini">
					<img src="<?php echo (($userinfo["头像"])?($userinfo["头像"]):'__TMPL__Public/assets/images/users/no-image.jpg'); ?>" alt="<?php echo ($userinfo["编号"]); ?>" style="min-height: 34px;min-width: 34px;"/>
				</a>
				<div class="profile">
					<div class="profile-image">
						<img src="<?php echo (($userinfo["头像"])?($userinfo["头像"]):'__TMPL__Public/assets/images/users/no-image.jpg'); ?>" alt="<?php echo ($userinfo["编号"]); ?>" style="min-height: 100px;min-width: 100px;"/>
					<?php if(($userinfo['商务中心级别'] > 1)): ?><img src="__TMPL__Public/assets/images/users/logo.png" alt="联盟机构" style="width:35px;height:35px;min-width:auto;min-height:auto;position:absolute;top: 51%;left:55%">
					</div><?php endif; ?>
					<div class="profile-data">
						<div class="profile-data-name"><?php echo ($userinfo["编号"]); ?>  *&nbsp <span style="color:#FF680F;"><?php echo ($userlevel['用户级别']['byname']); ?></span></div>       
					</div>
					<div class="profile-controls">
						<a href="__GROUP__/User/edit" class="profile-control-left"><span class="fa fa-info"></span></a>
						 <a href="__GROUP__/Mail/index" class="profile-control-right">	 	
					 	<span class="fa fa-envelope"></span>
					 	<!--<img class="admin-pic img-circle" src="http://api.randomuser.me/portraits/thumb/men/10.jpg" alt="" style="position: relative;left: 1530px;top: -102px;">-->
						 </a> 
						<div class="informer informer-danger" style="margin-top:30px;margin-right:-5px;"><?php if($mailcount_new > 0): echo ($mailcount_new); endif; ?></div>
					</div>
				</div>
			</li>
			<li class="active">
				<a href="__GROUP__/Index/index"><span class="fa fa-desktop"></span> <span class="xn-text">系统首页<b id="zbcsd"></b></span></a>
			</li>
			<?php if(isset($menu)): if(is_array($menu)): foreach($menu as $key=>$vo): ?><li class="xn-openable"><a href="javascript:;"><span class="<?php echo ($vo["icon"]); ?>"></span> <span class="xn-text"><?php echo ($key); ?></span></a>
					<ul>
						<?php if(is_array($vo["menus"])): foreach($vo["menus"] as $key=>$val): ?><li>
							<a class="report <?php if($val['action'] == $now_action && $val['model'] == $now_model): ?>navul-a<?php endif; ?>" href="__GROUP__/<?php echo ($val["model"]); ?>/<?php echo ($val["action"]); ?>" id="<?php echo ($val["title"]); ?>"><span class="<?php echo ($val["icon"]); ?>"></span><?php echo ($val[title]); ?></a>
						</li><?php endforeach; endif; ?>
						<div class="clear"></div>
					</ul>
                </li><?php endforeach; endif; endif; ?>

		</ul>
		<!-- END X-NAVIGATION -->
	</div>
	<!-- END PAGE SIDEBAR -->

	<!-- PAGE CONTENT -->
	<div class="page-content">

		<!-- START X-NAVIGATION VERTICAL -->
		<ul class="x-navigation x-navigation-horizontal x-navigation-panel" style='position:relative;'>
			<!-- TOGGLE NAVIGATION -->
			<li class="xn-icon-button">
				<a href="#" class="x-navigation-minimize"><span class="fa fa-dedent"></span></a>
			</li>
			<!-- END TOGGLE NAVIGATION -->
			<!-- SEARCH -->
			 <li >
				<a href="./" class="icon icon-home">
					<i class="icon icon-home"></i>
				</a>				
			</li>
		
		<!-- 	
		<li class="icon icon-home" title="" data-original-title="home"></li> -->



			<!-- END SEARCH -->

			<!-- <li class="xn-icon-button"><a href="#" id="zbx"></a></li>
			<li class="xn-icon-button"><a href="#" id="zby"></a></li>

			<li class="xn-icon-button"><a href="#" id="zbmukf"></a></li> -->
			<!-- SIGN OUT -->
			<li class="xn-icon-button pull-right">
				<a href="#" class="mb-control" data-box="#mb-signout"><span class="fa fa-sign-out"></span></a>
			</li>
			<!-- END SIGN OUT -->
			<!-- MESSAGES -->
			<li class="xn-icon-button pull-right">
				<!-- <a href="#"><span class="fa fa-comments"></span></a> -->
				<a href="#"><span class="fa fa-envelope"></span></a> 

				<div class="informer informer-danger"><?php if($mailcount_new > 0): echo ($mailcount_new); endif; ?></div>
				<div class="panel panel-primary animated zoomIn xn-drop-left xn-panel-dragging">
					<div class="panel-heading">
						<h3 ><span class="fa fa-comments"></span>邮件</h3>
						<div class="pull-right">
							<span class="label label-danger"><?php echo ($mailcount_new); ?>新</span>
						</div>
					</div>
					<div class="panel-body list-group list-group-contacts scroll" style="height: 200px;">
						<?php if(is_array($mail)): $i = 0; $__LIST__ = $mail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$m): $mod = ($i % 2 );++$i;?><a href="__GROUP__/Mail/view/id/<?php echo ($m["id"]); ?>" class="list-group-item">
							<div class="list-group-status status-online"></div>
							<img src="<?php echo (($m["头像"])?($m["头像"]):'TMPL__Public/assets/images/users/no-image.jpg'); ?>" class="pull-left" alt="<?php echo ($m["发件人"]); ?>"/>
							<span class="contacts-title"><?php echo ($m["发件人"]); ?></span>
							<p><?php echo ($m["标题"]); ?></p>
						</a><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
					<div class="panel-footer text-center">
						<a href="__GROUP__/Mail/index">查看全部邮件</a>
					</div>
				</div>
			</li>
			<li class="xn-icon-button pull-right">
				<a href="http://news.looksr.com" target="_blank"><span class="fa fa-globe"></span></a>
			</li>
            
			<!-- END MESSAGES -->
			<!-- TASKS -->
			
			<!-- END TASKS -->
			<!-- LANG BAR -->

            <!-- END LANG BAR -->
		</ul>
		<!-- 菜单悬浮 -->
		<div id="btx" style="display:none;position:fixed;top:0;z-index:9999;">
		<!-- START X-NAVIGATION -->
		<ul id="btx_top" class="x-navigation">
			<li class="xn-logo">
				<a href="__GROUP__/Index/index" style="background-size: 100% 100%;">商 业 联 盟 推 广 事 业 中 心</a>
				<a id='btx_control' href="#" class="x-navigation-control"></a>
			</li>
			<li class="xn-profile">
				<a href="__GROUP__/User/edit" class="profile-mini">
					<img src="" alt="<?php echo ($userinfo["编号"]); ?>" style="min-height: 34px;min-width: 34px;"/>
				</a>
				<div class="profile">
					<div class="profile-image">
						<img src="" alt="<?php echo ($userinfo["编号"]); ?>" style="min-height: 100px;min-width: 100px;"/>
					</div>
					<div class="profile-data">
						<div class="profile-data-name"><?php echo ($userinfo["编号"]); ?> - <span style="color:#FF680F;"><?php echo ($userlevel['用户级别']['byname']); ?></span></div>
					</div>
					<div class="profile-controls">
						<a href="__GROUP__/User/edit" class="profile-control-left"><span class="fa fa-info"></span></a>
						<a href="__GROUP__/Mail/index" class="profile-control-right"><span class="fa fa-envelope"></span></a><div class="informer informer-danger" style="margin-top:30px;margin-right:-5px;"><?php if($mailcount_new > 0): echo ($mailcount_new); endif; ?></div>
					</div>
				</div>
			</li>
			<li class="active">
				<a href="__GROUP__/Index/index"><span class="fa fa-desktop"></span> <span class="xn-text">系统首页<b id="zbcsd"></b></span></a>
			</li>
			<?php if(isset($menu)): if(is_array($menu)): foreach($menu as $key=>$vo): if(($userPrentmenu && in_array($key,$userPrentmenu)) or $userinfo['用户级别'] > 10): ?><li class="xn-openable"><a href="javascript:;"><span class="<?php echo ($vo["icon"]); ?>"></span> <span class="xn-text"><?php echo ($key); ?></span></a>
					<ul>
						<?php if(is_array($vo["menus"])): foreach($vo["menus"] as $key=>$val): if(!$userMenuPower or in_array($val['model'].'-'.$val['action'],$userMenuPower)): ?><li>
						<a class="report <?php if($val['action'] == $now_action && $val['model'] == $now_model): ?>navul-a<?php endif; ?>" href="__GROUP__/<?php echo ($val["model"]); ?>/<?php echo ($val["action"]); ?>" id="<?php echo ($val["title"]); ?>"><span class="<?php echo ($val["icon"]); ?>"></span><?php echo ($val[title]); ?></a>
						</li><?php endif; endforeach; endif; ?>
						<div class="clear"></div>
					</ul>
                </li><?php endif; endforeach; endif; endif; ?>

		</ul>
		<!-- END X-NAVIGATION -->
	<!-- END PAGE SIDEBAR -->

		<!-- START X-NAVIGATION VERTICAL -->
		<ul class="x-navigation x-navigation-horizontal x-navigation-panel btx_top" style='position:relative;'>
			<!-- TOGGLE NAVIGATION -->
			<li class="xn-icon-button">
				<a href="#" class="x-navigation-minimize"><span class="fa fa-dedent"></span></a>
			</li>
			<!-- END TOGGLE NAVIGATION -->
			<!-- SEARCH -->
			<li >
				<a href="./" class="btn btn-primary btn-condensed">
					<i class="glyphicon glyphicon-home"></i>
				</a>				
			</li>
			<!-- END SEARCH -->

			<!-- <li class="xn-icon-button"><a href="#" id="zbx"></a></li>
			<li class="xn-icon-button"><a href="#" id="zby"></a></li>

			<li class="xn-icon-button"><a href="#" id="zbmukf"></a></li> -->
			<!-- SIGN OUT -->
			<li class="xn-icon-button pull-right">
				<!-- <a href="#" class="mb-control" data-box="#mb-signout"><span class="fa fa-sign-out"></span></a> -->

			</li>
			<!-- END SIGN OUT -->
			<!-- MESSAGES -->
			<li class="xn-icon-button pull-right">
				<!-- <a href="#"><span class="fa fa-comments"></span></a> -->
				<a href="#"><span class="fa fa-envelope"></span></a>
				<div class="informer informer-danger"><?php if($mailcount_new > 0): echo ($mailcount_new); endif; ?></div>
				<div class="panel panel-primary animated zoomIn xn-drop-left xn-panel-dragging">
					<div class="panel-heading">
						<h3 ><span class="fa fa-comments"></span>邮件</h3>
						<div class="pull-right">
							<span class="label label-danger"><?php echo ($mailcount_new); ?>新</span>
						</div>
					</div>
					<div class="panel-body list-group list-group-contacts scroll" style="height: 200px;">
						<?php if(is_array($mail)): $i = 0; $__LIST__ = $mail;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$m): $mod = ($i % 2 );++$i;?><a href="__GROUP__/Mail/view/id/<?php echo ($m["id"]); ?>" class="list-group-item">
							<div class="list-group-status status-online"></div>
							<img src="<?php echo (($m["头像"])?($m["头像"]):'TMPL__Public/assets/images/users/no-image.jpg'); ?>" class="pull-left" alt="<?php echo ($m["发件人"]); ?>"/>
							<span class="contacts-title"><?php echo ($m["发件人"]); ?></span>
							<p><?php echo ($m["标题"]); ?></p>
						</a><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
					<div class="panel-footer text-center">
						<a href="__GROUP__/Mail/index">查看全部邮件</a>
					</div>
				</div>
			</li>
		</ul>
	</div>
		<script>
		function change_lang(lang){
			Cookies.set("lang",lang);
			  location.reload();
		}
		</script> 
		<!-- END X-NAVIGATION VERTICAL -->

<script type="text/javascript" src="__TMPL__Public/js/layer/layer.js"></script>
<style type="text/css">
.gray {
        -webkit-filter: grayscale(100%);
        -moz-filter: grayscale(100%);
        -ms-filter: grayscale(100%);
        -o-filter: grayscale(100%);
        filter: grayscale(100%);
        filter: gray;
}
.layui-layer{
    top:10vh !important;
}
.layui-layer-content{
    height:100% !important;
}
.layui-layer-content>img{
    height:100% !important;
    width:auto;
}
</style>
<!-- START BREADCRUMB -->
<ul class="breadcrumb">
    <li><a href="#"><?php echo GET_F_L('当前位置');?></a></li>
    <li class="active"><?php echo ($nowtitle); ?></li>
</ul>
<!-- END BREADCRUMB -->
<!-- PAGE CONTENT WRAPPER -->
<div class="page-content-wrap">
    <!-- START WIDGETS -->
<div class="row">

    <div class="col-md-3">

        <div class="widget widget-success widget-item-icon">
            <div class="widget-item-left">
                <span class="fa fa-credit-card"></span>
            </div>
            <div class="widget-data">
                <div class="widget-int num-count"><?php echo ($funbank["申购钱包"]["num"]); ?></div>
                <div class="widget-title">申购钱包</div>
                <div class="widget-subtitle">申购金用于进行用户申购注册</div>
            </div>
            <div class="widget-controls">
                <a href="#" class="widget-control-right"><span class="fa fa-times"></span></a>
            </div>
        </div>

    </div>



    <div class="col-md-3">

        <div class="widget widget-info widget-item-icon">
            <div class="widget-item-left">
                <span class="fa fa-yen"></span>
            </div>
            <div class="widget-data">
                <div class="widget-int num-count"><?php echo ($funbank["用户收益"]["num"]); ?></div>
                <div class="widget-title">用户收益</div>
                <div class="widget-subtitle">用户收益内的金额可以进行提现</div>
            </div>
            <div class="widget-controls">
                <a href="#" class="widget-control-right"><span class="fa fa-times"></span></a>
            </div>
        </div>

    </div>


    <div class="col-md-3">

        <!--<div class="widget widget-danger widget-item-icon">
                                <div class="widget-item-left">
                                    <span class="glyphicon glyphicon-shopping-cart"></span>
                                </div>
                                <div class="widget-data">
                                    <div class="widget-int num-count"><?php echo ($funbank["换购钱包"]["num"]); ?></div>
                                    <div class="widget-title">换购钱包</div>
                                    <div class="widget-subtitle">换购积分用于兑换联盟企业商品</div>
                                </div>
                                <div class="widget-controls">                                
                                    <a href="#" class="widget-control-right"><span class="fa fa-times"></span></a>
                                </div>                            
                            </div>-->
        <div class="widget widget-info widget-carousel" style="height:112px;">
            <div class="owl-carousel" id="owl-example">
                <?php if(is_array($funbank)): foreach($funbank as $key=>$fun_bank): ?><div>
                        <div class="widget-int" style="line-height: 32px;"><?php echo ($fun_bank["num"]); ?></div>
                        <div class="widget-title" style="font-size: 16px; line-height: 24px;margin-bottom:0"><?php echo GET_F_L($fun_bank['name']);?></div>
                        <div class="widget-subtitle">换购积分用于兑换联盟企业商品</div>
                    </div><?php endforeach; endif; ?>
            </div>
            <div class="widget-controls">
                <a href="#" class="widget-control-right widget-remove" data-toggle="tooltip" data-placement="top" title="Remove Widget"><span class="fa fa-times"></span></a>
            </div>
        </div>

    </div>
    <div class="col-md-3">

        <div class="widget widget-warning widget-item-icon">
            <div class="widget-item-left">
                <span class="fa fa-money"></span>
            </div>
            <div class="widget-data">
                <div class="widget-int num-count"><?php echo ($funbank["持股数量"]["num"]); ?></div>
                <div class="widget-title">持股数量</div>
                <div class="widget-subtitle">您目前持有的股权数量总额</div>
            </div>
            <div class="widget-controls">
                <a href="#" class="widget-control-right"><span class="fa fa-times"></span></a>
            </div>
        </div>

    </div>

</div>
</div>

    <!-- END WIDGETS -->
    <div class="row">

        <div class="col-md-8" >


			<div class="row">
                <!-- 平台公告 -->
                <div class="panel  panel-default panel-colorful">
                    <div class="panel-heading">
                        <div class="panel-title-box">
                        	<span class="fa fa-bullhorn" style="font-size:18px;color:#3c3c3c;"></span><strong style="font-size:18px;color:#3c3c3c;">&nbsp;<?php echo GET_F_L('最新消息');?></strong>
                       </div>
                       <ul class="pull-right" style="margin-bottom: 0;list-style: none">
                           <li><a href="__GROUP__/User/viewNotice" class="btn btn-default"
                                  style="background-color:#2FB4E2;color:#FFF">更多消息</a></li>
                       </ul>
                    </div>
                    <div class="panel-body list-group list-group-contacts">
                        <ul class="list-group">                    
                            <?php if(is_array($nownotice)): foreach($nownotice as $key=>$vo): ?><li class="list-group-item"  style="line-height: 45px;height: 45px;">
                                    <ul class="list-inline list-inline-notice" style="line-height: 26px;height: 26px;padding-top:0px">
                                        <li class="pull-right">
                                            <?php if(($vo["type"]) == "problem"): ?><span class="label label-warning label-form" style="margin:0"><?php echo GET_F_L('帮助');?></span><?php endif; ?>
                                            <?php if(($vo["type"]) == "news"): ?><span class="label label-info label-form" style="margin:0"><?php echo GET_F_L('新闻');?></span><?php endif; ?>
                                        </li>
                                        <li class="pull-right"><?php echo date('Y-m-d H:i',$vo['创建时间']);?></li>
                                        <li class="gg_title"><span class="glyphicon glyphicon-volume-up"
                                                                   style="top:2px"></span><a
                                                href="__GROUP__/User/showNotice/id/<?php echo ($vo["id"]); ?>" class="text-primary"
                                            <?php if(($vo["标题特效"]) == "是"): ?>style='color:red !important;'<?php endif; ?>
                                            >&nbsp;<?php echo GET_F_L($vo[标题],'notice',$vo['id'],'tit');?></a></li>
                                    </ul>
                                </li><?php endforeach; endif; ?>
                        </ul>
                    </div>
                </div>
                <!-- END 平台公告 -->
            </div>
            
            <div class="row">

                <div class="col-md-6" style="padding-left: 0px; padding-right: 3px;">

                    <!-- START SALES & EVENTS BLOCK -->
                    <div class="panel panel-colorful">
                        <div class="panel-heading">
                            <div class="panel-title-box">
                                <span class="fa fa-rocket" style="font-size:18px;color:#3c3c3c;"></span><strong style="font-size:18px;color:#3c3c3c;">&nbsp;<?php echo GET_F_L('推广补贴');?></strong>
                                <span></span>
                            </div>
                           
                        </div>
                        <div class="panel-body padding-0">
                            <div class="chart-holder" id="dashboard-line-1" style="height: 220px;"></div>
                        </div>
                    </div>
                    <!-- END SALES & EVENTS BLOCK -->

                </div>

                <div class="col-md-6" style="padding-left: 3px; padding-right: 0px;">

                    <!-- START USERS ACTIVITY BLOCK -->
                    <div class="panel panel-colorful">
                        <div class="panel-heading">
                            <div class="panel-title-box">
                                <span class="fa fa-signal" style="font-size:18px;color:#3c3c3c;"></span><strong
                                    style="font-size:18px;color:#3c3c3c;">&nbsp;<?php echo GET_F_L('推广分红');?></strong>
                                <span></span>
                            </div>
                            <ul class="panel-controls" style="margin-top: 2px;">
                                
                               
                            </ul>
                        </div>
                        <div class="panel-body padding-0">
                            <div class="chart-holder" id="dashboard-line-2" style="height: 220px;"></div>
                        </div>
                    </div>
                    <!-- END USERS ACTIVITY BLOCK -->

                </div>

            </div>
			
</div>
		
		

        <div class="col-md-4">

			<div class="row">
                <!-- 新品上线 -->
                <div class="panel panel-default panel-colorful">
                    <div class="panel-heading">
                        <span class="fa fa-bell" style="font-size:18px;color:#3c3c3c;"></span><strong style="font-size:18px;color:#3c3c3c;">&nbsp;<?php echo GET_F_L('联盟推荐产品');?></strong>
                        <ul class="panel-controls">
                            <!-- <li><a href="#"><span class="fa fa-plus"></span></a></li> -->
                        </ul>
                    </div>
                    <div class="panel-body list-group list-group-contacts">
                    	<?php if(is_array($recommend1)): $i = 0; $__LIST__ = $recommend1;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo1): $mod = ($i % 2 );++$i;?><a href="<?php echo (urldecode($vo1["url"])); ?>" class="list-group-item"  target="_blank">
                            <div class="list-group-status status-sort2" style="margin-top:10px;"></div>
                            <img src="<?php echo ($vo1["图片"]); ?>" class="pull-left" alt="<?php echo ($vo1["名称"]); ?>" style="border-radius: 0; width: 65px;"/>
                            <span class="contacts-title"><?php echo ($vo1["名称"]); ?></span>
                            <p><b class="text-success">市场价：<?php echo ($vo1["score"]); ?> </b> </p>
                            <p><b class="text-danger">联盟价：￥<?php echo ($vo1["价格"]); ?> </b> </p>

                        </a><?php endforeach; endif; else: echo "" ;endif; ?>
                    </div>
                </div>
                <!-- END 新品上线 -->
            </div>   
        </div>
    </div>

    <!-- START DASHBOARD CHART -->
    <div class="block-full-width">
        <div class="panel-body padding-0 hidden">
            <div class="chart-holder" id="dashboard-donut-1" style="height: 200px;"></div>
        </div>
        <div class="chart-legend">
            <div id="dashboard-legend"></div>
        </div>
    </div>
    <!-- END DASHBOARD CHART -->

</div>
<!-- END PAGE CONTENT WRAPPER -->
</div>
<!-- END PAGE CONTENT -->

</div>
<!-- END PAGE CONTAINER -->
<!-- MESSAGE BOX-->
<div class="message-box animated fadeIn" data-sound="alert" id="mb-signout">
	<div class="mb-container">
		<div class="mb-middle">
			<div class="mb-title"><span class="fa fa-sign-out"></span> <strong><?php echo GET_F_L('退出');?></strong> ?</div>
			<div class="mb-content">
				<p><?php echo GET_F_L('你确定要退出吗？');?></p>
				<p><?php echo GET_F_L('如果你想继续工作点击否；点击是注销当前用户。');?></p>
			</div>
			<div class="mb-footer">
				<div class="pull-right">
					<a href="__GROUP__/Public/logout" class="btn btn-success btn-lg"><?php echo GET_F_L('是');?></a>
					<button class="btn btn-default btn-lg mb-control-close"><?php echo GET_F_L('否');?></button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END MESSAGE BOX-->

    <?php if($zhici['应用'] == 1): ?><!doctype html>
<style>
.layui-layer{	
	margin-top:50vh;
	top:-250px !important;
	
}
</style>
<div id="conteoer" style="display: none;">
	<div class="content-frame pages-edit-liv">
		<div class="col-md-12" style="padding: 0px">			
				<div class="modal-dialog" style="margin: 0px auto">
					<div class="col-md-12" style="padding: 0px">
							<div class="panel panel-colorful" style="padding: 0px; margin: 0px;border-radius: 0;">
								<div class="" style="padding:0px 20px 20px 20px;height:400px;overflow: auto;">
									<input id="maskinfo" value="<?php echo ($userinfo["编号"]); ?>" hidden>
									<h1 style="text-align: center"><?php echo ($zhici["标题"]); ?></h1>
                        <p>
						            	<?php echo ($zhici["内容"]); ?>
						            </p>									
								</div>
								<div class="panel-footer">

									<!--<input class="btn btn-primary pull-right" type="button" value="<?php echo GET_F_L('不同意');?>" id="disagree">-->
									&nbsp;&nbsp;&nbsp;&nbsp;
									<input class="btn btn-info pull-right" type="button" value="<?php echo GET_F_L('知道了');?>" id="agree" style="margin-right: 20px;">
								</div>
								
							</div>
							
						</div>
						
					</div>
				
		</div>

	</div>

</div>
<script type="text/javascript">
$(function() {
	var index = layer.open({
		  type: 1,
		  title: '用户协议',
		  area: ['auto', '500px'],
		  shade: 0.5,
		  closeBtn: false,
		  shadeClose: false,
		  scrollbar: false,
		  content: $("#conteoer"),
		});
	$("#disagree").click(function(){
		window.close();
	});
	$("#agree").click(function(){
//		$.post("__GROUP__/Index/change_xieyi",{},function(result){
//
//			var data = eval("("+result+")");
//
//			if(data.status==1){

				layer.close(index);
//			}else{
//				alert(data.message);
//				console.log(data);
//			}
//		});
	});
});
</script><?php endif; ?>

<!-- START THIS PAGE PLUGINS-->
<script type='text/javascript' src='__TMPL__Public/js/plugins/icheck/icheck.min.js'></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/mcustomscrollbar/jquery.mCustomScrollbar.min.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/scrolltotop/scrolltopcontrol.js"></script>

<script type="text/javascript" src="__TMPL__Public/js/plugins/morris/raphael-min.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/morris/morris.min.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/rickshaw/d3.v3.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/rickshaw/rickshaw.min.js"></script>
<script type='text/javascript' src='__TMPL__Public/js/plugins/bootstrap/bootstrap-datepicker.js'></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/owl/owl.carousel.min.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/moment.min.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins/daterangepicker/daterangepicker.js"></script>
<!-- END THIS PAGE PLUGINS-->
<script type="text/javascript" src="__TMPL__Public/js/settings.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/plugins.js"></script>
<script type="text/javascript" src="__TMPL__Public/js/actions.js"></script>
<script>
    var track_sev = <?php echo ($intro_prize); ?>;
    var track_se = <?php echo ($team_prize); ?>;
    //点击图片放大
    $(".img-responsive").click(function () {
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['auto', '80vh'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            scrollbar: false,
            content: "<img src='" + $(this).attr("src") + "'/>"
        });
    });
</script>
<script type="text/javascript" src="__TMPL__Public/js/demo_dashboard.js"></script>
<!-- END TEMPLATE -->
<!-- END SCRIPTS -->
</body>
</html>