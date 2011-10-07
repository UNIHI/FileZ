
<h2><?php __('Send this link via mail:') ?> <span class="filename">(<?php echo h($file->file_name) ?>)</span></h2>

<?php echo partial ('file/_mailForm.php', array ('file' => $file)) ?>
