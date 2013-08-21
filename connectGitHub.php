<?php

require_once('config.php');
require_once('ghStatus.php');
require_once('mvc_event.php');

class connectGitHub
{
    function __construct( )
    {
        // ...
    }

    function __destruct()
    {
        // ...
    }
    
    /** set status in GitHub
     *
     * - http://developer.github.com/v3/repos/statuses/
     *     create: POST /repos/:owner/:repo/statuses/:sha
     *         -> state: pending, success, error, failure
     *         -> target_url: config::url . "?status=:sha"
     *         -> description: "The build succeeded!"
     *     response: "Status: 201 Created"
     *
     * - http://developer.github.com/v3/oauth/#scopes
     *     auth: (header) "Authorization: token <...>"
     *     scope: repo:status
     */
    function setStatus( &$db, $dbId, $status )
    {
        /** get event */
        $mvcEvent = new mvc_event();
        $evEntry = $mvcEvent->getById( $db, $dbId );
        
        $url = config::api . "/repos/" .
               $evEntry['owner'] . "/" . $evEntry['repo'] .
               "/statuses/" .       
               $evEntry['sha'];
        $data = '{"state": "' . $status . '", ' .
                ' "target_url": "' . config::url . '?status=' . $evEntry['sha'] . '", ' .
                ' "description": "Build status - ' . $status . '!"}';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: token " . config::access_token ) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        // mozilla CA bundle from
        //   http://curl.haxx.se/docs/caextract.html
        curl_setopt ($ch, CURLOPT_CAINFO, "cacert.pem");
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
        $response = curl_exec($ch);
        if( config::debug )
        {
            curl_error($ch);
            echo "<br />";
            curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
            echo "<br />";
            curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // expected response: "Status: 201 Created"
            echo "<br />";
            echo $response;
        }
        curl_close($ch);
        
        /** @todo set status in database */
    }

} // class connectGitHub

?>
