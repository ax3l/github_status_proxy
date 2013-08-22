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
require_once('mvc.php');
require_once('mvc_event.php');
require_once('mvc_test.php');

/** SQLite3 DB Handler
 *
 *  \see http://php.net/manual/de/book.sqlite3.php
 */
class dbHandler
{
    function __construct( $mvc_objects )
    {
        $this->dbName = config::dbName;

        if( ! class_exists('SQLite3') ) die( "Missing SQLite3 support!" );
        $this->sql = new SQLite3( $this->dbName );
        if( ! $this->sql ) die( "Error opening SQLite database..." );

        // set busy timeout in milliseconds
        $this->sql->busyTimeout( config::dbTimeout );

        // check for tables
        foreach( $mvc_objects as $table )
        {
            if( config::debug )
                echo "Looking for table \"" . $table->getName() . "\"\n";
            
            $table_ex = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . $table->getName() . "'";
            $result = $this->sql->query( $table_ex );
            
            if( ! $result )
                die( "Database Owner does not match web server user!" );
            
            // create missing table
            if( $result->fetchArray() == NULL )
            {
                if( config::debug )
                    echo "create table \"" . $table->getName() . "\"\n";
                
                //$this->sql->exec( "PRAGMA foreign_keys = ON;" );

                // SQLite 3.3+ : CREATE TABLE if not exists
                $cst = "CREATE TABLE " . $table->getName() . " (";
                
                $i=0;
                foreach( $table->getColumns() as $col )
                {
                    if( $i != 0 ) $cst .= ", ";
                    $i++;
                    $cst .= $col['name'] . " " . $col['type'] . " " . $col['prop'];
                }
                $cst .= ");";
                
                if( config::debug )
                    echo $cst;
                $this->sql->exec( $cst );
            }
            else
            {
                if( config::debug )
                    echo "table \"" . $table->getName() . "\" found\n";
            }
        }
    }

    function __destruct()
    {
        $this->sql->close();
        unset($this->sql);
    }
    
    function exec( $cmd )
    {
        return $this->sql->exec( $cmd );
    }
    
    function insert( $cmd )
    {
        $this->exec( $cmd );
        return $this->sql->lastInsertRowID();
    }

    function query( $query )
    {
        return $this->sql->query( $query );
    }
    
    private $dbName;
    public $sql;

} // class dbHandler

?>
