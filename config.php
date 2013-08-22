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

class config
{
    // tokens
    // limits

    // create your hooks by
    //   http://developer.github.com/v3/repos/hooks/#create-a-hook
    //   POST /repos/:owner/:repo/hooks
    //   curl -i -u :user -d '{"name": "web", "active": true, "events": ["push", "pull_request"], "config": {"url": ":url", "content_type": "form"}}'
    //        https://api.github.com/repos/:user/:repo/hooks

    // GitHub OAuth access token
    //   the token needs at least repo:status privileges
    //   create it with:
    //     curl -i -u <userName> -d
    //     '{"scopes": ["repo:status"], "note": ["GitHub Proxy"],
    //     "note_url": ["yourUrl"]}' https://api.github.com/authorizations
    const access_token = "...";

    // Client secret (one secret per test client)
    //   clients must auth theirselves with a POST variable "client" using the
    //   secret its value
    public static $client_secret = array( "client" => "..." );

    // Timout after which the test marked as errored and the test is scheduled again
    // as "has to be tested"
    // in hours
    // note: not implemented yet
    const clientTimeout = 2.0;

    // Re-Try a client test at the end of the queue if the client errored internally
    const retryOnClientError = FALSE;

    // Name of our database to store tasks and status
    const dbName = "db/states.db";

    // Concurency busy timeout of the database
    // in milliseconds
    const dbTimeout = 3000;

    // Allowed IP Range for GitHub POST origins
    public static $github_iprange = array(
     array( 'ip' => "204.232.175.64", 'mask' => "27"),
     array( 'ip' => "192.30.252.0",   'mask' => "22") );

    // Salt for unauthorized user requests
    const statusSalt = "...";

    // debug output - DISABLE for production runs!
    // note: this will probably confuse your connected test clients
    const debug = TRUE;

    // maximum number of commits/pull requests to store
    // 0 means: no limit
    /// @todo maxentries not implemented yet
    const maxentries = 0;

    // maximum length of the payload / test client output in chars
    // 0 means: no limit
    const maxlen = 100000;

    // url of this proxy
    const url = "...";

    // github api base url
    const api = "https://api.github.com";

} // class config

/** Debug settings */
if( config::debug == TRUE )
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

?>
