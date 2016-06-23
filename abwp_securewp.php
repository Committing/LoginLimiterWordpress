<?php

/*
	Plugin Name: Secure Wordpress
	Description: Hide bits and bobs around Wordpress and further secure Wordpress. Change settings within plugin file.
	Author: Jack Nicholson
	Version: 0.1
*/


if(session_id() == '') {
    session_start();
}




require_once('securewp.class.php');





$swp = new secureWP();

$swp->limitLogins = true;

$swp->run();
