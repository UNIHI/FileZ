<?php 
echo __r('Manage %users% and %files%.',
  array(
    'users'=>a(array('id'=>'admin-users', 
      'href'=>url_for ('admin/users')),__('users')),
    'files'=>a(array('id'=>'admin-files', 
      'href'=>url_for ('admin/files')),__('files'))
     )
);
?>
<script type="text/javascript">
  $(document).ready (function () {
    $('#admin-users').click(function() {
        $('#admin-tabs').tabs('select', 1);
        return false;
    });
    $('#admin-files').click(function() {
        $('#admin-tabs').tabs('select', 2);
        return false;
    });
  });
</script>
