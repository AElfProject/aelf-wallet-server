<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:53:33
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/info/edit.htm" */ ?>
<?php /*%%SmartyHeaderCode:17857954405cee80cd9eddf4-46678125%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'be8f8a4f07894f3a46ce1bb8a3fc5127749aa09a' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/info/edit.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17857954405cee80cd9eddf4-46678125',
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
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('STATIC_PATH')->value;?>
font-awesome-4.3.0/css/font-awesome.min.css" />
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('STATIC_PATH')->value;?>
jquery/jquery-1.8.3.min.js"></script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/global.js"></script>
	<?php if ($_smarty_tpl->getVariable('formData')->value['classId']==102){?>
	<script src="<?php echo $_smarty_tpl->getVariable('STATIC_PATH')->value;?>
/editor/ckeditor/ckeditor.js"></script>
	<script>
        $(function(){
            CKEDITOR.replace('content', {
                height : 350,
                filebrowserImageUploadUrl : '?con=admin&ctl=editor&act=pic'
            });
        });
	</script>
	<?php }?>
</head>

<body>
<div class="wrap inner clearfix">
	<div class="container">
		<div class="tips">
			<a href="<?php echo $_smarty_tpl->getVariable('returnUrl')->value;?>
" class="lnkReturn">返回列表</a>
		</div>

		<?php $_template = new Smarty_Internal_Template('form-result.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>

		<form method="post" id="form1">
			<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
				<tr class="editHdTr">
					<td colspan="2">内容管理</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">状态</td>
					<td class="editRtTd">
						<div class="input-box">
							<label><input type="checkbox" name="isApproved" value="1"<?php if ($_smarty_tpl->getVariable('formData')->value['isApproved']){?> checked<?php }?> />启用</label>
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">语言</td>
					<td class="editRtTd">
						<div class="input-box">
							<select name="lang">
								<option value="">请选择语言</option>
								<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('langs')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
									<option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"<?php if ($_smarty_tpl->tpl_vars['item']->value['id']==$_smarty_tpl->getVariable('formData')->value['lang']){?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</option>
								<?php }} ?>
							</select>
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">标题</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="text" name="title" value="<?php echo $_smarty_tpl->getVariable('formData')->value['title'];?>
" placeholder="" class="text" size="100" />
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">内容</td>
					<td class="editRtTd">
						<div class="input-box">
							<textarea name="content" class="text" style="width:80%; height:80px;"><?php echo $_smarty_tpl->getVariable('formData')->value['content'];?>
</textarea>
						</div>
					</td>
				</tr>
			</table>
			<div class="editBtn clearfix">
				<input type="submit" value="Save" class="lnkSave" />
				<a href="<?php echo $_smarty_tpl->getVariable('returnUrl')->value;?>
" class="lnkReturn">返回列表</a>
			</div>
		</form>
	</div>
</div>
</body>
</html>