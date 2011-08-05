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
 * @property int     file_id
 * @property string  ip
 * @property string  username
 * @property string  action
 * @property string  message
 */
class App_Model_Log extends Fz_Db_Table_Row_Abstract {

    protected $_tableClass = 'App_Model_DbTable_Log';

    /**
     * Return the string representation of the log object (file name)
     * @return string
     */
    public function __toString () {
        return 'log entry ';
    }
    
    // Add timestamp
    public function insert() {
        $this->timestamp = time();
        parent::insert();
    }

}
