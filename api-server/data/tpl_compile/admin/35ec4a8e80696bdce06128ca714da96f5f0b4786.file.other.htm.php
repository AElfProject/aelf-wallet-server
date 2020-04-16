<?php /* Smarty version Smarty-3.0.6, created on 2019-05-30 15:21:54
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/system/site/other.htm" */ ?>
<?php /*%%SmartyHeaderCode:19868045815cef8492e1b8d4-28727012%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '35ec4a8e80696bdce06128ca714da96f5f0b4786' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/system/site/other.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19868045815cef8492e1b8d4-28727012',
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
js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/global.js"></script>
</head>

<body>
<div class="wrap inner clearfix">
	<div class="container">
		<div class="tips"></div>
		<table class="tabTable">
			<tr>
				<?php if ($_smarty_tpl->getVariable('hideColumn')->value['site_index']){?><td><a href="?con=admin&ctl=system/site"><?php echo $_smarty_tpl->getVariable('lang')->value->basic_setting;?>
</a></td><?php }?>
				<?php if ($_smarty_tpl->getVariable('hideColumn')->value['site_other']){?><td><a class="current" href="#"><?php echo $_smarty_tpl->getVariable('lang')->value->advanced_setting;?>
</a></td><?php }?>
			</tr>
		</table>
		<form action="?con=admin&ctl=system/site&act=other&cl=<?php echo $_smarty_tpl->getVariable('cl')->value;?>
" method="post" onsubmit="return check(this);">
			<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
				<tr class="editHdTr">
					<td colspan="2"><?php echo $_smarty_tpl->getVariable('lang')->value->advanced_setting;?>
</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->select_lang;?>
</td>
					<td class="editRtTd">
						<select onchange="window.location.href = '?con=admin&ctl=system/site&act=other&cl=' + this.value;">
							<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('langs')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
								<option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"<?php if ($_smarty_tpl->getVariable('cl')->value==$_smarty_tpl->tpl_vars['item']->value['id']){?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</option>
							<?php }} ?>
						</select>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->copyright_info;?>
</td>
					<td class="editRtTd">
						<input id="isCopyrightEnabled1" name="data[isCopyrightEnabled]" value="1" type="radio"<?php if ($_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?> checked<?php }?> /><label for="isCopyrightEnabled1"><?php echo $_smarty_tpl->getVariable('lang')->value->show;?>
</label>
						<input id="isCopyrightEnabled2" name="data[isCopyrightEnabled]" value="0" type="radio"<?php if (!$_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?> checked<?php }?> /><label for="isCopyrightEnabled2"><?php echo $_smarty_tpl->getVariable('lang')->value->hide;?>
</label>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->contact_info;?>
</td>
					<td class="editRtTd">
						<input id="isContactEnabled1" name="data[isContactEnabled]" value="1" type="radio"<?php if ($_smarty_tpl->getVariable('data')->value['isContactEnabled']){?> checked<?php }?> /><label for="isContactEnabled1"><?php echo $_smarty_tpl->getVariable('lang')->value->show;?>
</label>
						<input id="isContactEnabled2" name="data[isContactEnabled]" value="0" type="radio"<?php if (!$_smarty_tpl->getVariable('data')->value['isContactEnabled']){?> checked<?php }?> /><label for="isContactEnabled2"><?php echo $_smarty_tpl->getVariable('lang')->value->hide;?>
</label>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->head_javascript;?>
</td>
					<td class="editRtTd">
						<input id="isHeadJavascriptEnabled1" name="data[isHeadJavascriptEnabled]" value="1" type="radio"<?php if ($_smarty_tpl->getVariable('data')->value['isHeadJavascriptEnabled']){?> checked<?php }?> /><label for="isHeadJavascriptEnabled1"><?php echo $_smarty_tpl->getVariable('lang')->value->show;?>
</label>
						<input id="isHeadJavascriptEnabled2" name="data[isHeadJavascriptEnabled]" value="0" type="radio"<?php if (!$_smarty_tpl->getVariable('data')->value['isHeadJavascriptEnabled']){?> checked<?php }?> /><label for="isHeadJavascriptEnabled2"><?php echo $_smarty_tpl->getVariable('lang')->value->hide;?>
</label>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->foot_javascript;?>
</td>
					<td class="editRtTd">
						<input id="isFootJavascriptEnabled1" name="data[isFootJavascriptEnabled]" value="1" type="radio"<?php if ($_smarty_tpl->getVariable('data')->value['isFootJavascriptEnabled']){?> checked<?php }?> /><label for="isFootJavascriptEnabled1"><?php echo $_smarty_tpl->getVariable('lang')->value->show;?>
</label>
						<input id="isFootJavascriptEnabled2" name="data[isFootJavascriptEnabled]" value="0" type="radio"<?php if (!$_smarty_tpl->getVariable('data')->value['isFootJavascriptEnabled']){?> checked<?php }?> /><label for="isFootJavascriptEnabled2"><?php echo $_smarty_tpl->getVariable('lang')->value->hide;?>
</label>
					</td>
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