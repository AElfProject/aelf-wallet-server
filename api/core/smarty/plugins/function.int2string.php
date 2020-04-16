<?php
function smarty_function_int2string ($params, &$smarty)
{
	return date($params['str'], $params['time']);
}
?>