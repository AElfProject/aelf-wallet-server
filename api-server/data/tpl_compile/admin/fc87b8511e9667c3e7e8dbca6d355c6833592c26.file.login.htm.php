<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:46:34
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/login.htm" */ ?>
<?php /*%%SmartyHeaderCode:11855127465cee7f2a41d192-90264359%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fc87b8511e9667c3e7e8dbca6d355c6833592c26' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/login.htm',
      1 => 1556430731,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11855127465cee7f2a41d192-90264359',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $_smarty_tpl->getVariable('lang')->value->login_management;?>
 <?php echo $_smarty_tpl->getVariable('CMS_VERS')->value;?>
</title>
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/global.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/login.css" />
<script type="text/javascript"src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
js/jquery.js" /></script>
<script>
function check (form)
{
	if (form.name.value == '')
	{
		form.name.focus();
		return false;
	}
	if (form.pass.value == '')
	{
		form.pass.focus();
		return false;
	}
//	if (form.verifyCode.value == '')
//	{
//		form.verifyCode.focus();
//		return false;
//	}

	return true;
}

window.onload = function () { document.form_job.name.focus(); }
</script>
</head>
<body>
<div class="mask"></div>
<div class="middle">
	<div class="logo"><a href=""><img src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/logo.jpg" alt="" /></a></div>
	<div class="login clearfix">
		<h2>Hello</h2>
		<?php if ($_smarty_tpl->getVariable('langs_count')->value>1){?>
		<div class="lang-set clearfix">
			<!--span><?php echo $_smarty_tpl->getVariable('lang')->value->select_lang;?>
: </span-->
			<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->getVariable('langs')->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
if ($_smarty_tpl->_count($_from) > 0){
    foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
			<a<?php if ($_smarty_tpl->getVariable('admin_lang')->value==$_smarty_tpl->tpl_vars['item']->value['id']){?> class="current" href="javascript:;" style="cursor:default;"<?php }else{ ?> href="<?php echo $_smarty_tpl->getVariable('http_root_www')->value;?>
?con=admin&ctl=default&admin_lang=<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
" target="_parent"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</a>
			<?php }} ?>
		</div>
		<?php }?>
		<div class="clear"></div>
		<!--div class="version"><em><?php echo $_smarty_tpl->getVariable('CMS_VERS')->value;?>
</em></div-->
		<div class="login-panel">
			<form action="?con=admin&ctl=common/login&act=login&k=<?php echo $_smarty_tpl->getVariable('k')->value;?>
" name="form_job" method="post" onsubmit="return check(this);">
				<input type="hidden" name="formKey" value="<?php echo $_smarty_tpl->getVariable('formKey')->value;?>
" />
				<ul>
					<li class="rows clearfix">
						<span><label><?php echo $_smarty_tpl->getVariable('lang')->value->user_name;?>
</label></span>
						<div class="input"><input name="name" type="text" size="30" maxlength="50" class="text" placeholder="<?php echo $_smarty_tpl->getVariable('lang')->value->user_name;?>
" /></div>
					</li>
					<li class="rows clearfix">
						<span><label><?php echo $_smarty_tpl->getVariable('lang')->value->password;?>
</label></span>
						<div class="input"><input name="pass" type="password" size="30" maxlength="50" class="text" placeholder="<?php echo $_smarty_tpl->getVariable('lang')->value->password;?>
" /></div>
					</li>
					<li class="rows clearfix">
						<span><label>Google Authenticator</label></span>
						<div class="input"><input name="code" type="text" size="9" maxlength="9" class="text" placeholder="请输入6位动态码" /></div>
					</li>
					<li class="rows rowsYzm clearfix">
						<span><label><?php echo $_smarty_tpl->getVariable('lang')->value->verify_code;?>
</label></span>
						<div class="input">
							<input name="verifyCode" type="text" size="10" maxlength="20" class="text" placeholder="<?php echo $_smarty_tpl->getVariable('lang')->value->verify_code;?>
" />
							<img src="<?php echo $_smarty_tpl->getVariable('http_root_www')->value;?>
verifycode.gif?w=80&h=30" width="120" height="50" alt="Not see, tap" style="vertical-align:middle; cursor:pointer;" onclick="this.src = '<?php echo $_smarty_tpl->getVariable('http_root_www')->value;?>
verifycode.gif?w=80&h=30&rnd=' + Math.random();" />
						</div>
					</li>
					<li class="rows rowsYzm clearfix">
						<span><label><?php echo $_smarty_tpl->getVariable('lang')->value->verify_code;?>
</label></span>
						<div class="input">
						</div>
					</li>
					<li class="rowsSubmit clearfix">
						<div class="input clearfix"><input type="submit" value="Login" class="btn-submit" /></div>
					</li>
				</ul>
			</form>
		</div>
	</div>
	<div class="footer">
		<div class="bg1"></div>
		<div class="bg2"></div>
		<div class="pic"></div>
		<div class="copyright">Copyright &copy; legendwebdesign. All Rights Reserved.</div>
	</div>
</div>
<script>
	$(function(){
		function topScroll(){
			var vieHeight = parseInt($(window).height()-86);
			var loginHeight = parseInt($('.login').height());
			$('.login').stop(true,true).animate({"top":((vieHeight-loginHeight)/2)+'px'});
		}
		topScroll();
		$(window).resize(function(){
			topScroll();
		});
	});
</script>
</body>
</html>