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

/** A MVC like object for database tables
 */
class mvc
{
    /** table name */
    protected $name = "abc";
    
    /** Columns */
    protected $columns = array();
    
    function getName()
    {
        return $this->name;
    }
    
    function getColumns()
    {
        return $this->columns;
    }
    
    /** Return a SQL template for fresh inserts in this table */
    function getInsertSQL()
    {
        $query = "INSERT INTO " . $this->getName() . " (";
        
        $i=0;
        foreach( $this->getColumns() as $col )
        {
            if( ! $col['default'] )
            {
                if( $i != 0 ) $query .= ", ";
                $i++;
                $query .= $col['name'];
            }
        }
        $query .= ") VALUES (";
        
        $i=0;
        foreach( $this->getColumns() as $col )
        {
            if( ! $col['default'] )
            {
                if( $i != 0 ) $query .= ", ";
                $i++;
                $query .= "'" . $col['format'] . "'";
            }
        }
        $query .= ");";
        
        return $query;
    }
    
} // class mvc

$mvc_objects = array();

?>
