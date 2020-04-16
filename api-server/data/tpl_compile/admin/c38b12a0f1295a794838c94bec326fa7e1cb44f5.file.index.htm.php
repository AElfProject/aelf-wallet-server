<?php /* Smarty version Smarty-3.0.6, created on 2019-05-30 15:45:34
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/database/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:4572976825cef8a1e241e19-80388498%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c38b12a0f1295a794838c94bec326fa7e1cb44f5' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/adv/database/index.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4572976825cef8a1e241e19-80388498',
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
				<input type="hidden" name="ctl" value="adv/database" />
				<input type="text" class="text" name="s" style="width:300px; margin-left:0;" placeholder="输入别名搜索" value="<?php echo $_smarty_tpl->getVariable('search')->value['s'];?>
" />
				<button type="submit" class="custom-button">搜索</button>
			</form>
		</div>

		<?php $_template = new Smarty_Internal_Template('form-result.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>

		<form id="listForm" name="listForm" action="" method="post">
			<table class="listTable">
				<tr class="listHdTr">
					<td>别名</td>
					<td>是否分配新会员</td>
					<td>会员数量</td>
					<td>转出数量</td>
					<td>转入数量</td>
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
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['alias'];?>
</td>
						<td><?php if ($_smarty_tpl->tpl_vars['item']->value['status']){?><span style="color:green;">分配</span><?php }else{ ?><span style="color:red;">不分配</span><?php }?></td>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['memberCount'];?>
</td>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['sendCount'];?>
</td>
						<td><?php echo $_smarty_tpl->tpl_vars['item']->value['receiveCount'];?>
</td>
						<td><a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=edit&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
">编辑</a></td>
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