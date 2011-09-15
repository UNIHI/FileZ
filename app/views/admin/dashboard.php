<?php 
echo __r('Manage %users% and %files%.',
  array(
    'users'=>a(array('id'=>'admin-users', 
      'href'=>'#'),__('users')),
    'files'=>a(array('id'=>'admin-files', 
      'href'=>'#'),__('files'))
     )
);
?>
<script type="text/javascript">
  $(document).ready (function () {
    $('#admin-users').click(function(e) {
      e.preventDefault();
      $('#admin-tabs').tabs('select', 1);
      return false;
    });
    $('#admin-files').click(function(e) {
      e.preventDefault();
      $('#admin-tabs').tabs('select', 2);
      return false;
    });
  });
</script>
