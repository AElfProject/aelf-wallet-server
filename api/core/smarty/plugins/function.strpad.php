<?php
function smarty_function_strpad ($params, &$smarty)
{
	if ($params['len'] > 0) return str_pad($params['str'], $params['len'] * 6, $params['pad']);
	else return '';
}
?>