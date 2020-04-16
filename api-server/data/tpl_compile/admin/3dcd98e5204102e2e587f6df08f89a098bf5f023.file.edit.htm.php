<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 21:18:51
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_games/edit.htm" */ ?>
<?php /*%%SmartyHeaderCode:4274378045cee86bb2db1e6-07466906%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3dcd98e5204102e2e587f6df08f89a098bf5f023' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_games/edit.htm',
      1 => 1559012173,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4274378045cee86bb2db1e6-07466906',
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
		<form method="post" id="form1" enctype="multipart/form-data">
			<table width="98%" align="center" height="100%" border="0" cellspacing="0" cellpadding="0" class="editTable">
				<tr class="editHdTr">
					<td colspan="2">DAPP 游戏列表管理</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">币种</td>
					<td class="editRtTd">
						<div class="input-box">
							<select name="coin" >
								<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('coins')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
								<option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
"<?php if ($_smarty_tpl->tpl_vars['item']->value['name']==$_smarty_tpl->getVariable('formData')->value['coin']){?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</option>
								<?php }} ?>
							</select>
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">推荐首页</td>
					<td class="editRtTd">
						<div class="input-box">
							<label><input type="checkbox" name="isindex" value="1"<?php if ($_smarty_tpl->getVariable('formData')->value['isindex']==1){?> checked<?php }?> />启用</label>
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">状态</td>
					<td class="editRtTd">
						<div class="input-box">
							<label><input type="checkbox" name="status" value="1"<?php if ($_smarty_tpl->getVariable('formData')->value['status']==1){?> checked<?php }?> />启用</label>
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">分类</td>
					<td class="editRtTd">
						<div class="input-box">
							<select name="cat" >
								<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('cat')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
								<option value="<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
"<?php if ($_smarty_tpl->tpl_vars['key']->value==$_smarty_tpl->getVariable('formData')->value['cat']){?> selected<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value;?>
</option>
								<?php }} ?>
							</select>
						</div>
					</td>
				</tr>

				<tr class="editTr">
					<td class="editLtTd">链接</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="text" name="url" value="<?php echo $_smarty_tpl->getVariable('formData')->value['url'];?>
" placeholder="" class="text" size="100"/>
						</div>
					</td>
				</tr>

				<tr class="editTr">
					<td class="editLtTd">排序</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="text" name="sort" value="<?php echo $_smarty_tpl->getVariable('formData')->value['sort'];?>
" placeholder="" class="text" size="10" /> 越大排名靠前
						</div>
					</td>
				</tr>

				<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('langs')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
?>
				<tbody>
				<tr class="editTr">
					<td class="editLtTd" style="background-color: #eee;padding: 0">语言:</td>
					<td class="editRtTd" style="background-color: #eee;padding: 0">
						&nbsp;<?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>

					</td>
				</tr>
				</tbody>
				<tr class="editTr">
					<td class="editLtTd">名称</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="text" name="name[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" value="<?php echo $_smarty_tpl->getVariable('name')->value[$_smarty_tpl->tpl_vars['item']->value['id']];?>
" placeholder="" class="text" size="50" />
						</div>
					</td>
				</tr>

				<tr class="editTr">
					<td class="editLtTd">标签</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="text" name="tag[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]" value="<?php echo $_smarty_tpl->getVariable('tag')->value[$_smarty_tpl->tpl_vars['item']->value['id']];?>
" placeholder="" class="text" size="50" /> 多个标签用|来分隔
						</div>
					</td>
				</tr>
				<tr class="editTr">
					<td class="editLtTd">简介</td>
					<td class="editor">
						<textarea class="text" style="width:98%; height:120px;" name="desc[<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
]"><?php echo $_smarty_tpl->getVariable('desc')->value[$_smarty_tpl->tpl_vars['item']->value['id']];?>
</textarea>  <?php if ($_smarty_tpl->tpl_vars['item']->value['id']=="en"){?>(注意：控制在90字符)<?php }else{ ?>(注意：控制在40字)<?php }?>
					</td>
				</tr>
				<input type="hidden" name="_lang[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"/>
				<?php }} ?>

				<tr class="editTr">
					<td class="editLtTd">ico 图标</td>
					<td class="editRtTd">
						<div class="input-box">
							<input type="file" name="ico" />
							<?php if ($_smarty_tpl->getVariable('formData')->value['ico']){?>
							<br />
							<img src="<?php echo $_smarty_tpl->getVariable('configs')->value['OSS_URL'];?>
<?php echo $_smarty_tpl->getVariable('formData')->value['ico'];?>
" style="max-height:100px;" />
							<br />
							<label><input type="checkbox" name="logoDel" value="1" />删除图片</label>
							<?php }?>
						</div>
					</td>
				</tr>
				<?php if ($_smarty_tpl->getVariable('formData')->value['addtime']){?>
				<tr class="editTr">
					<td class="editLtTd">添加时间</td>
					<td class="editRtTd">
						<div class="input-box">
							<?php echo date('Y-m-d H:i:s',$_smarty_tpl->getVariable('formData')->value['addtime']);?>

						</div>
					</td>
				</tr>
				<?php }?>
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