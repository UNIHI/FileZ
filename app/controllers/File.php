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
        if (! ( fz_config_get ('app', 'login_requirement', 'privacy') == 'off' ) ) {
            if (fz_config_get ('app', 'login_requirement', 'force') == 'force') {
                set ('requireLogin',    1);
            } else {
                set ('requireLogin',    $file->require_login);
            }
            set ('isLoggedIn',      $this->getAuthHandler()->isSecured());
        }
        return html ('file/preview.php');
    }

    /**
     * Download a file
     */
    public function downloadAction () {
        $file = $this->getFile ();
        $this->checkFileAuthorizations ($file);
        // logging information
       	$this->logging(); 
        $file->download_count = $file->download_count + 1;
        $file->save ();

        return $this->sendFile ($file);
    }


    /**
     * View an image
     */
    public function viewAction () {
        $file = $this->getFile ();
        $this->checkFileAuthorizations ($file);
         // logging information
       	$this->logging(); 
        
        $file->download_count = $file->download_count + 1;
        $file->save ();

        return $this->sendFile ($file, $file->isImage () ? false : true);
    }

    /**
     * Extend lifetime of a file
     */
    public function extendAction () {
        $file = $this->getFile ();

        $result = array ();
        if ($file->extends_count < fz_config_get ('app', 'max_extend_count')) {
            $file->extendLifetime ();
            $file->save ();
            $result ['status']     = 'success';
            $result ['statusText'] = __('Lifetime extended');
            $result ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        } else {
            $result ['status']     = 'error';
            $result ['statusText'] = __r('You can\'t extend a file lifetime more than %x% times',
                                    array ('x' => fz_config_get ('app', 'max_extend_count')));
        }

        if ($this->isXhrRequest()) {
            return json ($result);
        }
        else {
            flash (($result ['status'] == 'success' ? 'notification' : 'error'),
                    $result ['statusText']);
            redirect_to ('/');
        }
    }
    
    /**
     * Extend lifetime to the possible allowed maximum
     */
    public function extendMaximumAction () {
        $file = $this->getFile ();

        $result = array ();
        if ($file->extends_count < fz_config_get ('app', 'max_extend_count')) {
            $file->extendMaximumLifetime ();
            $file->save ();
            $result ['status']     = 'success';
            $result ['statusText'] = __('Lifetime extended to maximum');
            $result ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        } else {
            $result ['status']     = 'error';
            $result ['statusText'] = __r('You can\'t extend a file lifetime more than %x% times',
                                    array ('x' => fz_config_get ('app', 'max_extend_count')));
        }

        if ($this->isXhrRequest()) {
            return json ($result);
        }
        else {
            flash (($result ['status'] == 'success' ? 'notification' : 'error'),
                    $result ['statusText']);
            redirect_to ('/');
        }
    }

    /**
     * Toggle login requirement
     */
    public function confirmToggleRequireLoginAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);
        set ('file', $file);

        return html ('file/confirmToggleRequireLogin.php');
    }
    
    /**
     * Toggle login requirement
     */
    public function toggleRequireLoginAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);

        $result = array ();
        if (! (fz_config_get('app', 'login_requirement', 'on') == 'on')) {
            // do not allow to toggle at all if option is not enabled
            $result ['status']     = 'error';
            $result ['statusText'] = __('You are not allowed to toggle login requirement.');
        } else {
            $file->require_login = ($file->require_login == 1 ? 0 : 1);
            $status = ($file->require_login ? __('on') : __(off));
            $result ['status']     = 'success';
            $result ['statusText'] = 
            __r('Login requirement toggled %status% for file %file%', 
            array('status' => $status, 'file' => $file->file_name));
            $result ['html']       = partial ('main/_file_row.php', array ('file' => $file));
        }
        $file->save();

        if ($this->isXhrRequest()) {
            return json ($result);
        }
        else {
            flash (($result ['status'] == 'success' ? 'notification' : 'error'),
                    $result ['statusText']);
            $user->is_admin ? redirect_to ('/admin/files') : redirect_to ('/');
        }
    }
    
    /**
     * Allows to download file with filez-1.x urls
     */
    public function downloadFzOneAction () {
        if (! fz_config_get('app', 'filez1_compat'))
            halt (HTTP_FORBIDDEN);
        
        $file = Fz_Db::getTable('File')->findByFzOneHash ($_GET ['ad']);
        if ($file === null) {
            halt (NOT_FOUND, __('There is no file for this code'));
        }
        set ('file',      $file);
        set ('available', $file->isAvailable () || $file->isOwner ($this->getUser ()));
        set ('uploader',  $file->getUploader ());

        return html ('file/preview.php');
    }


    /**
     * Delete a file
     */
    public function confirmDeleteAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);
        set ('file', $file);

        return html ('file/confirmDelete.php');
    }
    
    /**
     * Delete a file
     */
    public function deleteAction () {
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);
        $file->delete();

        if ($this->isXhrRequest())
            return json (array ('status' => 'success'));
        else {
            flash ('notification', __('File deleted.'));
            $user->is_admin ? redirect_to ('/admin/files') : redirect_to ('/');
        }
    }

    /**
     * Report a file
     */
    public function reportAction () {
        $file = $this->getFile ();

        // Send report
        $mail = $this->createMail();
        $subject = $file->file_name . ' has been reported';
        $msg = 'File name: ' . $file->file_name . '\n';
        $msg.= 'Reason: ' . $_POST['report-reason'] . '\n';
        if (isset($_POST['comment'])) {
            $msg.= 'Additional information: ' . $_POST['comment'] . '\n';
        }
        
        $mail->setBodyText ($msg);
        $mail->setSubject  ($subject);
        $mail->setReplyTo  (fz_config_get('app', 'admin_email'));
        $mail->clearFrom();
        $mail->setFrom     (fz_config_get('app', 'admin_email'));
        $mail->addTo(fz_config_get('app', 'admin_email'));
        try {
            $mail->send ();
            flash ('notification', __('File has been reported.'));
            redirect_to($file->getDownloadUrl());
        }
        catch (Exception $e) {
            fz_log ('Error while sending email (reporting)', FZ_LOG_ERROR, $e);
            flash ('notification', __('Failed to report the file. Try again.'));
            redirect_to($file->getDownloadUrl());
        }
    }
    
     /**
     * Edit a file.
     */
    public function editAction () {
        $post = $_POST;
        $this->secure ();
        $file = $this->getFile ();
        $user = $this->getUser ();
        if (! $user->is_admin) $this->checkOwner ($file, $user);

        // Usual checks
        // Computing default values
        $comment = array_key_exists ('comment',  $post) ? $post['comment'] : '';
        $folder = array_key_exists ('folder', $post) ? $post['folder'] : '';
        
        // Allow only numbers and letters and convert space to _
        $folder = preg_replace('/[^A-Za-z0-9_]/', '', $folder);
        $folder = preg_replace('/ /', '_', $folder);

        if (! empty ($post ['password']))
            $file->setPassword  ($post ['password']);
        
        $file->comment = substr ($comment, 0, 199);
        $file->folder  = substr ($folder, 0, 199);
        
        try {
            $file->save ();
        
            if ($this->isXhrRequest())
                return json (array ('status' => 'success'));
            else {
                flash ('notification', __('File updated.'));
                $user->is_admin ? redirect_to ('/admin/files') : redirect_to ('/');
            }
        } catch (Exception $e) {
            fz_log ('Can\'t update file "'. $file .'" edited by '.$user['email'], FZ_LOG_ERROR);
            fz_log ($e, FZ_LOG_ERROR);
            return null;
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
        $subject = __r('[FileZ] "%sender%" wants to share a file with you', array (
            'sender' => $user));
        $msg = __r('email_share_file (%file_name%, %file_url%, %sender%, %msg%)', array(
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
        foreach (explode (' ', $_POST['to']) as $email) {
            $email = trim ($email);
            if (empty ($email))
                continue;
            if ($emailValidator->isValid ($email))
                $mail->addBcc ($email);
            else {
                $msg = __r('Email address "%email%" is incorrect, please correct it.',
                    array ('email' => $email));
                return $this->returnError ($msg, 'file/email.php');
            }
        }

        try {
            $mail->send ();
            return $this->returnSuccessOrRedirect ('/');
        }
        catch (Exception $e) {
            fz_log ('Error while sending email', FZ_LOG_ERROR, $e);
            $msg = __('An error occured during email submission. Please try again.');
            return $this->returnError ($msg, 'file/email.php');
        }
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
        set ('files', 
            Fz_Db::getTable ('File') 
                ->findByOwnerFolderOrderByUploadDateDesc (
                    params ('created_by'),params ('folder')));
        return html ('file/folder.php');
    }
    
    
    // TODO documenter les 2 fonctions suivantes et ? les passer dans la classe controleur

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
    private function returnSuccessOrRedirect ($url) {
        if ($this->isXhrRequest ()) {
            return json (array ('status' => 'success'));
        } else {
            redirect_to ($url);
        }
    }

    /**
     * Retrieve the requested file from database.
     * If the file isn't found, the action is stopped and a 404 error is returned.
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
     *
     * @param File $file
     */
    protected function checkFileAuthorizations ($file) {
        
        // TODO: this looks bloated, any way to make it look cleaner ?
        if (! $file->isOwner ($this->getUser ())) {
            if (! $file->isAvailable ()) {
                halt (HTTP_FORBIDDEN, __('File is not available for download'));

        	} else if ($file->isDownloadLimitReached($this->getUser () )) {
        		halt (HTTP_FORBIDDEN, __('Sorry, download limit reached for this file'));
			} else if (   ( fz_config_get ('app', 'login_requirement', 'on') == 'force'
                          && !$this->getAuthHandler()->isSecured() )
                       || (! ( fz_config_get ('app', 'login_requirement', 'on') == 'off' )
                          &&  $file->require_login 
                          && !$this->getAuthHandler()->isSecured()) ) { // force login
                // redirect to login page if not logged in and global login requirement is set
                flash ('error', __('You have to login before you can access the file') . ': '. $file);
                $this->secure();
            } else if (! empty ($file->password)
                    && ! $file->checkPassword ($_POST['password'])) {
                flash ('error', __('Incorrect password'));
                redirect ('/'.$file->getHash());
            }
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
    
    private function logging() {
    	 // logging information
        // TODO Checking logging Level
        $filelog = Fz_Db::getTable('FileLog');
        $user = $this->getUser();
        $file = $this->getFile ();
        $userID = ($user == NULL) ? "Unknown UserID" : $user['id']; 
        $filelog->insert($file->id, $userID);

    
        
        //--
    	
    }

    /**
     * Return data to the browser with the correct response type (json or html).
     * If the request comes from an iframe (with the is-async GET parameter,
     * the response is embedded inside a textarea to prevent some browsers :
     * quirks (http://www.malsup.com/jquery/form/#file-upload) JQuery Form
     * Plugin will handle the response transparently.
     * 
     * @param array $data
     */
    private function returnData ($data) {
        if (array_key_exists ('is-async', $_GET) && $_GET ['is-async']) {
            return html("<textarea>\n".json_encode ($data)."\n</textarea>",'');
        }
        else {
            flash ('notification', $data ['statusText']);
            redirect_to ('/');
        }
    }

}

?>
