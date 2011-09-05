<h2><?php echo $user ?></h2>

<p><b><?php echo __('Email') ?> :</b> <?php echo $user->email ?></p>
<p><b><?php echo __('Account created') ?> :</b> <?php echo $user->created_at ?></p>
<p><b><?php echo __('Administrator ?') ?> :</b> <?php echo $user->is_admin ? __('yes') : __('no') ?></p>

<table id="user_files" class="data">
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Availability') ?></th>
    <th><?php echo __('Size') ?></th>
    <th><?php echo __('DL count') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>

<?php foreach ($user->getFiles () as $file): ?>
  <tr>
    <td><a href="<?php echo $file->getDownloadUrl () ?>"><?php echo $file->file_name ?></a></td>
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
    <td><a href="<?php echo $file->getDownloadUrl () . '/delete' ?>"><?php echo __('Delete') ?></a></td>
  </tr>
<?php endforeach ?>
</table>
