SecureWP
===================

This class is intended for securing Wordpress. Update and add new features when you realise new ways to secure our sites.

----------

Usage
-------------

**abwp_securewp.php**
Modify this file to edit your settings.

    require_once('securewp.class.php');
    
    $swp = new secureWP();
    
    $swp->limitLogins = true;
    
    $swp->run();


Overview
-------------

> **Features:**

> - Login Rate limiter.

####  - Login Rate Limiter

1. Set the login limit with: `$swp->loginLimit = 5;`

2. Set the amount of time in minutes you want to disable the user from attempting again: `$swp->loginTimeoutMinutes = 5;`

Other Recommendations
-------------
Add this to your config file to change `wp-content` to a custom folder. (You must also rename `wp-content` to the new name:

    define( 'WP_CONTENT_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets' );
    define( 'WP_CONTENT_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/assets' );
    // Needs updating to automatically detect https
