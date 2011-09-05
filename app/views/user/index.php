<h2><?php echo __('Manage users') ?></h2>

<?php if ($isInternal): ?>
<p><a href="<?php echo url_for ('/admin/users/new') ?>" class="awesome">
  <?php echo __('Create a new user') ?></a></p>
<?php endif ?>
<table id="user_list" class="data">
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Role') ?></th>
    <th><?php echo __('Valid files') ?></th>
    <th><?php echo __('Disk usage') ?></th>
    <th><?php echo __('Expired files') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>

<?php foreach ($users as $user_item): ?>
  <tr>
    <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>">
      <?php echo h($user_item)." (".h($user_item->username).")" ?></a></td>
    <td><?php echo ($user_item->is_admin) ? __('admin') : '-' ?></td>
    <td><?php echo count ($user_item->getFiles ()) ?></td>
    <td><?php echo $user_item->getTotalDiskSpace(); ?></td>
    <td><?php echo count ($user_item->getFiles (true)) ?></td>
    <td>
      <a href="<?php echo url_for ('/admin/users/'.$user_item->id.'/edit') ?>">
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
          <?php echo __('Delete') ?>
        </a>
    <?php endif ?>
    </td>
  </tr>
<?php endforeach ?>
</table>
<script type="text/javascript">
  $(document).ready (function () {
    $('a.admin-delete', this).click (function (e) {
      e.preventDefault();
      var postData = { token : $.cookie('token') }
      $.postJSON($(this).attr('href'), postData, function (data) {
        if (data.status == undefined) {
          //notifyError (settings.messages.unknownErrorHappened);
        } else if (data.status == 'success') {
          //link.qtip('destroy');
          //fileListItem.slideUp(1000, function() { $(this).remove(); });
          //fileListItem.initFileActions ();
          //notify (data.statusText);
        } else if (data.status == 'error'){
          //notifyError (data.statusText);
        }
        $.cookie('token', data.token);
      });
    });
  });
</script>