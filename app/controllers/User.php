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
 * Controller used for user administration
 */
class App_Controller_User extends Fz_Controller {

  public function init () {
    layout ('layout'.DIRECTORY_SEPARATOR.'admin.html.php');
  }

  /**
   * Action list users
   * List users.
   */
  public function indexAction () {
    $this->secure ('admin');
    $this->setToken();
    
    // check input vars
    if (!array_key_exists('currentPage',$_POST) 
      || !is_int((int)$_POST['currentPage']) || $_POST['currentPage'] <= 0)
      $currentPage = 1;
    else
      $currentPage = $_POST['currentPage'];
    
    $usersOrder = 'name';
    if (array_key_exists('usersOrder',$_COOKIE))
      $usersOrder = $_COOKIE['usersOrder'];

    $usersOrderDirection = 'asc';
    if (array_key_exists('usersOrderDirection',$_COOKIE))
      $usersOrderDirection = $_COOKIE['usersOrderDirection'];

    $usersNameFilter = '';
    if (array_key_exists('usersNameFilter',$_COOKIE))
      $usersNameFilter = $_COOKIE['usersNameFilter'];
    
    $users = Fz_Db::getTable ('User')->find($currentPage, $usersOrder, 
      $usersOrderDirection, $usersNameFilter);
        
    if ($this->isXhrRequest()) {
      $err = false;
      $response = '';
      $response['items'] = '';
      foreach ($users as $user_item) {
        $response['items'] .= 
          partial('user/_user_row.php', array ('user_item' => $user_item));
      }
      if ($err == false) {
        $response ['status'] = 'success';
      } else {
        $response ['status'] = 'error';
        $response ['statusText'] = __('Error while processing data');
      }
      return json ($response);
    } else {
      set ('isInternal', $this->getUserFactory ()->isInternal ());
      set ('numberOfUsers', 
        Fz_Db::getTable ('User')->getNumberOfUsers($usersNameFilter));
      set ('users', $users);
      return html('user/index.php');
    }
  }

  /**
   * Action called to display user details
   */
  public function showAction () {
    $this->secure ('admin');
    set ('user', Fz_Db::getTable ('User')->findById (params ('id')));
    return html ('user/show.php');
  }

  /**
   * Action called to post values of a new user.
   */
  public function postnewAction () {
    // TODO prevent CSRF

    $this->secure ('admin');
    $user = new App_Model_User ();
    $user->setUsername  (array_key_exists('username',$_POST)?$_POST ['username']:'');
    $user->setPassword  (array_key_exists('password',$_POST)?$_POST ['password']:'');
    $user->setFirstname (array_key_exists('firstname',$_POST)?$_POST ['firstname']:'');
    $user->setLastname  (array_key_exists('lastname',$_POST)?$_POST ['lastname']:'');
    $isAdmin = array_key_exists('is_admin',$_POST)?$_POST ['is_admin']:0;
    $isLocked = array_key_exists('is_locked',$_POST)?$_POST ['is_locked']:0;
    $user->setIsAdmin   ($isAdmin == 'on' ? 1 : 0);
    $user->setIsLocked  ($isLocked == 'on' ? 1 : 0);
    $user->setEmail     (array_key_exists('email',$_POST)?$_POST ['email']:'');

    // TODO improve form check
    // for example : test if the email and the username are not already in DB
    if(filter_var($user->email, FILTER_VALIDATE_EMAIL) && null!=$_POST ['username'] && (3 <= strlen($_POST['password'])) ){
      $user->save ();
      return redirect_to ('/admin');
    }
    else {
      flash_now ('error', "error: email not valid or no username or password too short.");
      if ($this->isXhrRequest())
        return $this->createAction ();
      else
        return redirect_to ('/admin');
    }
  }

  /**
   * Action called to update values of an existing user.
   */
  public function updateAction () {
    $this->secure ('admin');
    $user = Fz_Db::getTable ('User')->findById (params ('id'));
    $user->setIsLocked   ($_POST ['is_locked'] == 'on' ? 1 : 0);
    if ($_POST ['is_locked'] == 'on') {
      // only alphanumerical input allowed + _
      $lockReason  = preg_replace('/[^A-Za-z0-9_ ]/', '',
        $_POST ['lock_reason'] );
      $user->setLockReason ($lockReason);
    } else {
      $user->setLockReason ('');
    }

    // If not internal database, firstname, lastname, 
    // username, password or email cannot be changed, so skip it
    if ($this->getUserFactory ()->isInternal () == false) {
      $user->save ();
      return redirect_to ('/admin');
    }

    // TODO prevent CSRF
    $user->setUsername  ($_POST ['username']);
    if ( 0 < strlen($_POST['password']) ) {
      $user->setPassword  ($_POST ['password']);
    }
    $user->setFirstname ($_POST ['firstname']);
    $user->setLastname  ($_POST ['lastname']);
    $user->setEmail     ($_POST ['email']);
    // TODO improve form check
    // for example : test if the email and the username are not already in DB
    if(filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL) && null!=$_POST ['username'] ) {
      $user->save ();
      return redirect_to ('/admin');
    }
    else {
      flash_now ('error', "error: email not valid or no username.");
      return $this->editAction ();
    }
  }



  /**
   * Action called to create a new user
   */
  public function createAction () {
    $this->secure ('admin');
    if ($this->getUserFactory ()->isInternal () == false) {
      flash_now('error',
      "error: cannot create new users while using external user database");
      return $this->indexAction ();
    } else {
      return html ('user/create.php');
    }
  }

  /**
   * Action called to edit a user
   */
  public function editAction () {
    $this->secure ('admin');
    set ('user', Fz_Db::getTable ('User')->findById (params ('id')));
    set ('isInternal', $this->getUserFactory ()->isInternal ());
    return html('user/edit.php');
  }

  /**
   * Action called to delete a user
   */
  public function deleteAction () {
    // TODO prevent CSRF
    $this->secure ('admin');
    if ($this->getUserFactory ()->isInternal () == false) {
      flash_now('error',
      "error: cannot delete users while using external user database");
      return $this->indexAction();
    } else {
      $user = Fz_Db::getTable ('User')->findById (params ('id'));
      if($user)
        $user->delete();
    }
    return redirect_to ('/admin');
  }
}
