<?php

require_once('config.php');
require_once('mvc.php');
require_once('mvc_event.php');

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
        
        // check for tables
        foreach( $mvc_objects as $table )
        {
            if( config::debug )
                echo "Looking for table \"" . $table->getName() . "\"<br />";
            
            $table_ex = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . $table->getName() . "'";
            $result = $this->sql->query( $table_ex );
            
            if( ! $result )
                die( "Database Owner does not match web server user!" );
            
            // create missing table
            if( $result->fetchArray() == NULL )
            {
                if( config::debug )
                    echo "create table \"" . $table->getName() . "\"<br />";
                
                // SQLite 3.3+ : CREATE TABLE if not exists
                $cst = "CREATE TABLE " . $table->getName() . " (";
                
                $i=0;
                foreach( $table->getColumns() as $col )
                {
                    if( $i != 0 ) $cst .= ", ";
                    $i++;
                    $cst .= $col['name'] . " " . $col['value'];
                    if( $col['autoincr'] )
                        $cst .= " AUTOINCREMENT";
                }
                $cst .= ");";
                
                $this->sql->exec( $cst );
            }
            else
            {
                if( config::debug )
                    echo "table \"" . $table->getName() . "\" found<br />";
            }
        }
    }

    function __destruct()
    {
        $this->sql->close();
    }
    
    function exec( $cmd )
    {
        return $this->sql->exec( $cmd );
    }
    
    function query( $query )
    {
        return $this->sql->query( $query );
    }
    
    private $dbName;
    public $sql;

} // class dbHandler

?>
