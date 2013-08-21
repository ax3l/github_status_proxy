<?php

class config
{
    // tokens
    // limits

    // GitHub OAuth access token
    //   the token needs at least repo:status privileges
    //   create it with:
    //     curl -i -u <userName> -d
    //     '{"scopes": ["repo:status"], "note": ["GitHub Proxy"],
    //     "note_url": ["yourUrl"]}' https://api.github.com/authorizations
    const access_token = "...";
    
    // Name of our database to store tasks and status
    const dbName = "db/states.db";
    
    // Allowed IP Range for GitHub POST origins
    public static $github_iprange = array(
     array( 'ip' => "204.232.175.64", 'mask' => "27"),
     array( 'ip' => "192.30.252.0",   'mask' => "22") );
     
    // debug output - DISABLE for production runs!
    const debug = TRUE;
    
    // maximum number of commits/pull requests to store
    // 0 means: no limit
    const maxentries = 0;
    
    // maximum length of the payload in chars
    // 0 means: no limit
    const maxlen = 10000;
    
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
