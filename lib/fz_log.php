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

define ('FZ_LOG_DEBUG',            'debug');
define ('FZ_LOG_ERROR',            'error');
define ('FZ_LOG_CRON',             'cron');
define ('FZ_LOG_CRON_ERROR',       'cron-error');

define ('FZ_LOG_DOWNLOAD',         'download');
define ('FZ_LOG_UPLOAD',           'upload');
define ('FZ_LOG_VIEW',             'view');
define ('FZ_LOG_PREVIEW',          'preview');
define ('FZ_LOG_DELETE',           'delete');
define ('FZ_LOG_EXTEND',           'extend');

define ('FZ_LOG_UPLOAD_MAIL_SENT', 'uploadmail');
define ('FZ_LOG_SHARE_MAIL_SENT',  'sharemail');
define ('FZ_LOG_DELETE_MAIL_SENT', 'deletemail');
define ('FZ_LOG_REPOR_MAIL_SENT', 'reportmail');

/**
 * Logging to files and database.
 * errors and debug information will not be saved to database
 * @param $message Message string
 * @param $type log type
 * @param $vars additional information 
 * @return void
 */
function fz_log ($message, $type = null, $vars = null) {
    
    if ($type == FZ_LOG_DEBUG && option ('debug') !== true)
        return;

    $message = trim ($message);
    
    switch ($type) {
        case FZ_LOG_DEBUG:
        case FZ_LOG_ERROR:
        case FZ_LOG_CRON:
        case FZ_LOG_CRON_ERROR:
            fz_log_file($message, $type, $vars);
            break;
        default:
            if (fz_config_get('logging','log_activity') == true)
                fz_log_db($message, $type, $vars);
            break;
    }
}

// Log errors and debug information to files
function fz_log_file ($message, $type = null, $vars = null) {
    if ($type !== null)
        $type = '-'.$type;

    if ($vars !== null)
        $message .= var_export ($vars, true)."\n";

    $message = str_replace("\n", "\n   ", $message);
    $message = '['.strftime ('%F %T').'] '
            .str_pad ('['.$_SERVER["REMOTE_ADDR"].']', 18)
            .$message."\n";

    if (fz_config_get ('logging', 'log_dir') !== null) {
        $log_file = fz_config_get ('logging', 'log_dir').'/filez'.$type.'.log';
        if (file_put_contents ($log_file, $message, FILE_APPEND) === false) {
            trigger_error('Can\'t open log file ('.$log_file.')', E_USER_WARNING);
        }
    }
    
    if (option ('debug') === true)
        debug_msg ($message);
}

// Log activity information to database
function fz_log_db ($message, $type = null, $vars = null) {
    $file = Fz_Db::getTable('File')->findById ($vars['file_id']);
    if ($file !== null) {
        $log = new App_Model_Log ();
        $log->file_id = $file->id;
        $log->action = $type;
        $log->message = substr($message, 0, 65535); // DB restriction for TEXT
        if  (fz_config_get ('logging', 'log_username') == true)
            $log->username = $file->created_by;
        if  (fz_config_get ('logging', 'log_ip') == true)
            $log->ip = $_SERVER['REMOTE_ADDR'];
        $log->insert();
    }
}

function debug_msg ($message) {
    $messages = option ('debug_msg');
    if (! is_array ($messages))
        $messages = array ();

    $messages [] = $message;
    option ('debug_msg', $messages);
}
