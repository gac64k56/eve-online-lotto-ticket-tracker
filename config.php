<?php

define('LOTTO_SITE', 1);

define('db_host',''); //localhost and 127.0.0.1 for same server hosted MySQL servers, IP address if otherwise
define('db_name',''); //Database name
define('db_user',''); //Database username for the database name
define('db_pass',''); //please make this a secure password
define('db_port',''); //Default port is 3306, change if otherwise

$keyID='';
$characterID='';
$vCode=''; //Must have corp wallet API access.


//$con =  new mysqli(db_host, db_user, db_pass, db_name, db_port) or die ('Could not connect to the database server' . mysqli_connect_error());
if (!$con = connectDB())
    {
        die ('Could not connect to the database server' . mysqli_connect_error());
    }

function connectDB()
{
    if ($con =  @new mysqli(db_host, db_user, db_pass, db_name, db_port))
        {
            return $con;
        }
    else
        {
            die ('Could not connect to the database server ' . mysqli_connect_error());
        }        
}
?>