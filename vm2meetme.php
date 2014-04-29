<?php

// Def var for seperating full name to first and last
$name = array();

//opne database connection
$con = mysql_connect("localhost", "meetmeuser", "m33tm3p@ssw0rd") or die(mysql_error());
mysql_select_db("meetme") or die(mysql_error());

// set all users to inactive
$sql = "update user set active = 0 where admin = 'User'";
$result = mysql_query($sql);


// open file for reading
$handle = fopen("/etc/asterisk/voicemail.conf", "r");

// read line in if line contains =>
$row = 1;
while (($data = fgetcsv($handle, 5000, ",")) !== FALSE)
    {
    if (preg_match('/\=\>/',$data[0]) != 0)
        {
        $name = explode(' ',$data[1]);
        //opne database connection
        $con = mysql_connect("localhost", "meetmeuser", "m33tm3p@ssw0rd") or die(mysql_error());
        mysql_select_db("meetme") or die(mysql_error());
        // test if email already exists
        $sql = "Select email from user where email = '"
        . mysql_real_escape_string($data[2])
        . "'";
        $result = mysql_query($sql);
        $row = mysql_fetch_row($result);
        if ($row[0] != $data[2])
            {
            // insert new user
            $sql =  "INSERT INTO user (email,first_name,last_name,telephone,password,admin,active) VALUES ('$data[2]','"
            . mysql_real_escape_string($name[0])
            . "','"
            . mysql_real_escape_string($name[1])
            . "','503943"
            . substr($data[0],0,4)
            . "','"
            . substr($data[0],8,10)
            . "','User','1')\n";
            $result = mysql_query($sql);
	    }
        else
	    {
	    // update users name, telephone#, password, active
            $sql =  "update user set first_name = '"
            . mysql_real_escape_string($name[0])
            . "', last_name = '"
            . mysql_real_escape_string($name[1])
            . "', telephone = '"
            . "503943"
            . substr($data[0],0,4)
            . "', password = '"
            . substr($data[0],8,10)
            . "', active = 1"
            . " WHERE email = '$data[2]'";
            $result = mysql_query($sql);
            }
        // get user Id
        $sql = "SELECT id FROM user WHERE email = '"
        . mysql_real_escape_string($data[2])
        . "'";
        $result = mysql_query($sql);
        $clientid = mysql_fetch_row($result);
        // create conferenc room for today for each user
        $sql = "INSERT INTO booking (clientid,confno,pin,adminpin,starttime,endtime,maxuser,status,confowner,confdesc,adminopts,opts) "
        . "VALUES ($clientid[0],'"
        . substr($data[0],0,4)
        . "','1','"
        . substr($data[0],8,10)
        . "',"
	. "NOW()"
        . ","
        . "DATE_ADD(NOW(),INTERVAL 1 DAY)"
        . ",'10','A','$data[2]','"
        . substr($data[0],0,4)
        . "','aAs','swM')";
        $result = mysql_query($sql);
        }
    }
// remove any user not updated or inserted
$sql = "delete from user where admin = 'User' and active = 0";
$result = mysql_query($sql);

// close database connection
mysql_close($con);

// close input file
fclose($handle);
?>