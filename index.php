<?php

/** Copyright 2013 Axel Huebl
 *
 *  This file is part of github_status_proxy.
 *
 *  github_status_proxy is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  github_status_proxy is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with github_status_proxy. If not, see <http://www.gnu.org/licenses/>.
 */

// To do:
// - write test client binding / api and sceduler
// - allow multiple tests / event
// - Round Robin
//     http://www.mail-archive.com/sqlite-users@sqlite.org/msg60752.html
// - put db in a password protected sub dir

/** Includes ******************************************************************
 */
require_once('config.php');
require_once('ghStatus.php');
require_once('ipRange.php');
require_once('dbHandler.php');
require_once('mvc_event.php');
require_once('connectGitHub.php');


/** Helpers *******************************************************************
 */
@header('Content-type: text/plain');
$isGitHub = ipRange::test( $_SERVER['REMOTE_ADDR'] );
$db = new dbHandler( $mvc_objects );

$client=array( 'isClient' => FALSE, 'name' => "" );
foreach( config::$client_secret as $key => $value )
{
    if( @$_POST['client'] == $value || @$_GET['client'] == $value )
    {
        $client['isClient'] = TRUE;
        $client['name'] = $key;
    }
}

/** Parse Request *************************************************************
 */
if( $isGitHub )
{
    if( config::debug )
        echo "Hello GitHub!\n";

    $ghParser = new connectGitHub( );
    
    // validate and prepare payload
    $payload = $_POST['payload'];
    if( config::maxlen > 0 )
        $payload = substr( $payload, 0, config::maxlen );
    
    $mvcEvent = new mvc_event();
    $mvcEvent->add( $db, $ghParser, $payload );
}
/** Test client connected */
elseif( $client['isClient'] )
{
    if( config::debug )
        echo "Hello " . $client['name'] . "\n";

    // Receive request payload
    if( isset( $_POST['payload'] ) )
        $payload = $_POST['payload'];
    elseif( isset( $_GET['payload'] ) )
        $payload = $_GET['payload'];
    else
    {
        if( config::debug )
            echo "No payload specified\n";
    }

    // get new work or report a finished test?
    if( config::maxlen > 0 )
        $payload = substr( $payload, 0, config::maxlen );

    $dec = json_decode( $payload );
    if( $dec->action == clientReport::request )
    {
        if( config::debug )
            echo "request new work\n";

        $mvcEvent = new mvc_event();
        $mvcEvent->getNext( $db );
    }
    elseif( $dec->action == clientReport::report )
    {
        if( config::debug )
            echo "report test results<br />";

        // ...
    }
    else
    {
        if( config::debug )
            echo "No known action found (in payload)\n";
    }
}
/** Unauth visitor */
else
{
    if( config::debug )
        echo "Hello you!\n";

    $mvcEvent = new mvc_event();
    $mvcEvent->getList( $db );
    
    $ghParser = new connectGitHub( );
    //$ghParser->setStatus( $db, 15, ghStatus::success );
    /*
    $mvcEvent->add( $db, $ghParser, '{ "after" : "237a99b", "repository" : { ' .
                                '"owner" : { "email" : null, ' .
                                            '"name" : ":owner" }, ' .
                                '"url" : "https://github.com/:owner/:repo", "name" : ":repo" }' .
                         '}' );
    */
}

unset( $db );
exit( 0 );
?>

