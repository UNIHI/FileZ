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

class App_Model_DbTable_User extends Fz_Db_Table_Abstract {

  protected $_rowClass = 'App_Model_User';
  protected $_name = 'fz_user';
  protected $_columns = array (
    'id',
    'username',
    'password',
    'salt',
    'firstname',
    'lastname',
    'email',
    'is_admin',
    'created_at',
  );

  /**
   * Retrieve a user by its username
   *
   * @param string $username
   * @return App_Model_User or null if not found
   */
  public function findByUsername ($username) {
    $sql = 'SELECT * FROM '.$this->getTableName ().' WHERE username = ?';
    return $this->findOneBySQL ($sql, $username);
  }

  /**
  * Retrieve a user by its email
  *
  * @param string $email
  * @return App_Model_User or null if not found
  */
  public function findByEmail ($email) {
    $sql = 'SELECT * FROM '.$this->getTableName ().' WHERE email = ?';
    return $this->findOneBySQL ($sql, $email);
  }

  /**
  * Count the number of users
  *
  * @return integer number of users
  */
  public function getNumberOfUsers ($usersNameFilter='') {
    $nameCondition = '';
    if ($usersNameFilter != '') {
      $usersNameFilter = preg_replace('/[^A-Za-z0-9_ ]/', '', $usersNameFilter);
      $nameCondition = "AND (firstname LIKE '%" . $usersNameFilter . "%'"
        . "OR lastname LIKE '%" . $usersNameFilter . "%')";
    }
    $sql = 'SELECT COUNT(*) AS count FROM '.$this->getTableName ()
      . ' WHERE 1=1 '
      . $nameCondition;
    $res = Fz_Db::findAssocBySQL($sql);
    return $res[0]['count'];
  }
  
  /**
   * Retrieve rows of the current table under certain conditions
   * @param $currentPage integer the current page to fetch items from
   * @param $isDeleted boolean include deleted files, default: false
   * @param $filesOrder string order result by $filesOrder 
   * {for legal values see below}
   * @param $filesOrderDirection string direction of ordering
   * @return array  Array of Fz_Table_Row_Abstrat
   */
  public function find ($currentPage, $usersOrder, $usersOrderDirection, 
    $usersNameFilter) {
    // only allow minimal set of characters for search
    $nameCondition = '';
    if ($usersNameFilter != '') {
        $usersNameFilter = preg_replace('/[^A-Za-z0-9_ ]/', '', $usersNameFilter);
        $nameCondition = "AND (firstname LIKE '%" . $usersNameFilter . "%'"
          . "OR lastname LIKE '%" . $usersNameFilter . "%')";
    }
    
    $orderBy = array (
      'name' => 'lastname',
      'email' => 'email', 
      'role' => 'is_admin'
    );
    if (array_key_exists($usersOrder, $orderBy)) {
      $order = " ORDER BY " . $orderBy[$usersOrder] . ' ';
      if ($usersOrderDirection == 'asc')
        $order .= 'ASC ';
      else
        $order .= 'DESC ';
    } else {
      $order = '';
    }

    $itemsPerPage = (int)(fz_config_get('app','items_per_page'));
    if (!is_int($itemsPerPage))
      $itemsPerPage = 10;
    $currentPage = ($currentPage-1) * $itemsPerPage;
    $limit = ' LIMIT ' . $currentPage .',' . $itemsPerPage;

    $sql = "SELECT * FROM ".$this->getTableName () 
      . " WHERE 1=1 $nameCondition $order $limit";
    return Fz_Db::findObjectsBySQL ($sql, $this->getRowClass ());
  }

}



