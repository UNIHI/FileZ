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
        set ('users', Fz_Db::getTable ('User')->findAll ()); // TODO paginate
        return html ('user/index.php');
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
        $user->setUsername  ($_POST ['username']);
        $user->setPassword  ($_POST ['password']);
        $user->setFirstname ($_POST ['firstname']);
        $user->setLastname  ($_POST ['lastname']);
        $user->setIsAdmin   ($_POST ['is_admin'] == 'on');
        $user->setEmail     ($_POST ['email']);

        // TODO improve form check
        // for example : test if the email and the username are not already in DB
        if(filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL) && null!=$_POST ['username'] && (3 <= strlen($_POST['password'])) ){
            $user->save ();
            return redirect_to ('/admin/users');
        }
        else {
            flash_now ('error', "error: email not valid or no username or password too short.");
            return $this->createAction ();
        }
    }

    /**
     * Action called to update values of an existing user.
     */
    public function updateAction () {
        // TODO prevent CSRF

        $this->secure ('admin');
        $user = Fz_Db::getTable ('User')->findById (params ('id'));
        $user->setUsername  ($_POST ['username']);
        if ( 0 < strlen($_POST['password']) ) {
          $user->setPassword  ($_POST ['password']);
        }
        $user->setFirstname ($_POST ['firstname']);
        $user->setLastname  ($_POST ['lastname']);
        $user->setIsAdmin   ($_POST ['is_admin'] == 'on');
        $user->setEmail     ($_POST ['email']);
        // TODO improve form check
        // for example : test if the email and the username are not already in DB
        if(filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL) && null!=$_POST ['username'] ) {
            $user->save ();
            return redirect_to ('/admin/users');
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
        return html ('user/create.php');
    }

    /**
     * Action called to edit a user
     */
    public function editAction () {
        $this->secure ('admin');
        set ('user', Fz_Db::getTable ('User')->findById (params ('id')));
        return html ('user/edit.php');
    }

    /**
     * Action called to delete a user
     */
    public function deleteAction () {
        // TODO prevent CSRF

        $this->secure ('admin');
        $user = Fz_Db::getTable ('User')->findById (params ('id'));
        if($user) 
            $user->delete();

        return redirect_to ('/admin/users');
    }
}
