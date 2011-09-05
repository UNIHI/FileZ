<div class="file-description">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype ()) ?>"
    class="mimetype" />
  <p class="filename">
    <a href="<?php echo $file->getDownloadUrl () ?>">
      <?php echo h (truncate_string ($file->file_name, 40)) ?>
    </a>
  </p>
  <p class="comment"><?php echo $file->comment ?></p>
  <p class="folder"><?php echo $file->folder ?></p>
  <p class="require-login"><?php echo $file->require_login; ?></p>
  <p class="has-password"><?php echo ($file->password ? 1 : 0)?></p>
  <p class="filesize">(<?php echo $file->getReadableFileSize () ?>)</p>
  <?php
  if (fz_config_get ('app', 'enable_copy_to_clipboard', true)) {
    echo '  <p class="zclip">';
    echo a(array(
    'href'  => $file->getDownloadUrl () . '/copy',
    'id'    => '',
    'class' => 'awesome blue zclip',
    'title' => __('Copy download link to clipboard')),
    __('Copy to clipboard'));
    echo '</p>';
  }
  ?>
  <p class="share">
  <?php
  echo a(array(
  'href'  => $file->getDownloadUrl () . '/share',
  'id'    => '',
  'class' => 'awesome green share',
  'title' => __('Share file with others')),
  __('Share'));
  ?>
  </p>
</div>

<div class="file-attributes file-folder">
    <p class="filefolder">
      <?php
      if (isset($file->folder) && $file->folder != '') {
        echo __('Folder') .': '.$file->folder;          
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
  if ($file->extends_count < fz_config_get ('app', 'max_extend_count')) {
    echo a(array(
    'href'  => $file->getDownloadUrl () . '/extend',
    'id'    => '',
    'class' => 'extend',
    'title' => __('Extend one more day')),
    __('Extend one more day'));
    echo a(array(
    'href'  => $file->getDownloadUrl () . '/extendMaximum',
    'id'    => '',
    'class' => 'extendMaximum',
    'title' => __('Extend maximum possible')),
    __('Extend maximum possible'));
  }
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
  <div class="file-functions">
  <?php
    if (fz_config_get ('app', 'login_requirement', 'on') == 'on') {
    $toggle = ($file->require_login ? __('off') : __('on'));
    echo '<p class="toggle">';
    echo a(array(
    'href'  => $file->getDownloadUrl () . '/toggle',
    'id'    => 'toggle',
    'class' => 'toggle-'.($file->require_login ? 'off' : 'on'),
    'title' => __r('Toggle login requirement %var%' , array('var' => $toggle))),
    __r('Toggle login requirement %var%' , array('var' => $toggle)));
    echo '</p>';
  }
  ?>
  
  <p class="delete">
  <?php
  echo a(array(
    'href'  => $file->getDownloadUrl () . '/delete',
    'id'    => '',
    'class' => 'delete',
    'title' => __('Delete')),
    __('Delete'));
  ?>
  </p>
  
  <p class="edit">
  <?php
  echo a(array(
    'href'  => $file->getDownloadUrl () . '/edit',
    'id'    => '',
    'class' => 'edit',
    'title' => __('Edit')),
    __('Edit'));
  ?>
  </p>
  </div>
</div>
