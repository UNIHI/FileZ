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
 * Controller used to do various actions on files (delete, email, download)
 */
class App_Controller_File extends Fz_Controller {
  /**
   * Delete a file.
   * Temporarly disabled, maybe removing forever
   */
  /*
  public function confirmDeleteAction () {
      $this->secure ();
      $file = $this->getFile ();
      $user = $this->getUser ();
      if (! $user->is_admin) $this->checkOwner ($file, $user);
      set ('file', $file);

      return html ('file/confirmDelete.php');
  }
  */

  /**
   * Toggle login requirement
   */
  /*
  public function confirmToggleRequireLoginAction () {
      $this->secure ();
      $file = $this->getFile ();
      $user = $this->getUser ();
      if (! $user->is_admin) $this->checkOwner ($file, $user);
      set ('file', $file);

      return html ('file/confirmToggleRequireLogin.php');
  }
  */

  /**
   * Delete a file
   */
  public function deleteAction () {
    $this->secure ();
    $file = $this->getFile ();
    $user = $this->getUser ();

    if (! $user->is_admin) $this->checkOwner ($file, $user);

    if (!$this->verifyToken()) {
      $result ['status']     = 'error';
      $result ['statusText'] = __('Action expired. Try again.');
    } else {
      $result ['status']     = 'success';
      $result ['statusText'] = __('File deleted.');
      $file->delete();
    }

    $this->setToken();
    $result ['token'] = $this->getTokenSecret();

    if ($this->isXhrRequest())
      return json ($result);
    else {
      flash ('notification', __('File deleted.'));
      $user->is_admin ? redirect_to ('/admin/files') : redirect_to ('/');
    }
  }

  /**
   * Download a file
   */
  public function downloadAction () {
    $file = $this->getFile ();
    $this->checkFileAuthorizations ($file);
    $file->download_count = $file->download_count + 1;
    $file->save ();
    fz_log('', FZ_LOG_DOWNLOAD, array('file_id' => $file->id));
    return $this->sendFile ($file);
  }

  /**
   * Edit a file.
   */
  public function editAction () {
    $this->secure ();
    $file = $this->getFile ();
    $user = $this->getUser ();
    if (! $user->is_admin) $this->checkOwner ($file, $user);

    // Usual checks
    // Computing default values
    $comment =
      array_key_exists ('comment',  $_POST) ? $_POST['comment'] : '';
    $folder = array_key_exists ('folder', $_POST) ? $_POST['folder'] : '';

    // Allow only numbers and letters and convert space to _
    $folder = preg_replace('/[^A-Za-z0-9_ ]/', '', $folder);
    $folder = preg_replace('/ /', '_', $folder);

    // set password
    $file->password = isset ($_POST ['use-password']);
    if ($file->password == true && ! empty ($_POST ['password']))
      $file->setPassword  ($_POST ['password']);
    else 
      $file->password = false;
    
    // remaining fields
    $file->comment = substr ($comment, 0, 199);
    $file->folder  = substr ($folder, 0, 199);
    $file->require_login = isset ($_POST ['require-login']);
    
    // validate start and end dates
    $availableFrom  = 
      array_key_exists ('start-from', $_POST) ? $_POST['start-from'] : null;
    $availableFrom  = 
      new Zend_Date ($availableFrom, Zend_Date::DATE_SHORT);
    $availableUntil  = 
      array_key_exists ('available-until', $_POST) ? $_POST['available-until'] : null;
    $availableUntil  = 
      new Zend_Date ($availableUntil, Zend_Date::DATE_SHORT);
    if ($availableUntil->isEarlier($availableFrom))
      $availableUntil = new Zend_date($availableFrom);
    $lifetimeMax = $file->getAvailableUntil();
    $previousAvailableUntil = $file->getAvailableUntil();
    $lifetimeMaxExtend = fz_config_get('app', 'lifetime_max_extend');
    $lifetimeMaxExtendValueOnly = substr($lifetimeMaxExtend, 0, -1);
    $unit = substr($lifetimeMaxExtend, -1);
    switch($unit) {
      case 'y':
        $lifetimeMax->add($lifetimeMaxExtendValueOnly, Zend_Date::YEAR);
        $previousAvailableUntil->sub($lifetimeMaxExtendValueOnly, 
          Zend_Date::YEAR);
        break;
      case 'm':
        $lifetimeMax->add($lifetimeMaxExtendValueOnly, Zend_Date::MONTH_SHORT);
        $previousAvailableUntil->sub($lifetimeMaxExtendValueOnly, 
          Zend_Date::MONTH_SHORT);
        break;
      case 'd':
        $lifetimeMax->add($lifetimeMaxExtendValueOnly, Zend_Date::DAY_SHORT);
        $previousAvailableUntil->sub($lifetimeMaxExtendValueOnly, 
          Zend_Date::DAY_SHORT);
        break;
    }
    if ($availableUntil->isLater($lifetimeMax))
      $availableUntil = new Zend_Date($lifetimeMax);
    
    // user must be within extend-time before expiration date 
    // to be allowed to extend
    $now = new Zend_Date();
    if ($availableUntil->isLater($file->getAvailableUntil()) 
        && $now->isLater($previousAvailableUntil)) {
      fz_log ('',FZ_LOG_EXTEND, array('file_id' => $file->id));
      $file->setAvailableUntil($availableUntil);
      // Reset notification lock to prevent cron script from notifying again
      // after lifetime extension.
      $file->del_notif_sent = false;
    }
    try {
      $file->save ();
      fz_log ('',FZ_LOG_EDIT, array('file_id' => $file->id));
      if ($this->isXhrRequest())
        return json (array ('status' => 'success'));
      else {
        $response ['status']     = 'success';
        $response ['statusText'] = __('File updated.');
        $response ['folder']     = $file->folder;
        $response ['hash']       = $file->getHash();
        return $this->returnData ($response);
      }
    } catch (Exception $e) {
      fz_log (
        'Can\'t update file "'. $file .'" edited by '.$user['email'],
        FZ_LOG_ERROR);
      fz_log ($e, FZ_LOG_ERROR);
      $response ['status']     = 'error';
      $response ['statusText'] = __('File could not be updated.');
      return $this->returnData ($response);
    }
  }

  /**
   * Share a file url by mail
   */
  public function emailAction () {
    $this->secure ();
    $user = $this->getUser ();
    $file = $this->getFile ();
    if (! $user->is_admin) $this->checkOwner ($file, $user);
    set ('file', $file);

    // Send mails
    $user = $this->getUser ();
    $mail = $this->createMail();
    $subject = __r('[FileZ] "%sender%" wants to share a file with you',
      array ('sender' => $user));
    $msg = __r('email_share_file (%file_name%, %file_url%, %sender%, %msg%)',
      array(
        'file_name' => $file->file_name,
        'file_url'  => $file->getDownloadUrl(),
        'msg'       => $_POST ['msg'],
        'sender'    => $user,
      ));
    $mail->setBodyText ($msg);
    $mail->setSubject  ($subject);
    $mail->setReplyTo  ($user->email, $user);
    $mail->clearFrom();
    $mail->setFrom     ($user->email, $user);

    $emailValidator = new Zend_Validate_EmailAddress();
    $emails = array();
    foreach (explode (' ', $_POST['to']) as $email) {
      $email = trim ($email);
      if (empty ($email))
        continue;

      if ($emailValidator->isValid ($email)) {
        $mail->addBcc ($email);
        $emails[] = $email;
      } else {
        $msg = __r('Email address "%email%" is incorrect, please correct it.',
          array ('email' => $email));
        return $this->returnError ($msg, 'file/email.php');
      }
    }

    try {
      $mail->send ();
      fz_log (implode(',',$emails), FZ_LOG_SHARE_MAIL_SENT,
        array('file_id' => $file->id));
      return $this->returnSuccessOrRedirect ('/');
    }
    catch (Exception $e) {
      fz_log ('Error while sending email: "Share file"', FZ_LOG_ERROR, $e);
      $msg =
        __('An error occured during email submission. Please try again.');
      return $this->returnError ($msg, 'file/email.php');
    }
  }

 /**
   * Share a file url by mail (show email form only)
   */
  public function emailFormAction () {
    $this->secure ();
    $user = $this->getUser ();
    $file = $this->getFile ();
    if (! $user->is_admin) $this->checkOwner ($file, $user);
    set ('file', $file);
    return html ('file/email.php');
  }

   /**
   * List folder content of a user
   */
  public function folderAction() {
    $folder = Fz_Db::getTable('File')->folderExists (
      params ('created_by'), params ('folder'));

    if ($folder === false) {
      halt (NOT_FOUND, __('There are no files in this folder.'));
    }
    set ('files', Fz_Db::getTable ('File')
      ->findByOwnerFolderOrderByUploadDateDesc (
        params ('created_by'),params ('folder')));
    return html ('file/folder.php');
  }



  /**
   * Report a file
   */
  public function reportAction () {
    $file = $this->getFile ();

    if (!$this->verifyToken()) {
      $response ['status']     = 'error';
      $response ['statusText'] =
        __('Failed to report the file. Try again.');
      return $this->returnData ($response);
    }
    
    // Send report
    $mail = $this->createMail();
    $subject = $file->file_name . ' has been reported';

    // Not perfect
    if ($_POST['report-reason'] == __('File is corrupt')) {
      $msg = __r(
      'This is an automatically sent message by FileZ.\n'
      .'Your uploaded file "%file%" is corrupt.\n'
      .'Please check the file\'s integrity and upload it again.',
      array('file'=>$file));
      $mail->addTo($file->getUploader ()->email);
    } else {
      $msg = 'File name: ' . $file->file_name . '\n';
      $msg.= 'Reason: ' . $_POST['report-reason'] . '\n';
      if (isset($_POST['comment'])) {
        $msg.= 'Additional comment: ' . $_POST['comment'] . '\n';
      }
      $mail->addTo(fz_config_get('app', 'admin_email'));
    }

    $mail->setBodyText ($msg);
    $mail->setSubject  ($subject);
    $mail->setReplyTo  (fz_config_get('app', 'admin_email'));
    $mail->clearFrom();
    $mail->setFrom     (fz_config_get('app', 'admin_email'));

    try {
      $mail->send ();
      fz_log (mysql_real_escape_string ($_POST['report-reason']) . ': '
        . mysql_real_escape_string ($_POST['comment']),
        FZ_LOG_REPORT_MAIL_SENT, array('file_id' => $file->id));
      $response ['status']     = 'success';
      $response ['statusText'] = __('File has been reported.');
      return $this->returnData ($response);
    }
    catch (Exception $e) {
      fz_log ('Error while sending email (reporting)', FZ_LOG_ERROR, $e);
      $response ['status']     = 'error';
      $response ['statusText'] =
        __('Failed to report the file. Try again.');
        return $this->returnData ($response);
    }
  }

    /**
   * Display file info and open a download dialog
   */
  public function previewAction () {
    $file = $this->getFile();
    $isOwner = $file->isOwner ($this->getUser ());

    set ('file',            $file);
    set ('isOwner',         $isOwner);
    set ('available',       $file->isAvailable () || $isOwner);
    set ('checkPassword',   !(empty ($file->password) || $isOwner));
    set ('uploader',        $file->getUploader ());

    // Check for access rights (require login)
    if (!(fz_config_get ('app', 'login_requirement', 'privacy') == 'off')) {
      if (fz_config_get('app', 'login_requirement', 'force') == 'force') {
        set ('requireLogin',    1);
      } else {
        set ('requireLogin',    $file->require_login);
      }
      set ('isLoggedIn',      $this->getAuthHandler()->isSecured());
    }
    fz_log('', FZ_LOG_PREVIEW, array('file_id' => $file->id));
    $this->setToken();
    return html ('file/preview.php');
  }
  
  /**
   * Toggle login requirement
   */
  // Design change made it obsolete
  /*
  public function toggleRequireLoginAction () {
    $this->secure ();
    $file = $this->getFile ();
    $user = $this->getUser ();
    if (! $user->is_admin) $this->checkOwner ($file, $user);
    $result = array ();
    if (! (fz_config_get('app', 'login_requirement', 'on') == 'on')) {
      $result ['status']     = 'error';
      $result ['statusText'] =
      __('You are not allowed to toggle login requirement.');
    } else if (!$this->verifyToken()) {
      $result ['status']     = 'error';
      $result ['statusText'] = __('Action expired. Try again.');
    } else {
      $file->require_login = ($file->require_login == 1 ? 0 : 1);
      $status = ($file->require_login ? __('on') : __('off'));
      $file->save();
      $result ['status']     = 'success';
      $result ['statusText'] =
      __r('Login requirement toggled %status% for file %file%',
      array('status' => $status, 'file' => $file->file_name));
      $result ['html']       = partial ('main/_file_row.php',
        array ('file' => $file));
    }
    
    $this->setToken();
    $result ['token'] = $this->getTokenSecret();

    if ($this->isXhrRequest()) {
      return json ($result);
    } else {
      flash (($result ['status'] == 'success' ? 'notification' : 'error'),
        $result ['statusText']);
      $user->is_admin ? redirect_to ('/admin/files') : redirect_to ('/');
    }
  }
  */
 
  /**
   * View an image
   */
  public function viewAction () {
    $file = $this->getFile ();
    $this->checkFileAuthorizations ($file);
    fz_log('', FZ_LOG_VIEW, array('file_id' => $file->id));
    $file->download_count = $file->download_count + 1;
    $file->save ();

    return $this->sendFile ($file, $file->isImage () ? false : true);
  }

  /**
   * Encapsulate an error message, send as xhr if it is or use new page
   * @param string $msg Textual error message
   * @param in case its not HXR, so use this template page
   */
  private function returnError ($msg, $template) {
    if ($this->isXhrRequest ()) {
      return json (array (
        'status' => 'error',
        'statusText' => $msg
      ));
    } else {
      flash_now ('error', $msg);
      return html ($template);
    }
  }

  /**
   * Does return xhr success (if it is an xhr) or only redirects to a given
   * page.
   * @param $url In case it is not an hxr do redirect to this URL
   */
  private function returnSuccessOrRedirect ($url) {
    if ($this->isXhrRequest ()) {
      return json (array ('status' => 'success'));
    } else {
      redirect_to ($url);
    }
  }

  /**
   * Retrieve the requested file from database.
   * If the file isn't found, the action is stopped and a 404 error is
   * returned.
   *
   * @return App_Model_File
   */
  protected function getFile () {
    $file = Fz_Db::getTable('File')->findByHash (params ('file_hash'));
    if ($file === null) {
      halt (NOT_FOUND, __('There is no file for this code'));
    }
    return $file;
  }

  /**
   * Check if the client is authorized to download the file
   * There is no return to be evaluated,
   * instead stop execution with error or redirect to
   * appropriate url (like login page)
   * Note: you should order functions by complexity and cost (simplest first)
   *
   * @param File $file
   */
  protected function checkFileAuthorizations ($file) {
    // Do not proceed if user == owner
    if ($file->isOwner ($this->getUser ()))
      return;

    if (fz_config_get('app', 'disable_locked_user_files')
        && $file->getUploader()->is_locked == 1) {
      halt (HTTP_FORBIDDEN,
      __('File is locked and currently not available for download.'));
    }

    if (! $file->isAvailable ())
      halt (HTTP_FORBIDDEN, __('File is not available for download'));

    if (fz_config_get('app','privacy_mode')
      && $file->require_login == 0 && $file->password == '') {
      halt (HTTP_FORBIDDEN, __(
      'Security restrictions do not allow public access of this file.'
      .' Please contact the file uploader to solve this problem.'));
    }

    // Force login if global login requirement set
    if (
          ( fz_config_get ('app', 'login_requirement', 'on') == 'force'
            && !$this->getAuthHandler()->isSecured()
          )
          ||
          ( !fz_config_get ('app', 'login_requirement', 'on') == 'off'
            &&  $file->require_login
            && !$this->getAuthHandler()->isSecured())
    ) {
      flash ('error',
        __('You have to login before you can access the file') . ': '. $file);
      $this->secure();
    }

    // Password mismatch
    if (! empty ($file->password)
        && ! $file->checkPassword ($_POST['password'])) {
      flash ('error', __('Incorrect password'));
      redirect ('/'.$file->getHash());
    }

    if ($file->isDownloadLimitReached($this->getUser () )) {
      halt (HTTP_FORBIDDEN,
        __('Sorry, download limit reached for this file'));
    }
  }

  /**
   * Send a file through the standart output
   * @param App_Model_File $file      File to send
   */
  protected function sendFile (App_Model_File $file, $forceDownload = true) {
    $mime = file_mime_content_type ($file->getFileName ());
    header('Content-Type: '.$mime);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: '.$file->file_size);

    if ($forceDownload)
      header('Content-Disposition: attachment; filename="'.
        iconv ("UTF-8", "ISO-8859-1", $file->getFileName ()).'"');

    return file_read ($file->getOnDiskLocation ());
  }

  /**
   * Checks if the user is the owner of the file. Stop the request if not.
   *
   * @param App_Model_File $file
   * @param App_Model_User $user
   */
  protected function checkOwner (App_Model_File $file, $user) {
    if ($file->isOwner ($user))
      return;
    halt (HTTP_UNAUTHORIZED, __('You are not the owner of the file'));
  }
}

?>
