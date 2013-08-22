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
$isGitHub = ipRange::test( $_SERVER['REMOTE_ADDR'] );
$db = new dbHandler( $mvc_objects );

$client=array( 'isClient' => FALSE, 'name' => "" );
foreach( config::$client_secret as $key => $value )
{
    if( @$_POST['client'] == $value )
    {
        $client['isClient'] = TRUE;
        $client['name'] = $key;
    }
}

/** Parse Request *************************************************************
 */
if( $isGitHub )
{
    echo "Hello GitHub!<br />";
    $ghParser = new connectGitHub( );
    
    // validate and prepare payload
    $payload = $_POST['payload'];
    if( config::maxlen > 0 )
        $payload = substr( $payload, 0, config::maxlen );
    
    $mvcEvent = new mvc_event();
    $mvcEvent->add( $db, $payload );
}
/** Test client connected */
elseif( $client['isClient'] )
{

}
/** Unauth visitor */
else
{
    echo "Hello you!<br />";
    $mvcEvent = new mvc_event();
    $mvcEvent->getList( $db );
    
    $ghParser = new connectGitHub( );
    //$ghParser->setStatus( $db, 15, ghStatus::success );
    /*
    $mvcEvent->add( $db, '{ "after" : "237a99b", "repository" : { ' .
                                '"owner" : { "email" : null, ' .
                                            '"name" : ":owner" }, ' .
                                '"url" : "https://github.com/:owner/:repo", "name" : ":repo" }' .
                         '}' );
    */
}

exit( 0 );
?>

