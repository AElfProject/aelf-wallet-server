<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 21:09:24
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/system/site/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:13321495275cee84841ff942-63564203%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '02f96e1165a83cb57504b8a6c05750763659072c' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/system/site/index.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '13321495275cee84841ff942-63564203',
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
<?php if ($_smarty_tpl->getVariable('data')->value['isContactEnabled']||$_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?>
<script src="<?php echo $_smarty_tpl->getVariable('STATIC_PATH')->value;?>
/editor/ckeditor/ckeditor.js"></script>
<?php }?>
<?php if ($_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']||$_smarty_tpl->getVariable('data')->value['isContactEnabled']){?>
<script>
$(function(){
	<?php if ($_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?>
	CKEDITOR.replace('copyright', {
		height	: 150,
		filebrowserImageUploadUrl : '?con=admin&ctl=editor&act=pic'
	});
	<?php }?>
	<?php if ($_smarty_tpl->getVariable('data')->value['isContactEnabled']){?>
	CKEDITOR.replace('contact', {
		height	: 150,
		filebrowserImageUploadUrl : '?con=admin&ctl=editor&act=pic'
	});
	<?php }?>
});
</script>
<?php }?>
<script type="text/javascript">
function check (form)
{
	var name = document.getElementsByName('data[name]')[0];
	if (name.value == '')
	{
		alert('<?php echo $_smarty_tpl->getVariable('lang')->value->site_name;?>
<?php echo $_smarty_tpl->getVariable('lang')->value->can_not_empty;?>
');
		name.focus();
		return false;
	}

	var name = document.getElementsByName('data[title]')[0];
	if (name.value == '')
	{
		alert('<?php echo $_smarty_tpl->getVariable('lang')->value->page_title;?>
<?php echo $_smarty_tpl->getVariable('lang')->value->can_not_empty;?>
');
		name.focus();
		return false;
	}

	<?php if ($_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?>
	$('#copyright').val(CKEDITOR.instances.copyright.getData(););
	<?php }?>
	<?php if ($_smarty_tpl->getVariable('data')->value['isContactEnabled']){?>
	$('#contact').val(CKEDITOR.instances.contact.getData(););
	<?php }?>

	return true;
}
</script>
</head>

<body>
<div class="wrap inner clearfix">
	<div class="container">
		<div class="tips"></div>
		<table class="tabTable">
			<tr>
				<?php if ($_smarty_tpl->getVariable('hideColumn')->value['site_index']){?><td><a class="current" href="#"><?php echo $_smarty_tpl->getVariable('lang')->value->basic_setting;?>
</a></td><?php }?>
				<?php if ($_smarty_tpl->getVariable('hideColumn')->value['site_other']){?><td><a href="?con=admin&ctl=system/site&act=other"><?php echo $_smarty_tpl->getVariable('lang')->value->advanced_setting;?>
</a></td><?php }?>
			</tr>
		</table>
		<form action="?con=admin&ctl=system/site&cl=<?php echo $_smarty_tpl->getVariable('cl')->value;?>
" method="post" onsubmit="return check(this);">
			<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
				<tr class="editHdTr">
					<td colspan="2"><?php echo $_smarty_tpl->getVariable('lang')->value->basic_setting;?>
</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->select_lang;?>
</td>
					<td class="editRtTd">
						<select onchange="window.location.href = '?con=admin&ctl=system/site&cl=' + this.value;">
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
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->site_name;?>
</td>
					<td class="editRtTd"><input name="data[name]" value="<?php echo $_smarty_tpl->getVariable('data')->value['name'];?>
" type="text" size="60" class="text" /></td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->page_title;?>
</td>
					<td class="editRtTd"><input name="data[pageTitle]" value="<?php echo $_smarty_tpl->getVariable('data')->value['pageTitle'];?>
" type="text" size="60" class="text" /></td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->page_keywords;?>
</td>
					<td class="editRtTd"><input name="data[keywords]" value="<?php echo $_smarty_tpl->getVariable('data')->value['keywords'];?>
" type="text" size="60" class="text" /></td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->page_description;?>
</td>
					<td class="editRtTd"><input name="data[description]" value="<?php echo $_smarty_tpl->getVariable('data')->value['description'];?>
" type="text" size="60" class="text" /></td>
				</tr>
				<?php if ($_smarty_tpl->getVariable('data')->value['isCopyrightEnabled']){?>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->copyright_info;?>
</td>
					<td class="editor">
						<textarea id="copyright" class="text" style="width:98%; height:120px;" name="data[copyright]"><?php echo $_smarty_tpl->getVariable('data')->value['copyright'];?>
</textarea>
					</td>
				</tr>
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('data')->value['isContactEnabled']){?>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->contact_info;?>
</td>
					<td class="editor">
						<textarea id="contact" class="text" style="width:98%; height:120px;" name="data[contact]"><?php echo $_smarty_tpl->getVariable('data')->value['contact'];?>
</textarea>
					</td>
				</tr>
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('data')->value['isHeadJavascriptEnabled']){?>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->head_javascript;?>
</td>
					<td class="editor">
						<textarea class="text" style="width:98%; height:120px;" name="data[headJavaScript]"><?php echo $_smarty_tpl->getVariable('data')->value['headJavaScript'];?>
</textarea>
					</td>
				</tr>
				<?php }?>
				<?php if ($_smarty_tpl->getVariable('data')->value['isFootJavascriptEnabled']){?>
				<tr class="editTr">
					<td class="editLtTd"><?php echo $_smarty_tpl->getVariable('lang')->value->foot_javascript;?>
</td>
					<td class="editor">
						<textarea class="text" style="width:98%; height:120px;" name="data[footJavaScript]"><?php echo $_smarty_tpl->getVariable('data')->value['footJavaScript'];?>
</textarea>
					</td>
				</tr>
				<?php }?>
			</table>
			<div class="editBtn clearfix">
				<input type="submit" value="Save" class="lnkSave" /> 
			</div>
		</form>
	</div>
</div>
</body>
</html>