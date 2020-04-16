<?php

class mdl_reg extends mdl_base
{

	function chkMail ($s, $p = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/')
	{
		return preg_match($p, $s);
	}

	function chkUrl ($s, $p = '/[a-zA-z]+:\/\/[^\s]*/')
	{
		return preg_match($p, $s);
	}

	//6-16个由a-z，A-Z，0-9以及下划线组成的字符串，但不能以数字和下划线开头
	function chkUsername ($s, $p = '/^[a-zA-Z][a-zA-Z0-9_]{5,15}$/')
	{
		return preg_match($p, $s);
	}

	//6-16个由a-z，A-Z，0-9以及下划线组成的字符串
	function chkPassword ($s, $p = '/^[a-zA-Z0-9_]{6,16}$/')
	{
		return preg_match($p, $s);
	}

	function chkPhone ($s, $p = '/\d{3}-\d{8}|\d{4}-\d{7}/')
	{
		return preg_match($p, $s);
	}

	function chkColor ($s, $p = '/^#[0-9A-Za-z]{3,6}$/')
	{
		return preg_match($p, $s);
	}

}

?>