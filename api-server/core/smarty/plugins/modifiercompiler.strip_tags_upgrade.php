<?php

function smarty_modifiercompiler_strip_tags_upgrade($params, $compiler)
{
   return 'strip_tags_upgrade(' . $params[0] . ')';
} 

?>