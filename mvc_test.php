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

require_once('mvc.php');
require_once('mvc_event.php');

class mvc_test extends mvc
{
    /** table name */
    protected $name = "test";
    
    /** Columns */
    protected $columns = array(
      array( 'name' => "id", 'type' => "INTEGER", 'prop' => "PRIMARY KEY AUTOINCREMENT", 'format' => "%d", 'default' => TRUE),
      array( 'name' => "eventid", 'type' => "INTEGER", 'prop' => "NOT NULL REFERENCES `event`( `id` ) ON UPDATE CASCADE ON DELETE CASCADE", 'format' => "%d", 'default' => FALSE),
      array( 'name' => "client", 'type' => "TEXT", 'prop' => "NOT NULL",  'format' => "%s", 'default' => FALSE),
      array( 'name' => "received", 'type' => "DATETIME", 'prop' => "DEFAULT CURRENT_TIMESTAMP",  'format' => "YYYY-MM-DD HH:MM:SS", 'default' => TRUE),
      array( 'name' => "status", 'type' => "TEXT", 'prop' => "NOT NULL",  'format' => "%s", 'default' => FALSE)
    );

    function add( &$db, $payload )
    {
        if( config::maxlen > 0 )
            $payload = substr( $payload, 0, config::maxlen );

        $dec = json_decode( $payload );
        $url = $dec->repository->url;
        $own = $dec->repository->owner->name;
        $rep = $dec->repository->name;
        $sha = substr( $dec->after, 0, 40 );
        
        $mvcEvent = new mvc_event();
        
        $query = sprintf( $mvcEvent->getInsertSQL(),
                          SQLite3::escapeString( $sha ),
                          SQLite3::escapeString( $own ),
                          SQLite3::escapeString( $rep ),
                          SQLite3::escapeString( $url ),
                          SQLite3::escapeString( $payload )
                        );
        $db->exec( $query );
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
        $results = $db->query("SELECT s.id, s.payload " .
                              "FROM " . $this->name . " AS s " .
                              "WHERE 1 " .
                              "ORDER BY s.id");
        echo "Available events:<br />";
        while( $row = $results->fetchArray() )
        {
           echo $row['id'];
           echo " ";
           echo strlen($row['payload']);
           echo "<br />";
           $dec = json_decode( $row['payload'] );
           print_r( $dec );
           echo "<br />";
           echo @$dec->repository->url . " ";
           echo @$dec->after;
        }
    }

} // class mvc_test

array_push($mvc_objects, new mvc_test() );

?>
