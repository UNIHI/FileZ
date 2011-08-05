<h2><?php echo __('Admin dashboard') ?></h2>

<?php 

echo __r('Manage %users% and %files%.',

array(
    'users'=>'<a href="'.url_for ('admin/users').'">'.__('users').'</a>',
    'files'=>'<a href="'.url_for ('admin/files').'">'.__('files').'</a>'
     )
);

?>