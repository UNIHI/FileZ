<?php 
  if (array_key_exists('ui-switch', $_COOKIE) && $_COOKIE['ui-switch'] == 'simple') 
    $simple = true;
  else $simple = false;
?>
<div class="file-description <?php echo $simple?'no-indent':'' ?>">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype ()) ?>"
    class="mimetype <?php echo $simple?'mimetype-simple':''?>" />
  <p class="filename <?php echo $simple?'small-filename':'' ?>">
    <a href="<?php echo $file->getDownloadUrl () ?>">
      <?php echo h (truncate_string ($file->file_name, 40)) ?>
    </a>
  </p>
  <p class="comment"><?php echo $file->comment ?></p>
  <p class="folder"><?php echo $file->folder ?></p>
  <p class="require-login"><?php echo $file->require_login; ?></p>
  <p class="has-password"><?php echo ($file->password ? 1 : 0)?></p>
  <p class="available-from"><?php echo $file->available_from; ?></p>
  <p class="available-until"><?php echo $file->available_until; ?></p>
  <p class="<?php echo $simple?'small-filesize':'filesize' ?>">(<?php echo $file->getReadableFileSize () ?>)</p>
  <p class="<?php echo $simple?'availability-simple':'availability-hide'?>">
    <?php
    echo __r('%from% to %to%', array ('from' =>
      $file->getAvailableFrom()->toString(option ('localeDateFormat')),
      'to' => '<b>'.$file->getAvailableUntil ()->toString (
      option ('localeDateFormat')).'</b>')
    );
    ?>
  </p>
  <p class="<?php echo $simple?'filefolder-simple':'filefolder-hide'?>">
    <?php
    if (isset($file->folder) && $file->folder != '') {
      echo __('Folder') .': ';
      echo a(array(
      'href'  => $file->getDownloadFolderUrl ($fz_user->id),
      'id'    => '',
      'class' => '',
      'title' => __('Open folder contents')),
      $file->folder);
    } else {
      echo __('No folder assigned');
    }
    ?>
  </p>
  <p class="deletelink"><?php echo $file->getDownloadUrl()?>/delete</p>
  <?php
  if (fz_config_get ('app', 'enable_copy_to_clipboard', true)) {
    if ($simple == true)
      echo '<p class="zclip-simple">';
    else
      echo '<p class="zclip">';
    $zclipButtonStyle = $simple?'zclip zclip-simple':'awesome blue zclip';
    echo a(array(
    'href'  => $file->getDownloadUrl () . '/copy',
    'id'    => '',
    'class' => $zclipButtonStyle,
    'title' => __('Copy download link to clipboard')),
    __('Copy to clipboard'));
    echo '</p>';
  }
  ?>
  <p class="<?php echo $simple?'share-simple':'share'?>">
  <?php
  $shareButtonStyles = $simple?'share share-simple':'awesome green share';
  echo a(array(
  'href'  => $file->getDownloadUrl () . '/share',
  'id'    => '',
  'class' => $shareButtonStyles,
  'title' => __('Share file with others')),
  __('Share'));
  ?>
  </p>
</div>

<?php if (!$simple): ?>
<div class="file-attributes file-folder <?php echo $simple?'folder-simple':''?>">
  <p class="filefolder">
    <?php
    if (isset($file->folder) && $file->folder != '') {
      echo __('Folder') .': ';
      echo a(array(
      'href'  => $file->getDownloadFolderUrl ($fz_user->id),
      'id'    => '',
      'class' => '',
      'title' => __('Open folder contents')),
      $file->folder);
    } else {
      echo __('No folder assigned');
    }
    ?>
  </p>
</div>
<div class="file-attributes">
  <p class="availability">
  <?php
  echo __r('Available from %from% to %to%', array ('from' =>
    $file->getAvailableFrom()->toString(option ('localeDateFormat')),
    'to' => '<b>'.$file->getAvailableUntil ()->toString (
    option ('localeDateFormat')).'</b>')
  );
  ?>
  </p>
  <p class="download-counter">
    <?php
    switch($file->download_count) {
      case 0:
        echo __('Never downloaded');
        break;
      case 1:
        echo __('Downloaded once');
        break;
      default:
        echo __r('Download %x% times',
          array('x' => (int) $file->download_count));
    }
    ?>
  </p>
<?php endif ?>
  <p>
  <?php 
  $editButtonClass = $simple?'edit edit-simple':'edit';
  echo a(array(
  'href'  => $file->getDownloadUrl () . '/edit',
  'id'    => '',
  'class' => $editButtonClass,
  'title' => __('Edit')),
  __('Edit'));
  
  ?>
  <div class="file-functions">
  
  <p class="edit">
  </p>
  </div>
</div>
