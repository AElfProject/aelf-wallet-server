<?php /* Smarty version Smarty-3.0.6, created on 2019-05-29 20:38:21
         compiled from "/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/main.htm" */ ?>
<?php /*%%SmartyHeaderCode:11129175335cee7d3dcc8435-76106021%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '28b40c3504d152fe350a58901ad7e47750eace44' => 
    array (
      0 => '/Users/aelf/workspace/php/aelf.admin/core/common/skin/admin/common/main.htm',
      1 => 1539660204,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11129175335cee7d3dcc8435-76106021',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
)); /*/%%SmartyHeaderCode%%*/?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/global.css">
<link rel="stylesheet" type="text/css" href="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/main.css">
</head>

<body>
<div class="wrap clearfix">
	<div class="container">
		<div>
			<ul class="msg clearfix">
				<li class="adminFace"><img src="<?php echo $_smarty_tpl->getVariable('SKIN_PATH')->value;?>
images/admin_face.png" width="84" height="84" alt="admin face" /></li>
				<li class="name"><em><?php echo $_smarty_tpl->getVariable('user')->value['displayName'];?>
</em>&nbsp;<?php echo $_smarty_tpl->getVariable('lang')->value->welcome_management;?>
</li>
				<li class="time"><?php echo $_smarty_tpl->getVariable('time')->value;?>
</li>
				<li></li>
			</ul>
		</div>
		<div class="grid-m0s330 layout">
			<div class="col-main">
				<div class="main-wrap">
					<div class="statistics box-skin-blue">
						<div class="hd">
							<h4><?php echo $_smarty_tpl->getVariable('lang')->value->statistics;?>
</h4>
						</div>
						<div class="bd clearfix">
							<div class="column">
								
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sub">
				<div class="information box-skin-blue">
					<div class="hd">
						<h4><?php echo $_smarty_tpl->getVariable('lang')->value->your_nformation;?>
</h4>
					</div>
					<div class="bd">
						<ul class="info-list">
							<li class="clearfix"><span><?php echo $_smarty_tpl->getVariable('lang')->value->user_name;?>
:</span>admin<?php echo $_smarty_tpl->getVariable('user')->value['name'];?>
</li>
							<li class="clearfix"><span><?php echo $_smarty_tpl->getVariable('lang')->value->ip;?>
:</span><?php echo $_smarty_tpl->getVariable('user')->value['lastLoginIP'];?>
</li>
							<li class="clearfix"><span><?php echo $_smarty_tpl->getVariable('lang')->value->login_count;?>
:</span><?php echo $_smarty_tpl->getVariable('user')->value['loginCount'];?>
</li>
							<li class="clearfix"><span><?php echo $_smarty_tpl->getVariable('lang')->value->last_login_time;?>
:</span><?php echo $_smarty_tpl->getVariable('user')->value['lastLoginDate'];?>
</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>