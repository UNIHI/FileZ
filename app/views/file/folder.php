<h2 id="folder-listing-title"><?php echo __('Folder listing') ?></h2>
<section id="folder-content">
  <ul id="files">
    <?php $odd = true; foreach ($files as $file): ?>
      <li class="file <?php echo $odd ? 'odd' : 'even'; $odd = ! $odd ?>" id="<?php echo 'file-'.$file->getHash() ?>">
        <?php echo partial ('file/_folder_row.php', array ('file' => $file)) ?> 
      </li>
    <?php endforeach ?>
  </ul>
</section>
