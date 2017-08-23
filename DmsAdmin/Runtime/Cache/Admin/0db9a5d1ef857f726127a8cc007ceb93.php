<?php if (!defined('THINK_PATH')) exit();?><div class="pageContent">
	<div class="pageFormContent" layoutH="60">         	   
		<?php if(is_array($theme)): foreach($theme as $key=>$vo): ?><div style="float:left;margin-bottom:5px">
			<div style="float:left;width:260px">
			<a href="__URL__/viewThemeTemp/name/<?php echo ($vo["name"]); ?>/imgurl/<?php echo rtrim(strtr(base64_encode('/'.$vo["path"].$vo["name"].'/preview.jpg'),'+/','-'),'=');?>" target="dialog" width="840" height="600" title="点击预览" mask="true">
				<img src="/<?php echo ($vo["path"]); echo ($vo["name"]); ?>/preview.jpg" width="240" height="150"/>
			</a>
			</div>
			<div style="float:left;width:290px"> 
			<ul style="list-style-type:none;padding:20px 20px 5px 5px">
				<li style="padding:2px">状&nbsp;&nbsp;态：<?php if(($vo['status'] == 1)): ?><font color="green">已使用</font><?php else: ?><font color="red">未使用</font><?php endif; ?></li>
				<li style="padding:2px">主题名称：<?php echo ($vo["name"]); ?></li>
				<li style="padding:2px">主题目录：<?php echo ($vo["catalog"]); ?></li>
				<li style="padding:2px">主题描述：<?php echo ($vo["description"]); ?></li>
				<li style="padding:2px">创建时间：<?php echo ($vo["themeTime"]); ?></li>
				<li style="padding:2px">
					<a class="buttonActive" href="__URL__/themeChange/themename/<?php echo ($vo["name"]); ?>" target="ajaxTodo" mask="true"><span>应用</span></a>
				</li>
			</ul>
			</div>
		</div><?php endforeach; endif; ?>              	
	</div>
</div>