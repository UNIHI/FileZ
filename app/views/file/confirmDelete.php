
<h2><?php echo __('Do you want to delete this file') ?> : <span class="filename"><?php echo h($file->file_name) ?> ?</span></h2>

<form method="post">
  <p style="padding: 2em 0;">
    <input type="submit" value="<?php echo __('Yes, delete this file') ?>" class="delete"/> |
    <a href="#" onclick="javascript:history.go(-1); return false;"><?php echo __('No, return to the main page') ?></a>
  </p>
</form>
