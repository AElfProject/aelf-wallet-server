<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:51:30
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/warning.htm" */ ?>
<?php /*%%SmartyHeaderCode:12396294025cee8052e12865-08859112%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '10d24e8d7440e07a786a3853a9051b36c72f38cf' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/warning.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '12396294025cee8052e12865-08859112',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $_smarty_tpl->getVariable('lang')->value->system_prompts;?>
</title>
<style type="text/css">
body { height:30px; line-height:30px; margin:0; padding:0; background-color:#fff; color:#000; font-size:12px; font-family:Arial; }
a { color:#174A7D; }
#container { clear:both; width:500px; margin:100px auto; padding:10px 20px; background:#ffa; border:1px solid #cc9; -moz-box-shadow: 2px 2px 11px #666; -webkit-box-shadow: 2px 2px 11px #666; }
#title { font-weight:bold; }
#content { padding:0 20px; height:60px; }
#url { font-weight:bold; text-align:center; }
</style>
</head>
<body>
<div id="container">
	<div id="title"><?php echo $_smarty_tpl->getVariable('lang')->value->system_prompts;?>
</div>
	<div id="content"><?php echo $_smarty_tpl->getVariable('msg')->value;?>
</div>
	<div id="url"><a href="<?php echo $_smarty_tpl->getVariable('url')->value;?>
"><?php echo $_smarty_tpl->getVariable('lang')->value->click_here_continue;?>
</a></div>
</div>
</body>
</html>