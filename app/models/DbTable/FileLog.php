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
class App_Model_DbTable_FileLog extends Fz_Db_Table_Abstract {

	protected $_name = 'fz_fileLog';
    protected $_columns = array (
        '$file_id',
        'ip',
    	'username',
    	'timestamp',
    );



    /**
     * Method used to insert fileinformation into fz_fileLog table.
     *
     * @param int $file_id
     */
    public function insert ($file_id, $userId) {
    	$db   = Fz_Db::getConnection();
    	// Logging-Settings
    	$userId = (fz_config_get ('app', 'log_username')) ? $userId : "Not logged"; 
        $ip = (fz_config_get ('app', 'log_ip')) ? $_SERVER['REMOTE_ADDR'] : "Not logged";
        //TODO logging Browser Version
        
        $sql  = 'INSERT INTO `'.$this->getTableName ().'` (`file_id`, `ip`, `username`, `timestamp`) VALUES (:file_id, :ip, :username, :timestamp)';
        $stmt = $db->prepare ($sql);
        return $stmt->execute (array (
            ':file_id' => $file_id,
            ':ip' => $ip,
        	':username' => $userId,
        	':timestamp' => time(),
        )); 
    }
    
public function countFile ($dat) {
		//TODO Errorhandling 
		// TODO Count all Users? 
     	$db   = Fz_Db::getConnection();
        $sql  = 'SELECT count(`id`) FROM `'.$this->getTableName ().'` WHERE `file_id` = ? AND `timestamp` BETWEEN ? AND ?';
        $stmt = $db->prepare ($sql);
        $stmt->execute ($dat);
        $countResult = $stmt->fetch();
        return $countResult['0'];
    }

     
     

}
?>
