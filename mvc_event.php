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

require_once('config.php');
require_once('enums.php');
require_once('mvc.php');
require_once('mvc_test.php');

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
      array( 'name' => "key", 'type' => "TEXT", 'prop' => "NOT NULL",       'format' => "%s", 'default' => FALSE),
      array( 'name' => "etype", 'type' => "TEXT", 'prop' => "NOT NULL",     'format' => "%s", 'default' => FALSE),
      array( 'name' => "estatus", 'type' => "TEXT", 'prop' => "NOT NULL",   'format' => "%s", 'default' => FALSE),
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
      array( 'name' => "lastup", 'type' => "DATETIME", 'prop' => "DEFAULT CURRENT_TIMESTAMP", 'format' => "YYYY-MM-DD HH:MM:SS", 'default' => TRUE),
      array( 'name' => "payload", 'type' => "TEXT", 'prop' => "NOT NULL",  'format' => "%s", 'default' => FALSE)
    );

    function add( &$db, &$ghParser, $payload )
    {
        if( config::maxlen > 0 )
            $payload = substr( $payload, 0, config::maxlen );

        // detect type of event
        //   http://developer.github.com/v3/repos/hooks/#create-a-hook
        //   http://developer.github.com/v3/activity/events/types/#pullrequestevent
        $dec = json_decode( $payload );
        
        $eventType = ghType::commit;
        if( isset( $dec->pull_request) )
            $eventType = ghType::pull;

        $url = ""; $git = ""; $own = ""; $rep = ""; $sha = "";
        $url_p = ""; $git_p = ""; $own_p = ""; $rep_p = ""; $sha_p = "";

        if( $eventType == ghType::commit )
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
        elseif( $eventType == ghType::pull )
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
                          SQLite3::escapeString( crypt( config::statusSalt . rand() . $payload ) ),
                          SQLite3::escapeString( $eventType ),
                          SQLite3::escapeString( eventStatus::received ),
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
        $newID = $db->insert( $query );

        // trigger github pending status
        $ghParser->setStatus( $db, $newID, ghStatus::pending,
                              "received by status proxy" );

        /// @todo insert to `test` table as "has to be tested" for each test client
        /// ...
    }

    function setStatus( &$db, $id, $estatus )
    {
        $upQuery = sprintf( "UPDATE `%s`" .
                            " SET `estatus`='%s'," .
                            "     `lastup`=datetime('now')" .
                            " WHERE `id`='%d';",
                            SQLite3::escapeString( $this->name ),
                            SQLite3::escapeString( eventStatus::scheduled ),
                            SQLite3::escapeString( $id )
                          );
        $db->exec( $upQuery );
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

        if( ! $result )
            return NULL;

        return $result->fetchArray();
    }

    function getByEventKey( &$db, $key )
    {
        $mvcTest = new mvc_test();
        $mvcTest->getName();

        $queryTpl = "SELECT *, %s.id as %s_id" .
                    " FROM `%s`" .
                    " LEFT JOIN `%s`" .
                    " ON %s.id=%s.eventid" .
                    " WHERE key='%s';";
        $query = sprintf( $queryTpl,
                          $mvcTest->getName(),
                          $mvcTest->getName(),
                          $this->getName(),
                          $mvcTest->getName(),
                          $this->getName(),
                          $mvcTest->getName(),
                          SQLite3::escapeString( $key )
                        );

        $result = $db->query( $query );

        if( ! $result )
            return NULL;

        $allRows = array();
        while( $thisRow = $result->fetchArray() )
        {
            array_push( $allRows, $thisRow );
        }
        return $allRows;
    }

    /** get new work and mark as eventStatus::scheduled
     *
     *  start with oldest eventStatus::received events
     */
    function getNext( &$db, &$ghParser )
    {
        $results = $db->query("SELECT * " .
                              " FROM " . $this->name .
                              " WHERE `estatus`='" . eventStatus::received . "'" .
                              " ORDER BY `lastup` ASC" .
                              " LIMIT 1");
        while( $row = $results->fetchArray() )
        {
            $thisEvent = array(
                'id' => $row['id'],
                'lastup' => $row['lastup'],
                'etype' => $row['etype'],
                'base' => array(
                    'owner' => $row['owner'],
                    'repo' => $row['repo'],
                    'git' => $row['git'],
                    'sha' => $row['sha'],
                    'url' => $row['url']
                )
            );
            if( $row['etype'] == ghType::pull )
            {
                array_push($thisEvent, array(
                    'head' => array(
                        'owner' => $row['owner_p'],
                        'repo' => $row['repo_p'],
                        'git' => $row['git_p'],
                        'sha' => $row['sha_p'],
                        'url' => $row['url_p']
                        )
                    )
                );
            }

            //$json = json_encode( $thisEvent );
            //echo $json;
            print_r( $thisEvent );

            // mark as scheduled in `event` table
            $this->setStatus( $db, $thisEvent['id'], eventStatus::scheduled );

            // trigger github pending status (update to pending at client side)
            $ghParser->setStatus( $db, $thisEvent['id'], ghStatus::pending,
                                  "scheduled to test client" );
        }
    }
    
    function getList( &$db )
    {
        $results = $db->query("SELECT * " .
                              " FROM `" . $this->name . "`" .
                              " WHERE 1 " .
                              " ORDER BY id");
        echo "Available events:\n";
        while( $row = $results->fetchArray() )
        {
            $thisEvent = array(
                'id'      => $row['id'],
                'key'     => $row['key'],
                'lastup'  => $row['lastup'],
                'etype'   => $row['etype'],
                'estatus' => $row['estatus'],
                'base'    => array(
                    'owner' => $row['owner'],
                    'repo' => $row['repo'],
                    'git' => $row['git'],
                    'sha' => $row['sha'],
                    'url' => $row['url']
                )
            );
            if( $row['etype'] == ghType::pull )
            {
                array_push($thisEvent, array(
                    'head' => array(
                        'owner' => $row['owner_p'],
                        'repo' => $row['repo_p'],
                        'git' => $row['git_p'],
                        'sha' => $row['sha_p'],
                        'url' => $row['url_p']
                        )
                    )
                );
            }

            //$json = json_encode( $thisEvent );
            //echo $json;
            print_r( $thisEvent );
            echo "\n";
        }
    }

} // class mvc_event

array_push($mvc_objects, new mvc_event() );

?>
