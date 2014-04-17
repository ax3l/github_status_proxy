<?php
/** Copyright 2013-2014 Axel Huebl
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

require_once('config.php');
require_once('enums.php');
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

    /** check if a user is in a certain Team
     *
     * - https://developer.github.com/v3/orgs/teams/#get-team-member
     *     response: "Status: 204 No Content" -> is a member
     *               "Status: 404 Not Found"  -> is NOT a member OR
     *                                           scope read:org missing
     *
     * \return true (is in the team) or false (is NOT in the team)
     */
    function isUserInTeam( $username, $teamid )
    {
        debugLog("is user `" .
                 $username . "` in team with id = `" . $teamid . "`");

        $url = config::api . "/teams/" .
               $teamid .
               "/members/" .
               $username;

        /** send to GitHub */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, "GitHub Status Proxy");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: token " . config::access_token ) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        // mozilla CA bundle from
        //   http://curl.haxx.se/docs/caextract.html
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if( config::debug )
        {
            curl_error($ch);
            echo "\n";
            curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
            echo "\n";
            echo $returnCode;
            // expected response: "Status: 202 No Content" OR
            //                    "Status: 404 Not Found"
            echo "\n";
            //echo $response;
        }

        /** return state */
        if( $returnCode == 204 )
        {
            debugLog("`" .
                     $username . "` found in team with id = `" .
                     $teamid . "` (Status: " .
                     $returnCode . ") ");
            return true;
        }
        elseif( $returnCode == 404 )
        {
            debugLog("`" .
                     $username . "` NOT found in team with id = `" .
                     $teamid . "` (Status: " .
                     $returnCode . ") " .
                     $response . " - " . curl_error($ch) );
            return false;
        }
        else
        {
            debugLog("`" .
                     $username . "` request for team with id = `" .
                     $teamid . "` failed (Status: " .
                     $returnCode . ") " .
                     $response . " - " . curl_error($ch),
                     true );
        }
        curl_close($ch);
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
    function setStatus( &$db, $dbId, $status, $postDesc = NULL )
    {
        debugLog("writing status `" .
                 $status . "` for id = `" . $dbId . "`");

        /** get event */
        $mvcEvent = new mvc_event();
        $evEntry = $mvcEvent->getById( $db, $dbId );

        /** description */
        $description = "Test status - " . $status;
        if( isset( $postDesc ) )
            $description .= " - " . $postDesc;

        /** data */
        // commits
        $owner = $evEntry['owner'];
        $repo = $evEntry['repo'];

        // pull requests - write status in BASE repos
        if( $evEntry['etype'] == ghType::pull )
        {
            $owner = $evEntry['owner_b'];
            $repo = $evEntry['repo_b'];
        }

        /** JSON params */
        $url = config::api . "/repos/" .
               $owner . "/" . $repo .
               "/statuses/" .
               $evEntry['sha'];
        $data = '{"state": "' . $status . '", ' .
                ' "target_url": "' . config::url . '?status=' . $evEntry['key'] . '", ' .
                ' "description": "' . $description . '"}';

        /** send to GitHub */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, "GitHub Status Proxy");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: token " . config::access_token ) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        // mozilla CA bundle from
        //   http://curl.haxx.se/docs/caextract.html
        curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $returnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if( !$response || $returnCode != 201 )
            debugLog("(" .
                     $returnCode . ") " .
                     $response . " - " . curl_error($ch),
                     true );

        if( config::debug )
        {
            curl_error($ch);
            echo "\n";
            curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
            echo "\n";
            echo $returnCode;
            // expected response: "Status: 201 Created"
            echo "\n";
            //echo $response;
        }
        curl_close($ch);
    }

} // class connectGitHub

?>
