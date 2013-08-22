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

require_once('ghStatus.php');
require_once('mvc.php');

class mvc_event extends mvc
{
    /** table name */
    protected $name = "event";
    
    /** Columns
     *
     *  @todo Actually, one should de-clutter this table to contain only
     *        - id
     *        - etype
     *        - received
     *        - payload
     *  and store commits and pulls in separate tables.
     */
    protected $columns = array(
      array( 'name' => "id", 'type' => "INTEGER", 'prop' => "PRIMARY KEY AUTOINCREMENT", 'format' => "%d", 'default' => TRUE),
      array( 'name' => "etype", 'type' => "TEXT", 'prop' => "NOT NULL",     'format' => "%s", 'default' => FALSE),
      array( 'name' => "sha", 'type' => "CHAR(40)", 'prop' => "NOT NULL",   'format' => "%s", 'default' => FALSE),
      array( 'name' => "sha_p", 'type' => "CHAR(40)", 'prop' => "NOT NULL", 'format' => "%s", 'default' => FALSE),
      array( 'name' => "owner", 'type' => "TEXT", 'prop' => "NOT NULL",     'format' => "%s", 'default' => FALSE),
      array( 'name' => "owner_p", 'type' => "TEXT", 'prop' => "NOT NULL",   'format' => "%s", 'default' => FALSE),
      array( 'name' => "repo", 'type' => "TEXT", 'prop' => "NOT NULL",      'format' => "%s", 'default' => FALSE),
      array( 'name' => "repo_p", 'type' => "TEXT", 'prop' => "NOT NULL",    'format' => "%s", 'default' => FALSE),
      array( 'name' => "url", 'type' => "TEXT", 'prop' => "NOT NULL",       'format' => "%s", 'default' => FALSE),
      array( 'name' => "url_p", 'type' => "TEXT", 'prop' => "NOT NULL",     'format' => "%s", 'default' => FALSE),
      array( 'name' => "git", 'type' => "TEXT", 'prop' => "NOT NULL",       'format' => "%s", 'default' => FALSE),
      array( 'name' => "git_p", 'type' => "TEXT", 'prop' => "NOT NULL",     'format' => "%s", 'default' => FALSE),
      array( 'name' => "received", 'type' => "DATETIME", 'prop' => "DEFAULT CURRENT_TIMESTAMP", 'format' => "YYYY-MM-DD HH:MM:SS", 'default' => TRUE),
      array( 'name' => "payload", 'type' => "TEXT", 'prop' => "NOT NULL",  'format' => "%s", 'default' => FALSE)
    );

    function add( &$db, $payload )
    {
        if( config::maxlen > 0 )
            $payload = substr( $payload, 0, config::maxlen );

        // detect type of event
        //   http://developer.github.com/v3/repos/hooks/#create-a-hook
        //   http://developer.github.com/v3/activity/events/types/#pullrequestevent
        $dec = json_decode( $payload );
        
        $eventType = ghStatus::commit;
        if( isset( $dec->pull_request) )
            $eventType = ghStatus::pull;

        $url = ""; $git = ""; $own = ""; $rep = ""; $sha = "";
        $url_p = ""; $git_p = ""; $own_p = ""; $rep_p = ""; $sha_p = "";

        if( $eventType == ghStatus::commit )
        {
            $url = $dec->repository->url;
            $own = $dec->repository->owner->name;
            $rep = $dec->repository->name;
            $git = "git://github.com/" . $own . "/" . $rep . ".git";
            $sha = substr( $dec->after, 0, 40 );

            $url_p = $url;
            $git_p = $git;
            $own_p = $own;
            $rep_p = $rep;
            $sha_p = $sha;
        }
        elseif( $eventType == ghStatus::pull )
        {
            // http://developer.github.com/v3/activity/events/types/#pullrequestevent
            //   opened, closed, synchronize or reopened
            if( $dec->action == "closed" )
                return;

            // base repo to merge to
            $url = $dec->pull_request->base->repo->html_url;
            $git = $dec->pull_request->base->repo->clone_url;
            $own = $dec->pull_request->base->repo->owner->login;
            $rep = $dec->pull_request->base->repo->name;
            $sha = substr( $dec->pull_request->base->sha, 0, 40 );

            // head of the branch of the forked repo to merge from
            $url_p = $dec->pull_request->head->repo->html_url;
            $git_p = $dec->pull_request->head->repo->clone_url;
            $own_p = $dec->pull_request->head->repo->owner->login;
            $rep_p = $dec->pull_request->head->repo->name;
            $sha_p = substr( $dec->pull_request->head->sha, 0, 40 );
        }
        else
        {
            echo "Unknown event";
            return;
        }
        
        $mvcEvent = new mvc_event();

        $query = sprintf( $mvcEvent->getInsertSQL(),
                          SQLite3::escapeString( $eventType ),
                          SQLite3::escapeString( $sha ),
                          SQLite3::escapeString( $sha_p ),
                          SQLite3::escapeString( $own ),
                          SQLite3::escapeString( $own_p ),
                          SQLite3::escapeString( $rep ),
                          SQLite3::escapeString( $rep_p ),
                          SQLite3::escapeString( $url ),
                          SQLite3::escapeString( $url_p ),
                          SQLite3::escapeString( $git ),
                          SQLite3::escapeString( $git_p ),
                          SQLite3::escapeString( $payload )
                        );
        $db->exec( $query );

        /// @todo trigger github pending status
    }
    
    function getById( &$db, $id )
    {
        $queryTpl = "SELECT * " .
                    " FROM " . $this->name .
                    " WHERE id='%d';";
        $query = sprintf( $queryTpl,
                          SQLite3::escapeString( $id )
                        );
        
        $result = $db->query( $query );
        return $results->fetchArray();
    }
    
    function getList( &$db )
    {
        $results = $db->query("SELECT * " .
                              " FROM " . $this->name .
                              " WHERE 1 " .
                              " ORDER BY id");
        echo "Available events:<br />";
        while( $row = $results->fetchArray() )
        {
            echo "Meta: <br />";
            echo $row['received']  . "<br /><br />";
            echo $row['etype']  . "<br /><br />";
            //echo $row['payload']  . "<br />";

            echo "Base: <br />";
            echo $row['owner'] . "<br />";
            echo $row['repo']  . "<br />";
            echo $row['sha']   . "<br />";
            echo $row['url']   . "<br />";

            if( $row['etype'] == ghStatus::pull )
            {
                echo "Head: <br />";
                echo $row['owner_p'] . "<br />";
                echo $row['repo_p']  . "<br />";
                echo $row['sha_p']   . "<br />";
                echo $row['url_p']   . "<br />";
            }
            echo "<br />";
        }
    }

} // class mvc_event

array_push($mvc_objects, new mvc_event() );

?>
