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
 * General Controller used for administratives tasks
 */
class App_Controller_Admin extends Fz_Controller {

  public function init () {
    layout ('layout'.DIRECTORY_SEPARATOR.'admin.html.php');
  }

  public function indexAction () {
    $this->secure ('admin');
    set ('numberOfUsers', Fz_Db::getTable ('User')->getNumberOfUsers() );
    set ('numberOfFiles', Fz_Db::getTable ('File')->getNumberOfFiles() );
    set ('totalDiskSpace', Fz_Db::getTable ('File')->getTotalDiskSpace() );
    if ($this->isXhrRequest())
      return partial ('admin/dashboard.php');
    else
      return html ('admin/index.php');
  }

  /**
   * Action called to manage files
   * List files, display stats.
   */
  public function filesAction () {
    $this->setToken();
    $this->secure ('admin');
    
    // check input vars
    if (!array_key_exists('currentPage',$_POST) 
      || !is_int((int)$_POST['currentPage']) || $_POST['currentPage'] <= 0)
      $currentPage = 1;
    else
      $currentPage = $_POST['currentPage'];
    if (array_key_exists('isDeleted', $_COOKIE) && $_COOKIE['isDeleted'] == 'true')
      $isDeleted = true;
    else
      $isDeleted = false;
    
    $files = Fz_Db::getTable ('File')->find($currentPage, $isDeleted);

    if ($this->isXhrRequest()) {
      $err = false;
      $response = '';
      $response['items'] = '';
      foreach ($files as $file) {
        $response['items'] .= 
          partial('admin/_file_row.php', array ('file' => $file));
      }
      if ($err == false) {
        $response ['status'] = 'success';
      } else {
        $response ['status'] = 'error';
        $response ['statusText'] = __('Error while processing data');
      }
      return json ($response);
    } else {
      set ('files', $files);
      set ('numberOfFiles', Fz_Db::getTable ('File')->getNumberOfFiles($isDeleted));
      return html('file/index.php');
    }
  }
  
  /**
   * Evaluation of database information
   */
  public function statisticsAction () {
    $this->secure ('admin');
    return html ('admin/statistics.php');
  }

  /**
   * Action called to clean expired files and send mail to those who will be
   * in the next 2 days. This action is meant to be called from a cron script.
   * It should not respond any output except PHP execution errors. Everything
   * else is logged in 'filez-cron.log' and 'filez-cron-errors.log' files in
   * the configured log directory.
   */
  public function checkFilesAction () {

    // No access via browser, only via PHP-CLI (crontabs, etc.)
    if ( $_SERVER['REMOTE_ADDR'] != fz_config_get ('cron', 'cron_allowed_ip', '') )
    {
	    fz_log ('Unallowed access to checkFiles via browser', FZ_LOG_ERROR);
	    halt (HTTP_BAD_REQUEST, 'You are not allowed to execute this script');
	    return;
    }

    $lastCron = Fz_Db::getTable('Info')->getLastCronTimestamp();
    $freq = fz_config_get ('cron', 'frequency');

    if(strtotime($freq." ".$lastCron) > time())
      return;

    Fz_Db::getTable('Info')->setLastCronTimestamp(date('Y-m-d H:i:s'));

    // Delete files whose lifetime expired
    Fz_Db::getTable('File')->deleteExpiredFiles ();

    // Send mail for files which will be deleted in less than 2 days
    $days = fz_config_get('cron', 'days_before_expiration_mail');
    foreach (Fz_Db::getTable('File')->findFilesToBeDeleted ($days) as $file) {
      // TODO improve the SQL command to retrieve uploader email at the same time
      //      to reduce the # of request made by notifyDeletionByEmail
      if ($file->notify_uploader) {
        $file->del_notif_sent = true;
        $file->save ();
        $this->notifyDeletionByEmail ($file);
      }
    }
  }

  /**
   * Notify the owner of the file passed as parameter that its file is going
   * to be deleted
   *
   * @param App_Model_File $file
   */
  private function notifyDeletionByEmail (App_Model_File $file) {
    try {
      option ('translate')->setLocale(fz_config_get('app','default_locale'));
      option ('locale')->setLocale(fz_config_get('app','default_locale'));
      $mail = $this->createMail();
      $user = $file->getUploader ();
      $subject = __r('[FileZ] Your file "%file_name%" is going to be deleted', array (
        'file_name' => $file->file_name));
      $msg = __r('email_delete_notif (%file_name%, %file_url%, %filez_url%, %available_until%)', array(
        'file_name'       => $file->file_name,
        'file_url'        => $file->getDownloadUrl(),
        'filez_url'       => url_for('/'),
        'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_FULL),
      ));
      $mail->setBodyText ($msg);
      $mail->setSubject  ($subject);
      $mail->addTo ($user->email);
      $mail->send ();
      fz_log ('', FZ_LOG_DELETE_MAIL_SENT, array('file_id' => $file->id));
    }
    catch (Exception $e) {
      fz_log ('Can\'t send email to '.$user->email
        .' file_id:'.$file->id, FZ_LOG_CRON_ERROR);
    }
  }

}
