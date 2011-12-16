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

define ('UPLOAD_ERR_QUOTA_EXCEEDED', 99);

/**
 * Controller used to upload files and monitor progression
 */
class App_Controller_Upload extends Fz_Controller {

    /**
     * Action called when uploading a file
     * @return string   json if request is made async or html otherwise
     */
    public function startAction () {
        $this->secure ();
        fz_log ('uploading');
        fz_log ('uploading', FZ_LOG_DEBUG, $_FILES);
        $response = array (); // returned data
        $availableFrom  = array_key_exists ('start-from', $_POST) ? $_POST['start-from'] : null;
        $availableFrom  = new Zend_Date ($availableFrom, Zend_Date::DATE_SHORT);
        $testDate = new Zend_Date();
        // check if request exceed php.ini post_max_size
        if ($_SERVER ['CONTENT_LENGTH'] > $this->shorthandSizeToBytes (
                                                   ini_get ('post_max_size'))) {
            fz_log ('upload error (POST request > post_max_size)', FZ_LOG_ERROR);
            return $this->onFileUploadError (UPLOAD_ERR_INI_SIZE);
        }
        // User accepted agreement (if enabled in options)?
        else if (fz_config_get('app', 'require_user_agreement', true)
            && !isset ($_POST['user-agreement'])) {
            $response ['status']     = 'error';
            $response ['statusText'] = __('You have to accept the user agreement.');
            return $this->returnData ($response);

        }
        // user has to require login or a password for his file 
        // if filez setting privacy_mode is true
        else if (fz_config_get('app', 'privacy_mode', false)
            && !isset ($_POST['require-login'])
            && !isset ($_POST['use-password'])) {
            $response ['status']     = 'error';
            $response ['statusText'] = __('You have to protect your file with either login requirement or a password.');
            return $this->returnData ($response);

        }
        // date in the past?
        else if ($availableFrom->isEarlier($testDate, Zend_Date::DATE_SHORT)) {
            $response ['status']     = 'error';
            $response ['statusText'] = __('You have entered a date in the past. Please use the date picker to select a date.');
            return $this->returnData ($response);
        }
        else if ($_FILES ['file']['error'] === UPLOAD_ERR_OK) {
            if ($this->checkQuota ($_FILES ['file'])) // Check user quota first
                return $this->onFileUploadError (UPLOAD_ERR_QUOTA_EXCEEDED);

            // Still no error ? we can move the file to its final destination
            $file = $this->saveFile ();
            if ($file !== null) {
                $this->sendFileUploadedMail ($file);
                return $this->onFileUploadSuccess ($file);

            } else { // Errors happened while saving or moving the uploaded file
                return $this->onFileUploadError ();
            }
        } else { // Errors happened during file upload
            return $this->onFileUploadError ($_FILES ['file']['error']);
        }
    }

    /**
     * Action called from the javascript to request file upload progress
     * @return string (json)
     */
    public function getProgressAction () {
        $this->secure ();

        $uploadId = params ('upload_id');
        if (! $uploadId)
            halt (HTTP_BAD_REQUEST, 'A file id must be specified');

        $progressMonitor = fz_config_get ('app', 'progress_monitor');
        $progressMonitor = new $progressMonitor ();

        if (! $progressMonitor->isInstalled ())
            halt (HTTP_NOT_IMPLEMENTED, 'Your system is not configured for'.get_class ($progressMonitor));
            
        $progress = $progressMonitor->getProgress ($uploadId);

        if (! is_array ($progress))
            halt (NOT_FOUND);

        return json ($progress);
    }



    /**
     * Create a new File object from posted values and store it into the database.
     *
     * @param array $post       ~= $_POST
     * @param array $files      ~= $_FILES
     * @return App_Model_File
     */
    private function saveFile () {
        // Computing default values

        $comment = array_key_exists ('comment',  $_POST) ? $_POST['comment'] : '';
        $folder = array_key_exists ('folder', $_POST) ? $_POST['folder'] : '';
        
        // Allow only numbers and letters and convert space to _
        $folder = preg_replace('/[^A-Za-z0-9_ ]/', '', $folder);
        $folder = preg_replace('/ /', '_', $folder);
        
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
        $lifetimeMax = new Zend_Date($availabeFrom);
        $lifetimeMaxSetting = fz_config_get('app', 'lifetime_max');
        $unit = substr($lifetimeMaxSetting, -1);
        switch($unit) {
          case 'y':
            $lifetimeMax->add(substr($lifetimeMaxSetting, 0, -1), 
              Zend_Date::YEAR);
            break;
          case 'm':
            $lifetimeMax->add(substr($lifetimeMaxSetting, 0, -1), 
            Zend_Date::MONTH_SHORT);
            break;
          case 'd':
            $lifetimeMax->add(substr($lifetimeMaxSetting, 0, -1), 
            Zend_Date::DAY_SHORT);
            break;
        }
        if ($availableUntil->isLater($lifetimeMax))
          $availableUntil = new Zend_Date($lifetimeMax);

        $user = $this->getUser ();
        
        // Storing values
        $file = new App_Model_File ();
        $file->setFileInfo      ($_FILES ['file']);
        $file->setUploader      ($user);
        $file->setCreatedAt     (new Zend_Date());
        $file->comment          = substr ($comment, 0, 199);
        $file->folder           = substr ($folder, 0, 199);
        $file->setAvailableFrom ($availableFrom);
        $file->setAvailableUntil($availableUntil);
        // Check for notification enforcement
        if (fz_config_get ('app', 'force_notification', false) == true) {
            $file->notify_uploader  = true;
        } else {
            $file->notify_uploader  =
              (isset ($_POST['email-notifications'])?1:0);
        }
        
        
        // Check for login requirement enforcement 
        if (fz_config_get ('app', 'login_requirement', 'force') == 'force') {
            $file->require_login = 1;
        } else if (fz_config_get ('app', 'login_requirement', 'on') == 'on') {
            $file->require_login    = isset ($_POST['require-login']);
        }
        
        // set password
        $file->password = isset ($_POST ['use-password']);
        if ($file->password == true && ! empty ($_POST ['password']))
            $file->setPassword  ($_POST ['password']);

        try {
            $file->save ();

            if ($file->moveUploadedFile ($_FILES ['file'])) {
                //fz_log ('Saved "'.$file->file_name.'"['.$file->id.'] uploaded by '.$user);
                fz_log ('',FZ_LOG_UPLOAD, 
                    array('file_id' => $file->id));
                return $file;
            }
            else {
                $file->delete ();
                return null;
            }
        } catch (Exception $e) {
            fz_log ('Can\'t save file "'.$_FILES ['file']['name'].'" uploaded by '.$user, FZ_LOG_ERROR);
            fz_log ($e, FZ_LOG_ERROR);
            return null;
        }
    }

    /**
     * Notify the user by email that its file has been uploaded
     *
     * @param App_Model_File $file
     */
    private function sendFileUploadedMail (App_Model_File $file) {
        if (! $file->notify_uploader)
            return;

        $user = $this->getUser ();
        $subject = __r('[FileZ] "%file_name%" uploaded successfuly',
            array('file_name' => $file->file_name));
        $msg = __r('email_upload_success (%file_name%, %file_url%, %filez_url%, %available_from%, %available_until%)',
            array('file_name' => $file->file_name,
                  'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
                  'available_until' => $file->getAvailableUntil()->toString (Zend_Date::DATE_LONG),
                  'file_url'  => $file->getDownloadUrl(),
                  'filez_url' => fz_url_for ('/', (fz_config_get ('app', 'https') == 'always'))
            )
        );

        $mail = $this->createMail();
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);
        $mail->addTo ($user->email, $user);

        try {
            $mail->send ();
            fz_log ('', FZ_LOG_UPLOAD_MAIL_SENT, array('file_id' => $file->id));
        }
        catch (Exception $e) {
            fz_log ('Can\'t send email "File Uploaded" : '.$e, FZ_LOG_ERROR);
        }
    }

    /**
     * Transform a size in the shorthand format ('K', 'M', 'G') to bytes
     *
     * @param   string      $size
     * @return  integer
     */
    private function shorthandSizeToBytes ($size) {
        $size = str_replace (' ', '', $size);
        switch(strtolower($size[strlen($size)-1])) {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }
        return floatval ($size);
    }

    /**
     * Check if the user will exceed its quota if if he upload the file $file
     *
     * @param array $file   File element from $_FILES
     * @return boolean      true if he will exceed, false else
     */
    private function checkQuota ($file) {
        $fileSize = $_FILES['file']['size'];
        $freeSpace = Fz_Db::getTable('File')->getRemainingSpaceForUser ($this->getUser());
        return ($fileSize > $freeSpace);
    }

    /**
     * Function called on file upload success, a default message is returned
     * to the user.
     *
     * @param App_Model_File $file
     */
    private function onFileUploadSuccess (App_Model_File $file) {
        $user                    = $this->getUser();
        $response ['status']     = 'success';
        $response ['statusText'] = __('The file was successfuly uploaded');
        $response ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        $response ['fileHash']   = $file->getHash();
        $response ['disk_usage'] = bytesToShorthand (max (0,
                     Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($user)));
        $this->setToken();
        $result ['token'] = $this->getTokenSecret();
        return $this->returnData ($response);
    }

    /**
     * Function called on file upload error. A message corresponding to the error
     * code passed as parameter is return to the user. Error codes come from
     * $_FILES['userfile']['error'] plus a custom error code called
     * 'UPLOAD_ERR_QUOTA_EXCEEDED'
     *
     * @param integer $errorCode
     */
    private function onFileUploadError ($errorCode = null) {
        $response ['status']     = 'error';
        $response ['statusText'] = __('An error occured while uploading the file.').' ';

        if ($errorCode === null)
            return $this->returnData ($response);

        switch ($errorCode) {
            case UPLOAD_ERR_NO_TMP_DIR:
                fz_log ('upload error (Missing a temporary folder)', FZ_LOG_ERROR);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                fz_log ('upload error (Failed to write file to disk)', FZ_LOG_ERROR);
                break;

            // These errors come from the client side, let him know what's wrong
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $response ['statusText'] .=
                    __('The uploaded file exceeds the max file size.')
                    .' : ('.ini_get ('upload_max_filesize').')';
                break;
            case UPLOAD_ERR_PARTIAL:
                $response ['statusText'] .=
                     __('The uploaded file was only partially uploaded.');
                break;
            case UPLOAD_ERR_NO_FILE:
                $response ['statusText'] .=
                     __('No file was uploaded.');
                break;
            case UPLOAD_ERR_QUOTA_EXCEEDED:
                $response ['statusText'] .= __r('You exceeded your disk space quota (%space%).',
                    array ('space' => fz_config_get ('app', 'user_quota')));
        }
        return $this->returnData ($response);
    }
}

