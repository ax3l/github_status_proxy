<?php
echo "Hey!<br>";
///echo phpversion();
echo "<br>";
//echo $_SERVER['REMOTE_ADDR'];
echo "<br>";
//print_r( SQLite3::version() );
echo "<br>";

//$sql = new SQLite3("test.db");
//if (!$sql) die ($error);

//$stm = "CREATE TABLE Friends(id integer PRIMARY KEY," .
//       "name text NOT NULL, gender text CHECK(gender IN ('M', 'F')))";
//$sql->exec($stm);

//echo "Database Friends created successfully";

// - Round Robin
//     http://www.mail-archive.com/sqlite-users@sqlite.org/msg60752.html
// - put db in a password protected sub dir
// - limit access to this file for github IP range
//     The Public IP addresses for these hooks are: 204.232.175.64/27, 192.30.252.0/22.
//     http://php.net/manual/en/function.ip2long.php
//     http://php.net/manual/en/function.ip2long.php#92544
// - parse json 
//     http://php.net/manual/de/function.json-decode.php
// - github hooks: payload format
//     https://help.github.com/articles/post-receive-hooks


//$results = $db->query("SELECT s.spielid, t.trackdate, t.id
//  FROM tableTracks AS t
//   JOIN tableSpieler AS s
//   WHERE t.trackspieler = s.id
//   ORDER BY t.id");
//while ($row = $results->fetchArray()) {
//   echo $r['trackdate'];
//   echo $r['spielid'];
//   ...
//}

//$sql->close();
?>

