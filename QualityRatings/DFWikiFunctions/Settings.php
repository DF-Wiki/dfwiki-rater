<?php
$wgNamespaceAliases['MDF'] = 1000;
$wgNamespaceAliases['MDF_TALK'] = 1001;

$wgNamespaceAliases['U'] = 2;
$wgNamespaceAliases['F'] = 6;
$wgNamespaceAliases['T'] = 10;
$wgNamespaceAliases['H'] = 12;
$wgNamespaceAliases['C'] = $wgNamespaceAliases['CAT'] = 14;

$DFReleases = array(
    1 => 110,
    2 => 106,
    3 => 112,
    4 => 114,
);

foreach ($DFReleases as $id => $ns) {
    $wgNamespaceAliases['Rel' . $id] = $wgNamespaceAliases['V' . $id] = $ns;
    $wgNamespaceAliases['Rel' . $id . '_talk'] = $wgNamespaceAliases['V' . $id . '_talk'] = $ns;
}

if (array_key_exists('debug', $_GET)) {
    $wgHooks['ParserFirstCallInit'][] = function($parser=null){
    error_reporting( E_ALL | E_STRICT );
    ini_set( 'display_errors', 1 );
    ini_set('display_startup_errors', 1);
    
    // Debugging
    global $wgShowErrors,  $wgShowExceptionDetails, $wgShowSQLErrors, $wgDebugDumpSql;
    $wgShowErrors = true;
    $wgShowExceptionDetails = true;
    $wgShowSQLErrors = true;
    $wgDebugDumpSql  = true;
    };
}

