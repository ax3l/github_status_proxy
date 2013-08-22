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
      array( 'name' => "lastup", 'type' => "DATETIME", 'prop' => "DEFAULT CURRENT_TIMESTAMP",  'format' => "YYYY-MM-DD HH:MM:SS", 'default' => TRUE),
      array( 'name' => "status", 'type' => "TEXT", 'prop' => "NOT NULL",  'format' => "%s", 'default' => FALSE)
    );

} // class mvc_test

array_push($mvc_objects, new mvc_test() );

?>
