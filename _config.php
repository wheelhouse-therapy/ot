<?php

define( "SEEDROOT", "../seeds/" );
define( "SEEDCore", SEEDROOT."seedcore/" );

require_once SEEDCore."SEEDCore.php" ;
require_once SEEDROOT."Keyframe/KeyframeForm.php" ;
require_once SEEDROOT."Keyframe/KeyframeDB.php" ;

if( !defined("CATSDIR") ) { define( "CATSDIR", "./" ); }
if( !defined("CATSDIR_IMG") ) { define( "CATSDIR_IMG", CATSDIR."i/img/" ); }

$dirImg = CATSDIR_IMG;
if( !isset($dirJQuery) )    { $dirJQuery =    "./jquery/"; }

?>