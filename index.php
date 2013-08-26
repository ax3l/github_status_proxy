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

/** Includes ******************************************************************
 */
require_once('config.php');
require_once('enums.php');
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

    $payload = "";
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
        // payload={"action":"request"}
        if( config::debug )
            echo "request new work\n";

        $ghParser = new connectGitHub( );
        $mvcEvent = new mvc_event();
        $mvcEvent->getNext( $db, $ghParser );
    }
    elseif( $dec->action == clientReport::report )
    {
        // payload={"action":"report","eventid":1,"result":"success","output":"..."}
        if( config::debug )
            echo "report test results<br />";

        $eventid = $dec->eventid;
        $result  = $dec->result;
        $output  = $dec->output;

        $ghParser = new connectGitHub( );
        $mvcTest = new mvc_test();
        $mvcTest->add( $db, $ghParser, $eventid, $client['name'], $result, $output );
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
    @header_remove("Content-type");
    @header('Content-type: text/html; charset=utf-8');
    echo "<!DOCTYPE html>";
    echo "<html><head><meta charset='utf-8' /><title>Status report</title>";
    echo '<link href="style/style.css" media="all" rel="stylesheet" type="text/css" />';
    echo "</head>";
    echo "<body>";

    if( config::debug )
        echo "Hello you!\n";

    $statusKey = NULL;
    // Receive request output
    if( isset( $_POST['status'] ) )
        $statusKey = $_POST['status'];
    elseif( isset( $_GET['status'] ) )
        $statusKey = $_GET['status'];
    else
    {
        if( config::debug )
            echo "No status specified\n";
    }
    
    if( isset( $statusKey ) )
    {
        if( config::debug )
            echo "Requested tests for event key " . $statusKey . "\n";

        $mvcEvent = new mvc_event();

        // getByEventKey, joined with `test` table
        //
        $thisEntries = $mvcEvent->getByEventKey( $db, $statusKey );

        // show nice and handy user output of all `test` 's for this `event` table entry
        if( isset($thisEntries[0]['sha'] ) )
        {
            $thisId = $thisEntries[0]['id'];
            $thisEtype = $thisEntries[0]['etype'];
            $thisEstatus = $thisEntries[0]['estatus'];
            $thisSHA = $thisEntries[0]['sha'];
            $thisRepo = $thisEntries[0]['repo'];
            $thisOwner = $thisEntries[0]['owner'];
            $thisSHA_b = $thisEntries[0]['sha_b'];
            $thisRepo_b = $thisEntries[0]['repo_b'];
            $thisOwner_b = $thisEntries[0]['owner_b'];
            $thisResult = $thisEntries[0]['result'];
            $thisOutput = $thisEntries[0]['output'];

            echo "<table><caption>Results for <em>" . $thisEtype . "</em> " . $thisOwner . "/" . $thisRepo . "@" . mb_substr($thisSHA, 0, 7);
            if( $thisEtype == "pull" )
                echo " -> " . $thisOwner_b . "/" . $thisRepo_b . "@" . mb_substr($thisSHA_b, 0, 7);
            echo "</caption>";
            echo "<thead><tr><th>test client</th><th>result</th><th>output</th></tr></thead>";
            echo "<tbody>";
            foreach( $thisEntries as $thisTest )
            {
                echo '<tr class="row_' . $thisTest['result'] . '">';
                echo '<th>' . $thisTest['client'] . '</th>';
                echo '<td class="field_result field_' . $thisTest['result'] . '">' . $thisTest['result'] . '</td>';
                $thisOutput = trim( htmlentities( $thisTest['output'] , ENT_COMPAT, 'UTF-8' ) );
                // remove emails: <*@*>
                $thisOutput = preg_replace( "/\&lt\;.*@.*\&gt\;/", "" , $thisOutput );
                echo '<td><pre>' . $thisOutput . '</pre></td>';
                echo '</tr>';
            }
            echo "</tbody></table>";
        }
    }
    else
    {
        /*
        $ghParser = new connectGitHub( );
        //$ghParser->setStatus( $db, 15, ghStatus::success );

        $mvcEvent = new mvc_event();
        $mvcEvent->add( $db, $ghParser, '{ "after" : "237a99b", "repository" : { ' .
                                    '"owner" : { "email" : null, ' .
                                                '"name" : ":owner" }, ' .
                                    '"url" : "https://github.com/:owner/:repo", "name" : ":repo" }' .
                             '}' );
        */
        if( config::debug )
        {
            echo "<pre>";
            $mvcEvent = new mvc_event();
            $mvcEvent->getList( $db );
            echo "</pre>";
        }
    }
    echo "</body></html>";
}

unset( $db );
exit( 0 );
?>

