<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:38:21
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/menu.htm" */ ?>
<?php /*%%SmartyHeaderCode:1330332355cee7d3da1ef16-43416528%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0106771740f33922602273715302d61a25e1caf1' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/menu.htm',
      1 => 1556430731,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1330332355cee7d3da1ef16-43416528',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/global.css">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/menu.css">
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/menu.js"></script>
</head>

<body>
<div id="menu-area">
	<div id="menu">
		<dl>
			<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('data')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
				<?php if (!$_smarty_tpl->tpl_vars['item']->value['hide']){?>
					<dt class="<?php if ($_smarty_tpl->tpl_vars['key']->value==0){?>first<?php }?>"><?php if ($_smarty_tpl->tpl_vars['item']->value['url']){?><a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
" target="<?php if ($_smarty_tpl->tpl_vars['item']->value['target']==''){?>main<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['item']->value['target'];?>
<?php }?>"><?php }else{ ?><a href="#"><?php }?><span><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
<?php if ($_smarty_tpl->tpl_vars['item']->value['name']=="交易"){?><span style="color:red;" id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"> (<?php echo $_smarty_tpl->tpl_vars['item']->value['num'];?>
)</span><?php }?></span></a></dt>
					<?php if ($_smarty_tpl->tpl_vars['item']->value['childCount']>0){?>
						<dd style="display:none;">
							<ul class="third-menu">
								<?php  $_smarty_tpl->tpl_vars['sub'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['subkey'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['item']->value['child']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['sub']->key => $_smarty_tpl->tpl_vars['sub']->value){
 $_smarty_tpl->tpl_vars['subkey']->value = $_smarty_tpl->tpl_vars['sub']->key;
?>
									<?php if (!$_smarty_tpl->tpl_vars['sub']->value['hide']){?>
										<li class="clearfix"><a href="<?php echo $_smarty_tpl->tpl_vars['sub']->value['url'];?>
" target="<?php if ($_smarty_tpl->tpl_vars['sub']->value['target']==''){?>main<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['sub']->value['target'];?>
<?php }?>"><?php echo $_smarty_tpl->tpl_vars['sub']->value['name'];?>
<?php if ($_smarty_tpl->tpl_vars['sub']->value['name']=="转出审核"||$_smarty_tpl->tpl_vars['sub']->value['name']=="转入审核"||$_smarty_tpl->tpl_vars['sub']->value['name']=="转移审核"){?><span style="color:red;" id="<?php echo $_smarty_tpl->tpl_vars['sub']->value['id'];?>
"> (<?php echo $_smarty_tpl->tpl_vars['sub']->value['num'];?>
)</span><?php }?></a></li>
									<?php }?>
								<?php }} ?>
							</ul>
						</dd>
					<?php }?>
				<?php }?>
			<?php }} ?>
		</dl>
	</div>
</div>
</body>
<script>
    function reqs() {
        $.ajax({
            type: 'get',
            url: '<?php echo $_smarty_tpl->getVariable('http_root_www')->value;?>
index.php?con=admin&ctl=common/menu&act=ajax_data',
            dataType: 'json',
            success: function(res) {
                try {
                    $("#110").html('（'+res.all+'）');
                    $("#110103").html('（'+res.out+'）');
                    $("#110106").html('（'+res.order+'）');
                    $("#110111").html('（'+res.receipt+'）');
                }catch(err){

                }
                //console.log(res.out);
            },
            error: function() {
                console.log('请求失败');
            }
        });
    }
    //reqs();
    setInterval(reqs, 10000);
</script>


</html>