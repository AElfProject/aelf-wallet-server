<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 21:02:25
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_search/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:6546771545cee82e14daf56-87227294%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e2b6998a47cd1eaadbe0e7ebb2c52e77ddc576a5' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_search/index.htm',
      1 => 1558428343,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '6546771545cee82e14daf56-87227294',
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
    <script type="text/javascript" src="<?php echo $_smarty_tpl->getVariable('STATIC_PATH')->value;?>
DatePicker/My97DatePicker/WdatePicker.js"></script>
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
                    <td colspan="2">搜索热词设置</td>
                </tr>

                <tbody class="normal_fall">
                <?php if ($_smarty_tpl->getVariable('formData')->value['list']){?>
                <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['k'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('formData')->value['list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['k']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
                <tr class="editTr">
                    <td class="editLtTd">排名</td>
                    <td class="editRtTd">
                        <div class="">
                            <input type="text" name="rank[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['rank'];?>
" placeholder="" class="text" size="10" />
                            &nbsp;&nbsp;游戏id
                            <input type="text" name="gid[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['gid'];?>
" placeholder="" class="text" size="10" />
                            游戏名称
                            <input type="text" name="name[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
" placeholder="" class="text" size="10" />

                            <input type="hidden" name="id[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" placeholder="" class="text" size="10" />
                            <?php if ($_smarty_tpl->tpl_vars['k']->value==0){?>
                            <a class="normal_add">添加</a>
                            <?php }else{ ?>
                            <a class="normal_dell">删除</a>
                            <?php }?>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
                <?php }else{ ?>
                <tr class="editTr">
                    <td class="editLtTd">排名</td>
                    <td class="editRtTd">
                        <div class="">
                            <input type="text" name="rank[]" value="" placeholder="" class="text" size="10" />
                            &nbsp;&nbsp;游戏id
                            <input type="text" name="gid[]" value="" placeholder="" class="text" size="10" />
                            游戏名称
                            <input type="text" name="name[]" value="" placeholder="" class="text" size="10" />
                            <input type="hidden" name="id[]" value=0 placeholder="" class="text" size="10" />
                            <a class="normal_add">添加</a>
                        </div>
                    </td>
                </tr>
                <?php }?>
                </tbody>

            </table>
            <div class="editBtn clearfix">
                <input type="submit" value="Save" class="lnkSave" />
            </div>
        </form>
    </div>
</div>
<script language="JavaScript">
    $(function () {
        $('.normal_add').on('click',function () {
            var html = ' <tr class="editTr">\n' +
                    '                    <td class="editLtTd">排名</td>\n' +
                    '                    <td class="editRtTd">\n' +
                    '                        <div class="">\n' +
                    '                            <input type="text" name="rank[]" value="" placeholder="" class="text" size="10" />\n' +
                    '                            &nbsp;&nbsp;游戏id\n' +
                    '                            <input type="text" name="gid[]" value="" placeholder="" class="text" size="10" />\n' +
                    '游戏名称' +
                    '<input type="text" name="name[]" value="" placeholder="" class="text" size="10" />' +
                    '                            <a class="normal_dell">删除</a>\n' +
                    '                        </div>\n' +
                    '                    </td>\n' +
                    '                </tr>';

                $('.normal_fall').append(html);

        });

        $(document).on('click', '.normal_dell', function () {
            $(this).parents('.editTr').remove();
        })

    });

</script>

</body>
</html>