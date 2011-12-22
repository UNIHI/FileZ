<h2 id="user-details-username"><?php echo $user ?></h2>
<ul id="show-user-details">
  <li><b><?php echo __('Email') ?> :</b> <?php echo $user->email ?></li>
  <li><b><?php echo __('Account created') ?> <?php echo new Zend_Date($user->created_at) ?></b></li>
  <?php 
  if ($user->is_admin) echo '<li><b>' .  __('Has administrative privileges') . '</b></li>';
  ?>
</ul>
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
    <td>
      <a href="<?php echo $file->getDownloadUrl () ?>">
        <?php echo $file->file_name ?>
      </a>
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
      <a href="<?php echo $file->getDownloadUrl () . '/delete' ?>">
        <img src="../../resources/images/icons/remove.png">
        <?php echo __('Delete') ?>
      </a>
    </td>
  </tr>
<?php endforeach ?>
</table>
