<?php
/**
 * Copyright 2010  Université d'Avignon et des Pays de Vaucluse 
 * email: gpl@univ-avignon.fr
 *
 * This file is part of Filez.
 *
 * Filez is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Filez is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Filez.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Application controller
 */
class Fz_Controller {

    // Most of this attributes are static in order to share data between controllers
    // while forwarding request for example
    protected static $_user = null;
    protected static $_authHandler = null;
    protected static $_mailTransportSet = false;

    /**
     * Check if the current user is authenticated and forward
     * to a login action if not.
     *
     * @param string  $credential
     */
    protected function secure ($credential = null) {
        $this->getAuthHandler ()->secure ();
        $user = $this->getUser();

        // setting user template var
        set ('fz_user', $user);

        if ($user->is_locked) {
            halt (HTTP_FORBIDDEN, __('Your account has been locked. Please contact the FileZ administrator.'));
        }
        if ($credential == 'admin') { // 
            if (! $user->is_admin)
                halt (HTTP_FORBIDDEN, __('This page is secured'));
        }
    }

    /**
     * Return the current user profile
     */
    protected function getUser () {
        $auth = $this->getAuthHandler ();
        $factory = $this->getUserFactory ();
        if (self::$_user === null && $auth->isSecured ()) {
            self::$_user = Fz_Db::getTable('User')->findByUsername ($auth->getUserId ());
            if (! $factory->isInternal ()) {
                if (self::$_user === null)
                    self::$_user = new App_Model_User ();

                // Update fields
                $userData = $factory->findById ($auth->getUserId ());
                self::$_user->username     = $userData['id'];
                self::$_user->email        = $userData['email'];
                self::$_user->firstname    = $userData['firstname'];
                self::$_user->lastname     = $userData['lastname'];
                self::$_user->save (); // will issue an update or insert only if a property changed
            }
        }
        return self::$_user;
    }

    /**
     * Returns the config
     */
    protected function getConfig () {

    }

    /**
     * Initialize the controller
     */
    public function init () {
    }

    /**
     * Set request token in session (secret, created_at)
     * and send token with response as temporary cookie.
     * 
	 * TODO: improve secret algorithm
	 * 
     * @param boolean $delete if true, delete token 
     * @return void
     */
    protected  function setToken() {
        $_SESSION['token'] = array(
            'secret'  => time(),
            'created_at' => time()
        );
        setcookie('token',$this->getTokenSecret());
    }

    /**
     * Returns token secret.
     * 
     * @return string Token secret
     */    
    protected function getTokenSecret() {
        return $_SESSION['token']['secret'];
    }

    /**
     * Returns token creation time. 
     * 
     * @return integer UNIX timestamp
     */
    protected function getTokenCreationTime() {
        return $_SESSION['token']['created_at'];
    }
    
    /**
     * Verify token. (equality, expiration)
     * 
     * @return boolean true if submitted token is valid, else false
     */
    public function verifyToken () {
      if (array_key_exists('token', $_POST) && $_POST['token'] == $this->getTokenSecret()) {
        if ( (time() - $this->getTokenCreationTime()) > fz_config_get('app','token_lifetime', 60)) {
          return false;
        } else {
          return true;
        }
      } else {
        return false;
      }
    }
    
    /**
     * Return an instance of the authentication handler class
     * 
     * @return Fz_Controller_Security_Abstract
     */
    protected function getAuthHandler () {
        if (self::$_authHandler === null) {
            $authClass = fz_config_get ('app', 'auth_handler_class',
                                        'Fz_Controller_Security_Cas');
            self::$_authHandler = new $authClass ();
            self::$_authHandler->setOptions (
                                fz_config_get ('auth_options', null, array ()));
        }
        return self::$_authHandler;
    }

    /**
     * Return an instance of the user factory
     *
     * @return Fz_User_Factory_Abstract
     */
    protected function getUserFactory () {
        // userFactory option is set in ./index.php
        return option ('userFactory');
    }

    /**
     * Tells if the request was made from an xml http request object
     *
     * @return boolean
     */
    protected function isXhrRequest () {
        return (array_key_exists ('HTTP_X_REQUESTED_WITH', $_SERVER)
                      && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * Create an instance of Zend_Mail, set the default transport and the sender
     * info.
     *
     * @return Zend_Mail
     */
    protected function createMail () {
        if (self::$_mailTransportSet === false) {
            $config = fz_config_get ('email');
            $config ['name'] = 'filez';
            if ($config ['host'] == "sendmail")
                $transport = new Zend_Mail_Transport_Sendmail();
            else
                $transport = new Zend_Mail_Transport_Smtp ($config ['host'], $config);
            Zend_Mail::setDefaultTransport ($transport);
            self::$_mailTransportSet = true;
        }
        $mail = new Zend_Mail ('utf-8');
        $mail->setFrom ($config ['from_email'], $config ['from_name']);
        return $mail;
    }

    /**
     * Redirect the user to the previous page
     */
    protected function goBack () {
        redirect ($_SERVER["HTTP_REFERER"]);
    }
    
    /**
     * Return data to the browser with the correct response type (json or html).
     * If the request comes from an iframe (with the is-async GET parameter,
     * the response is embedded inside a textarea to prevent some browsers :
     * quirks (http://www.malsup.com/jquery/form/#file-upload) JQuery Form
     * Plugin will handle the response transparently.
     * 
     * @param array $data
     */
    protected function returnData ($data) {
        if (array_key_exists ('is-async', $_GET) && $_GET ['is-async']) {
            // Notice by July 04, 2011: this is a temporary fix, 
            // to solve the problem with non-working buttons
            return html("<textarea>\n".json_encode ($data)."\n</textarea>",'');
        }
        else {
            flash ('notification', $data ['statusText']);
            redirect_to ('/');
        }
    }

}


