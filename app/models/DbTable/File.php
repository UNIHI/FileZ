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

class App_Model_DbTable_File extends Fz_Db_Table_Abstract {

  protected $_rowClass = 'App_Model_File';
  protected $_name = 'fz_file';
  protected $_columns = array (
    'del_notif_sent',
    'file_name',
    'file_size',
    'nom_physique',
    'available_from',
    'available_until',
    'download_count',
    'notify_uploader',
    'created_by',
    'created_at',
    'extends_count',
    'comment',
    'password',
    'require_login',
    'downloadLimit',
    'intervalCount',
    'intervalType',
    'reported',
    'folder'
  );

  /**
   * Transform a hash in base 36 to an integer
   *
   * @param string $hash
   * @return integer
   */
  public function hashToId ($hash) {
    return base_convert ($hash, 36, 10);
  }

  /**
   * Transforme an integer into a hash in base 36
   *
   * @param integer $id
   * @return string   hash code
   */
  public function idToHash ($id) {
    return base_convert ($id, 10, 36);
  }


  /**
   * Generate a random code in base 36.
   *
   * @param  integer  $min    Minimum size of the hash
   * @param  integer  $max    Maximum size of the hash
   * @return string           Hash code
   */
  protected function generateRandomHash ($min, $max) {
    $size = mt_rand ($min, min ($max, 10));
    $hash = '';
    for ($i = 0; $i < $size; ++$i) {
      $hash .= base_convert (mt_rand (0, 35), 10, 36);
    }
    return $hash;
  }

  /**
   * Return a free slot id in the fz_file table
   *
   * @return integer
   */
  public function getFreeId () {
    $min = fz_config_get('app', 'min_hash_size');
    $max = fz_config_get('app', 'max_hash_size');
    $id = null;
    do {
      $id = base_convert ($this->generateRandomHash ($min, $max), 36, 10);
    } while ($this->rowExists ($id));
    return $id;
  }

  /**
   * Find a file by its hash code
   *
   * @param string $hash
   * @return App_Model_File
   */
  public function findByHash ($hash) {
    return $this->findById ($this->hashToId ($hash));
  }

  /**
   * Find a file by its id
   * which is NOT marked as deleted
   *
   * @param   int     $id
   * @return  Fz_Table_Row_Abstrat
   */
  public function findById ($id) {
    $sql = "SELECT * FROM ".$this->getTableName ()
    .' WHERE id = ? AND isDeleted = 0';
    return $this->findOneBySQL ($sql, array ($id));
  }

  /**
   * Retrieve all rows of the current table
   *
   * @return array  Array of Fz_Table_Row_Abstrat
   */
  public function findNotDeleted () {
    $sql = "SELECT * FROM ".$this->getTableName () . " WHERE isDeleted = 0";
    return Fz_Db::findObjectsBySQL ($sql, $this->getRowClass ());
  }

  /**
   * Return all file owned by $uid which are available (not deleted)
   *
   * @param App_Model_User $user
   * @param boolean $expired only count expired files
   * @return array of App_Model_File
   */
  public function findFilesByOwnerOrderByUploadDateDesc (
    $user, $expired = false) {
    $sql = 'SELECT * FROM '.$this->getTableName ()
      .' WHERE created_by=:id '
      .' AND  available_until '.($expired?'<':'>='). ' CURRENT_DATE() '
      . ( $expired ? '' : ' AND isDeleted = 0 ' )
      .' ORDER BY created_at DESC';
    return $this->findBySql ($sql, array (':id' => $user->id));
  }

  /**
   * Return all files owned by $created_by which are available (not deleted)
   * and located in the specified folder
   *
   * @param string $created_by
   * @param string $folder
   * @return array of App_Model_File
   */
  //TODO: Check for further file availability
  public function findByOwnerFolderOrderByUploadDateDesc ($created_by, $folder) {
    $sql = 'SELECT * FROM '.$this->getTableName ()
      .' WHERE created_by=:created_by '
      .' AND folder=:folder '
      .' AND  available_until >= CURRENT_DATE() '
      .' ORDER BY created_at DESC';
    return $this->findBySql ($sql,
      array (':created_by' => $created_by, ':folder' => $folder));
  }

  /**
   * Delete files whose lifetime expired
   */
  public function deleteExpiredFiles () {
    $select = 'SELECT * FROM '.$this->getTableName ();
    //$where  = ' WHERE available_until<CURRENT_DATE()';
    $where  = ' WHERE available_until<CURRENT_DATE() AND isDeleted = 0';
    foreach ($this->findBySql ($select.$where) as $file) {
      if ($file->deleteFromDisk () === true) {
        fz_log ('Deleted file "'.$file->getOnDiskLocation ().'"',
                    FZ_LOG_CRON);
      } else {
        fz_log ('Failed deleting file "'.$file->getOnDiskLocation ().'"',
                    FZ_LOG_CRON_ERROR);
      }
    }
    //Fz_Db::getConnection()->exec ('DELETE FROM '.$this->getTableName ().$where);
    // Do not delete table row, instead mark file as deleted.
    Fz_Db::getConnection()->exec ('UPDATE '.$this->getTableName ()
      . ' SET isDeleted = 1 ' . $where);
  }

  /**
   * Return files which will be deleted within X days and where uploader wants
   * to be notified but hasn't been yet
   *
   * @param integer   $days   Number of days before being deleted
   * @return App_Model_File
   */
  public function findFilesToBeDeleted ($days = 2) {
    $sql = 'SELECT * FROM '.$this->getTableName ()
      .' WHERE available_until BETWEEN CURRENT_DATE() '
      .'AND DATE_ADD(CURRENT_DATE(), INTERVAL '.$days.' DAY) '
      .'AND del_notif_sent=0 AND notify_uploader=1';
    return $this->findBySql ($sql);
  }

  /**
   * Return disk space used by someone
   *
   * @param App_Model_User    $user   User
   * @return float            Size in bytes
   */
  public function getTotalDiskSpaceByUser ($user, $includeExpired = false) {
    $result = Fz_Db::getConnection()
      ->prepare ('SELECT sum(file_size) FROM `'
        .$this->getTableName ()
        .'` WHERE created_by = ?'
        .( $includeExpired ? ' AND  available_until >= CURRENT_DATE() ' : '' )
        .' AND isDeleted = 0');
    $result->execute (array ($user->id));
    return (float) $result->fetchColumn ();
  }

  /**
   * Return remaining disk space available for user $user
   *
   * @param App_Model_User    $user   User data
   * @return float            Size in bytes or string if $shorthand = true
   */
  public function getRemainingSpaceForUser ($user) {
    return ($this->shorthandSizeToBytes (fz_config_get ('app', 'user_quota'))
      - $this->getTotalDiskSpaceByUser ($user));
  }

  /**
   * Transform a size in the shorthand format ('K', 'M', 'G') to bytes
   *
   * @param   string      $size
   * @return  float
   */
  public function shorthandSizeToBytes ($size) {
    $size = str_replace (' ', '', $size);
    switch (strtolower ($size [strlen($size) - 1])) {
      case 'g': $size *= 1024;
      case 'm': $size *= 1024;
      case 'k': $size *= 1024;
    }
    return floatval ($size);
  }

  /**
   * Return list of already used folders
   *
   * @param App_Model_User $user
   * @return array            List of folders
   */
  public function getFolders ($user) {
    $folders = array();
    $result = Fz_Db::getConnection()
      ->prepare ('SELECT DISTINCT folder FROM `'
        .$this->getTableName ()
        .'` WHERE created_by = ? '
        .' AND folder <> "" '
        .' AND folder IS NOT NULL '
        .' AND isDeleted != 1 '
        .' AND available_until >= CURRENT_DATE() '
        .' ORDER BY folder ASC');
    $result->execute (array ($user->id));
    while ($folder = $result->fetchColumn()) { $folders[] = $folder; }
    return $folders;
  }

  /**
   * Return true or false wheter a row of <created_by,folder> exists or not.
   *
   * @param   string      $created_by
   * @param   string      $folder
   * @return  boolean
   */
  public function folderExists ($created_by, $folder) {
    $db = Fz_Db::getConnection();
    $sql  = 'SELECT folder FROM `'.$this->getTableName ().'` '
            .'WHERE created_by = ? AND '
            .'folder = ?';
    $stmt = $db->prepare ($sql);
    $stmt->execute (array ($created_by, $folder));

    return $stmt->fetchColumn () === false ? false : true;
  }
}


