<?php

$dir = dirname( $_SERVER['PHP_SELF'] );

header( 'location: '. str_replace( 'admin', '', $dir ) .'index.php?con=admin&ctl=default&k='.$_GET['k'] );

?>