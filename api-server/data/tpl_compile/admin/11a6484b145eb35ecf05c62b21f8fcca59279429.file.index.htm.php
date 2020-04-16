<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:50:30
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/hidden/cls/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:15595220995cee80166ee9c5-87970515%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '11a6484b145eb35ecf05c62b21f8fcca59279429' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/hidden/cls/index.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '15595220995cee80166ee9c5-87970515',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/global.css">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/main.css">
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/global.js"></script>
</head>

<body>
<div class="wrap inner clearfix">
	<div class="container">
		<div class="tips">
			<a href="?con=admin&ctl=hidden/cls&act=doCls&cacheName=all" class="lnkDelete"><?php echo $_smarty_tpl->getVariable('lang')->value->all;?>
</a>
		</div>
		<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
			<tr class="editHdTr">
				<td colspan="2"><?php echo $_smarty_tpl->getVariable('lang')->value->update_cache;?>
</td>
			</tr>
			<tr class="editTr">
				<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->action_cache;?>
:</td>
				<td class="editRtTd"><a href="?con=admin&ctl=hidden/cls&act=doCls&cacheName=action"><?php echo $_smarty_tpl->getVariable('lang')->value->update;?>
</a></td>
			</tr>
		</table>
		<div class="editBtn clearfix">
			<a href="?con=admin&ctl=hidden/cls&act=doCls&cacheName=all" class="lnkDelete"><?php echo $_smarty_tpl->getVariable('lang')->value->all;?>
</a>
		</div>
	</div>
</div>
</body>
</html>