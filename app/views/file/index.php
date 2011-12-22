<div id="pagination"></div>
<table id="file_list" class="data">
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Uploader') ?></th>
    <th><?php echo __('Availability') ?></th>
    <th><?php echo __('Size') ?></th>
    <th><?php echo __('Download counter') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>

<?php foreach ($files as $file): ?>
  <tr>
    <td>
      <?php
      echo a(array('href'=>$file->getDownloadUrl ()), $file->file_name);
      ?>
    </td>
    <td>
      <?php
      echo a(array('href'=>url_for ('/admin/users/'.$file->getUploader()->id)),
        h($file->getUploader ()));
      ?>
    </td>
    <td>
      <?php
      echo __r('from %from% to %to%', array ('from' =>
        $file->getAvailableFrom()->toString(option ('localeDateFormat')),
        'to' => '<b>'.$file->getAvailableUntil ()->toString (
        option ('localeDateFormat')).'</b>')
      );
      ?>
    </td>
    <td><?php echo $file->getReadableFileSize () ?></td>
    <td><?php echo (int) $file->download_count ?></td>
    <td>
    <?php
    echo a(array('href'=>$file->getDownloadUrl () . '/delete',
      'class'=>'admin-delete'),
      '<img src="../resources/images/icons/remove.png">'.__('Delete'));
    ?>
    </td>
<?php endforeach ?>
</table>
