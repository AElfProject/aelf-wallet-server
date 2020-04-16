<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 21:02:23
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_banner/index.htm" */ ?>
<?php /*%%SmartyHeaderCode:9507319165cee82dfde5490-68281739%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '42e196574b6b9801f600743760a0f62b415f7f96' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/onchain/dapps_banner/index.htm',
      1 => 1558428343,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9507319165cee82dfde5490-68281739',
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
    <script>
        function countSelect ()
        {
            var cnt		= 0;
            var list	= document.getElementsByName('ids[]');
            for (var i = 0; i < list.length; i++)
            {
                if (list[i].checked) cnt++;
            }
            return cnt;
        }
        function DeleteSome ()  //批量删除
        {
            if (countSelect() <= 0)
            {
                alert('<?php echo $_smarty_tpl->getVariable('lang')->value->please_select_batch_records;?>
');
                return false;
            }
            if (window.confirm('<?php echo $_smarty_tpl->getVariable('lang')->value->are_you_sure_delete_selected_records;?>
')) $('#listForm').attr('action', '<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=delete').submit();
        }

        $(function(){
            $('#checkAll').click(function(){
                $('input.listChk').attr('checked', $(this).attr('checked'));
            });
        });
    </script>
</head>

<body>
<div class="wrap inner clearfix">
    <div class="container">
        <div class="tips">
            <a href="<?php echo $_smarty_tpl->getVariable('refreshUrl')->value;?>
" class="lnkRefresh">刷新</a>
            <a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=edit" class="lnkAdd">添加</a>
            <a onclick="DeleteSome();" class="lnkDeleteSome">删除</a>
        </div>

        <?php $_template = new Smarty_Internal_Template('form-result.htm', $_smarty_tpl->smarty, $_smarty_tpl, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null);
 echo $_template->getRenderedTemplate();?><?php $_template->updateParentVariables(0);?><?php unset($_template);?>

        <form id="listForm" name="listForm" action="" method="post">
            <table class="listTable">
                <tr class="listHdTr">
                    <td width="40"><input type="checkbox" id="checkAll" /></td>
                    <td>标题</td>
                    <td>状态</td>
                    <td width="6%">编辑</td>
                    <td width="6%">删除</td>
                </tr>
                <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('list')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
                <tr<?php if ($_smarty_tpl->tpl_vars['key']->value%2==0){?> class="Alternating"<?php }?>>
                <td><input type="checkbox" name="ids[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" class="listChk" /></td>
                <td><?php echo $_smarty_tpl->tpl_vars['item']->value['title'];?>
</td>
                <td><?php if ($_smarty_tpl->tpl_vars['item']->value['status']==1){?><span style="color:green;">启用</span><?php }else{ ?><span style="color:red;">禁用</span><?php }?></td>
                <td><a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=edit&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
">编辑</a></td>
                <td><a href="<?php echo $_smarty_tpl->getVariable('doUrl')->value;?>
act=delete&id=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" class="lnkDelete" onclick="return chkDelete();" title="删除">删除</a></td>
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