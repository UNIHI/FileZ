<div class="file-description">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype ()) ?>" class="mimetype" />
  <p class="filename">
    <a href="<?php echo $file->getDownloadUrl () ?>">
      <?php echo h (truncate_string ($file->file_name, 40)) ?>
    </a>
  </p>
  <p class="comment"><?php echo $file->comment ?></p>
  <p class="filesize">(<?php echo $file->getReadableFileSize () ?>)</p>
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
</div>
