<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:50:38
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/hidden/gml/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:11005273065cee801ea3ab21-89546112%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '61956a636d9be899d5df151c702d38554fd6874d' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/hidden/gml/index.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11005273065cee801ea3ab21-89546112',
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
		<div class="tips"></div>
		<form method="post">
			<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
				<tr class="editHdTr">
					<td colspan="2"><?php echo $_smarty_tpl->getVariable('lang')->value->generate_multiple_lang;?>
</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->source_lang;?>
<?php echo $_smarty_tpl->getVariable('lang')->value->maohao;?>
</td>
					<td class="editRtTd"><input name="sourceLang" type="text" size="20" class="text" /></td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->target_lang;?>
<?php echo $_smarty_tpl->getVariable('lang')->value->maohao;?>
</td>
					<td class="editRtTd"><input name="targetLang" type="text" size="20" class="text" /></td>
				</tr>
			</table>
			<div class="editBtn clearfix">
				<input type="submit" value="Save" class="lnkSave" /> 
			</div>
		</form>
	</div>
</div>
</body>
</html>