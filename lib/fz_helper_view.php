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
 *
 * @param string $path
 * @return string Url containing the public path of the project
 */
function public_url_for ($path) {
    $args = func_get_args ();
    $paths = array (option ('base_path'));
    $paths = array_merge ($paths, $args);
    return call_user_func_array ('file_path', $paths);
}

/**
 * Truncate a string in the middle with "[...]"
 * 
 * @param string $str
 * @param integer $size
 * @return string Truncated string
 */
function truncate_string ($str, $maxSize) {
    if (($size = strlen ($str)) > $maxSize) {
        $halfDiff = ($size - $maxSize + 5) / 2;
        $str = substr ($str, 0, ($size / 2) - ceil ($halfDiff))
              .'[...]'
              .substr ($str, ($size / 2) + floor ($halfDiff));
    }
    return $str;
}


/**
 * Translate a string
 * @param string
 * @return string
 */
function __($msg) {
    if (! option ('translate')) return $msg;
    return option ('translate')->translate ($msg);
}

/**
 * Translate a string with different plural form
 * @param string    $sing Singular form
 * @param string    $plur Plural form
 * @param integer   
 * @return string
 */
function __p($sing, $plur, $nb) {
    if (! option ('translate')) return $msg;
    return option ('translate')->plural ($sing, $plur, $nb);
}

/**
 * Translate a string and subtitute values defined in $subtitution
 *
 * @param string    $msg
 * @param array     $subtitutions   ex: array('var'=>'real value') will replace
 *                                  %var% by 'real value
 * @return string
 */
function __r($msg, array $subtitutions) {
    $msg = __($msg);
    foreach ($subtitutions as $key => $value)
        $msg = str_replace ("%$key%", $value, $msg);
    return $msg;
}


/**
 * Transform a size in bytes to the shorthand format ('K', 'M', 'G')
 *
 * @param   string      $size
 * @return  integer
 */
function bytesToShorthand ($size) {
    return ($size >= 1073741824 ? (round ($size / 1073741824, 2).'GB') : (
            $size >= 1048576    ? (round ($size / 1048576, 2).'MB') : (
            $size >= 1024       ? (round ($size / 1024, 2).'KB') :
                                           $size.'B')));
}

function doc_img_tag ($name) {

    return '<div class="img-block">'
        .'<img src="'.url_for('/').'doc/user/'.option ('locale')->getLanguage ().'/images/'.$name.'" />'
        .'</div>';

}

function get_mimetype_icon_url ($mimetype, $size = 32) {

    $mimetype = str_replace ('/', '-', $mimetype).'.png';
    $path = 'resources/images/icons/mimetypes/'.$size.'/';
    $mime_basesir = public_url_for ($path);
    $root = option ('root_dir').'/';

    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

    $mimetype = str_replace ('application-', '', $mimetype);
    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

    $mimetype = 'gnome-mime-'.$mimetype;
    if (file_exists ($root.$path.$mimetype))
        return $mime_basesir.$mimetype;

}
/**
 * Make html hyperlink
 * @param array $attributes assoc array with attr_name => attr_value
 * attributes set
 * @param string $innerhtml set tag text
 * @return string html anchor tag
 */
function a(array $attributes, $innerhtml = 'undefined') {
  $a = '<a';
  reset($attributes);
  while (list($key, $val) = each($attributes))
      $a .= ' '. $key . '="' . $val . '"';
  $a .= '>' . $innerhtml . '</a>';
  return $a;
}
