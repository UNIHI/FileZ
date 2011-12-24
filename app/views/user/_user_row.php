<tr class="table-data-row">
  <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>">
    <?php echo h($user_item)." (".h($user_item->username).")" ?></a>
  </td>
  <td>
    <?php echo h($user_item->email) ?></a>
  </td>
  <td>
    <?php echo ($user_item->is_admin) ? '<img src="../resources/images/icons/admin.png" alt="'.__('admin').'" title="'.__('admin').'">' : '' ?>
    <?php echo ($user_item->is_locked) ? '<img src="../resources/images/icons/keys.png" alt="'.__('locked').'" title="'.__('locked').'">' : '' ?>
  </td>
  <td><?php echo count ($user_item->getFiles (false)) ?>
    (<?php echo $user_item->getTotalDiskSpace(true, false) ?> MB)
  </td>
  <td><?php echo count ($user_item->getFiles (true)) ?>
    (<?php echo $user_item->getTotalDiskSpace(false, true) ?> MB)
  </td>
  <td>
    <?php echo $user_item->getTotalDiskSpace(true, true) ?> MB
  </td>
  <td>
    <a href="<?php echo url_for ('/admin/users/'.$user_item->id.'/edit') ?>">
      <img src="../resources/images/icons/edit.png">           
      <?php echo __('Edit') ?>
    </a>
  <?php if ( $fz_user->id != $user_item->id ) : // prevents self-deleting ?>
    <a onclick='javascript:return confirm (
      <?php echo json_encode(
        __r('Are you sure you want to delete the user "%displayname%" '
        .'(%username%)?',
          array (
            'displayname' => $user_item,
            'username' => $user_item->username)
          )
        ) ?>)'
        href="
        <?php echo url_for ('/admin/users/'.$user_item->id.'/delete') ?>">
      <img src="../resources/images/icons/remove.png" />
      <?php echo __('Delete') ?>
    </a>
  <?php endif ?>
  </td>
</tr>