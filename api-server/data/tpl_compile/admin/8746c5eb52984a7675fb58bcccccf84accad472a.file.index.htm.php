<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 21:09:23
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/setting/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:14122376175cee8483924809-37307779%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8746c5eb52984a7675fb58bcccccf84accad472a' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/setting/index.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14122376175cee8483924809-37307779',
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
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/global.js"></script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/list.js"></script>
</head>

<body>
<div class="wrap inner clearfix">
	<div class="container">
		<div class="tips">
			<a href="<?php echo $_smarty_tpl->getVariable('refreshUrl')->value;?>
" class="lnkRefresh">刷新</a>
			<a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=edit" class="lnkAdd">添加</a>
		</div>
		<div class="search">
			<form method="get">
				<input type="hidden" name="con" value="admin" />
				<input type="hidden" name="ctl" value="adv/setting" />
				<input type="text" class="text" name="s" style="width:300px; margin-left:0;" placeholder="输入key搜索" value="<?php echo $_smarty_tpl->getVariable('search')->value['s'];?>
" />
				<button type="submit" class="custom-button">搜索</button>
			</form>
		</div>

		<?php $_template = new Smarty_Internal_Template('form-result.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>

		<form id="listForm" name="listForm" action="" method="post">
			<table class="listTable">
				<tr class="listHdTr">
					<td>KEY</td>
					<td>值</td>
					<td>说明</td>
					<td width="6%">编辑</td>
				</tr>
				<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('list')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
					<tr<?php if ($_smarty_tpl->tpl_vars['key']->value%2==0){?> class="Alternating"<?php }?>>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['key'];?>
</td>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['val'];?>
</td>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['tip'];?>
</td>
						<td>
							<?php if ($_smarty_tpl->tpl_vars['item']->value['type']!='system'){?><a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=edit&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
">编辑</a><?php }?>
						</td>
					</tr>
				<?php }} ?>
				<tr class="listFtTr">
					<td colspan="15" align="right"><?php echo $_smarty_tpl->getVariable('pager')->value;?>
</td>
				</tr>
			</table>
		</form>
	</div>
</div>

</body>
</html>