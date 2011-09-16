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
 * Main controller
 */
class App_Controller_Main extends Fz_Controller {

    public function indexAction () {
        $this->secure ();
        $user = $this->getUser ();
        $freeSpaceLeft = max (0, Fz_Db::getTable('File')->getRemainingSpaceForUser ($user));
        $maxUploadSize = min (
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('upload_max_filesize')),
             Fz_Db::getTable('File')->shorthandSizeToBytes (ini_get ('post_max_size')),
                $freeSpaceLeft);

        $progressMonitor = fz_config_get ('app', 'progress_monitor');
        $progressMonitor = new $progressMonitor ();
        $folders = implode(', ', Fz_Db::getTable('File')->getFolders($user));
        
        set ('upload_id'        , md5 (uniqid (mt_rand (), true)));
        set ('start_from'       , Zend_Date::now ()->get (Zend_Date::DATE_SHORT));
        set ('refresh_rate'     , 1200);
        set ('files'            , Fz_Db::getTable ('File')
                                    ->findFilesByOwnerOrderByUploadDateDesc (
                                    $user));
        set ('use_progress_bar' , $progressMonitor->isInstalled ());
        set ('upload_id_name'   , $progressMonitor->getUploadIdName ());
        set ('free_space_left'  , $freeSpaceLeft);
        set ('max_upload_size'  , $maxUploadSize);
        set ('folders'          , $folders);
        
        $this->setToken();
        
        return html ('main/index.php');
    }

    public function disclaimerAction () {
        return html ('main/disclaimer.php');
    }
}
