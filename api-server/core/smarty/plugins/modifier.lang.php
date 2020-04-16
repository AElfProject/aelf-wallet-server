<?php

require_once 'core/lang/lang.php';

function smarty_modifier_lang( $string ) {
	return getCustomLangStr( $string );
}

?>