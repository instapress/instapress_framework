
<?php
header('Content-Type: text/html; charset=utf-8');
/*$lang="hi_IN";
$language = !strstr( $lang, ".utf8" ) ? $lang . ".utf8" : $lang;
putenv("LANG=$language"); 
setlocale(LC_ALL, $language);
//$domain = 'internal_messages';
$domain = 'internal_messages';
bindtextdomain($domain,'../../Locale'); 
bind_textdomain_codeset($domain,'UTF-8'); 
textdomain($domain);*/
// ===============================================================================
//echo dirname( __FILE__ );

echo gettext( "Create Dossier" )." - ".gettext( "Add Assignments" );

require_once( LIB_PATH . "../admin_php/ipeditorial/add_dossier_template_assignment.php" );

?>



