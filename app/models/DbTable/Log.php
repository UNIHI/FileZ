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
 * Table to log download activity
 */


/**
 * @property int $file_id
 * @property string  $ip
 * @property string  username
 * @property int  $timestamp
 */
class App_Model_DbTable_Log extends Fz_Db_Table_Abstract {

    protected $_rowClass = 'App_Model_Log';
	protected $_name = 'fz_log';
    
    protected $_columns = array (
        'file_id',
        'ip',
    	'username',
    	'timestamp',
    	'action'
    );
    
    /**
     * Count file downloads for given file in given time interval.
     * @param $data accept array with 3 elements: file id, timestamp, timestamp
     * @return amount of Downloads
     */
    public function countFileDownloads (array $data) {
		//TODO: Errorhandling 
		//TODO: Count all Users? 
        $sql  = 'SELECT count(`id`) FROM `'.$this->getTableName ()
        .'` WHERE `file_id` = ? '
        .' AND action = \'' . FZ_LOG_DOWNLOAD
        .'\' AND `timestamp` BETWEEN ? AND ?';
        return $this->findOneBySQL ($sql, $data);
    }

     
     

}
?>
