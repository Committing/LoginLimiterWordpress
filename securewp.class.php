<?php



/**
 * Secure and hide Wordpress class
 *
 * PHP version 5.3+
 *
 * @author     Jack <jack@ab-uk.com>
 * @version    0.1
 */

class secureWP
 {

 	/**
     * Whether or not to enable a limit on login attempts
     * @var bool
     * @access public
     */
    public $limitLogins = false;

    /**
     * The login attempt limit for every failed attempt
     * @var int
     * @access public
     */
    public $loginLimit = 5;

    /**
     * Minutes to disable login form for
     * @var int
     * @access public
     */
    public $loginTimeoutMinutes = 5;

    /**
     * Current login faliled attempts
     * @var int
     * @access private
     */
    private $loginAttempts = 0;

    /**
     * Set the current login attempts property
     */
    public function __construct() {

    	if (!isset($_SESSION['login_attempts'])) {
            $this->loginAttempts = $_SESSION['login_attempts'] = 0;
        } else {
            $this->loginAttempts = $_SESSION['login_attempts'];
        }

        $this->setLoginTimeoutMinutes();
    }

 	/**
 	 * Determines whether or not we are on the Wordpress login page
	 * @return bool true/false if on wordpress login page
 	 */
 	private function isLoginPage() {
	    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}

    /**
     * Action to take when a failed login occurs
     * @return 
     */
    public function limitLogins() {
        if ($this->isLoginPage() === true) {
            
            add_action('wp_login_failed', array($this, 'loginFailAction'));

            // Add +1 because the wp_login_failed action runs later
            if ( ($this->loginAttempts + 1) >= $this->loginLimit) {
                $this->setLoginTimeoutMinutes();

                add_filter('authenticate', array($this, 'loginError'), 100, 3);
                add_action('login_form', array($this, 'disableLoginForm'), 100, 2 );
            }
        }
    }

    /**
     * Set a timer before user can reattempt new logins
     * @return int Time left in seconds
     */
    public function setLoginTimeoutMinutes() {
        $time_left = $this->remainingTimeout();
        
        if ($time_left <= 0) {
            $this->loginAttempts = 0;
            unset($_SESSION['login_attempts']);
            unset($_SESSION['started']);
            return true;
        } else {
            return $time_left;
        }
    }

    /**
     * Calculate remamining seconds until login is enabled again
     * @return int Seconds remaining
     */
    public function remainingTimeout() {
        if (!isset($_SESSION['started'])) {
            $_SESSION['started'] = time();
        }
        $duration = $this->loginTimeoutMinutes * 60;
        $time = ($duration - (time() - $_SESSION['started']));
        return $time;
    }

    /**
     * Force the login to fail
     * @return bool true/false
     */
    public function loginError($user, $username, $password) {
        return null;
    }

    /**
     * Stop the form showing and show an error that they have been disabled temporarily
     * @return string html/css/js of error
     */
    public function disableLoginForm() {
        $output = '';
        $output .= '<style>#custom_error { margin-bottom:15px;clear:both;background-color:#fbfbfb;border-left: 4px solid #dc3232;padding: 12px;-webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); }</style>';
        $output .= '<div id="custom_error"><strong>Login disabled</strong><br />Attempt limit reached: ' . $this->loginLimit . '/' . $this->loginLimit . '<br />Timeout: <span id="login_timer">' . $this->remainingTimeout() . '</span> seconds</div>';
        $output .= '<script>setInterval(function () { var parent=document.getElementById("custom_error"); var ele = document.getElementById("login_timer"); var num = parseInt(ele.innerHTML); document.getElementById("login_timer").innerHTML = num-1; if (num <= 1){parent.style.display="none"} }, 1000);</script>';
        echo $output;
    }

    /**
     * Count how many failed login attempts there have been and return errors if so
     * @param string $username Passed from wordpress, username submitted from login form
     * @return bool true/false
     */
    public function loginFailAction($username) {
        // Update current limit
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }

        $_SESSION['login_attempts']++;
        $this->loginAttempts = $_SESSION['login_attempts'];

        $referrer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF'];
        $referrer = add_query_arg('result', 'failed', $referrer);
        $referrer = add_query_arg('username', $username, $referrer);

        // Standard url redirect after loggin in
        if( !empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin') ) {
            wp_redirect($referrer);
            exit;
        }
    }

 	/**
 	 * Runs all the features of secureWP if enabled
     * @return bool true/false
 	 */
 	public function run() {
        $this->limitLogins();

 		return true;
 	}

 }
