<?php if (!defined('THINK_PATH')) exit(); if(!empty($errs)): if(is_array($errs)): foreach($errs as $errkey=>$err): if(strstr($errkey,'product')!='') $errkey="productCountMoney"; ?>
	$('#state_<?php echo ($errkey); ?>',navTab.getCurrentPanel()).html('<?php echo ($err); ?>');<?php endforeach; endif; endif; ?>
<?php if(!empty($RegionSet)): $Regionname=$RegionSet[0]; $Regiondata=$RegionSet[1]; ?>
$('#<?php echo ($Regionname); ?>',navTab.getCurrentPanel()).html("<?php if(is_array($RegionSet[1])): foreach($RegionSet[1] as $key=>$Region): ?><option value='<?php echo ($Region["name"]); ?>' <?php if($posselect==$Region['name']) echo 'selected'; ?>><?php echo ($Region["name"]); ?></option><?php endforeach; endif; ?>");<?php endif; ?>