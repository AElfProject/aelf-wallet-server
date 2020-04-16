<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:53:30
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/form-result.htm" */ ?>
<?php /*%%SmartyHeaderCode:5948667005cee80ca6b6273-34705545%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f9a1780269e226696dc9aea79c0541659f1a74d1' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/form-result.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '5948667005cee80ca6b6273-34705545',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_smarty_tpl->getVariable('formError')->value){?>
	<div class="form-error">
		<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('formError')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
			<li><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</li>
		<?php }} ?>
	</div>
<?php }?>
<?php if ($_smarty_tpl->getVariable('formReturn')->value){?>
	<div class="form-return<?php if ($_smarty_tpl->getVariable('formReturn')->value['success']){?> form-return-success<?php }else{ ?> form-return-faild<?php }?>">
		<p><?php echo $_smarty_tpl->getVariable('formReturn')->value['msg'];?>
</p>
	</div>
<?php }?>