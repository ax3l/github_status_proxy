<?php

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
            if( ! $col['autoincr'] )
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
            if( ! $col['autoincr'] )
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
