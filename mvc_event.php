<?php

require_once('mvc.php');

class mvc_event extends mvc
{
    /** table name */
    protected $name = "event";
    
    /** Columns */
    protected $columns = array(
      array( 'name' => "id", 'value' => "INTEGER PRIMARY KEY", 'format' => "%d", 'autoincr' => TRUE),
      array( 'name' => "sha", 'value' => "CHAR(40) NOT NULL",  'format' => "%s", 'autoincr' => FALSE),
      array( 'name' => "owner", 'value' => "TEXT NOT NULL",    'format' => "%s", 'autoincr' => FALSE),
      array( 'name' => "repo", 'value' => "TEXT NOT NULL",     'format' => "%s", 'autoincr' => FALSE),
      array( 'name' => "url", 'value' => "TEXT NOT NULL",      'format' => "%s", 'autoincr' => FALSE),
      array( 'name' => "payload", 'value' => "TEXT NOT NULL",  'format' => "%s", 'autoincr' => FALSE)
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

} // class mvc_event

array_push($mvc_objects, new mvc_event() );

?>
