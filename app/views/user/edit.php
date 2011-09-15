<h2 class="new-user"><?php echo __('Edit user') ?></h2>
<section class="new-file fz-modal">
  <form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('/admin/users/'.$user->id) ?>" id="update_user-form">
  
  <?php if ($isInternal === true): ?>
  
  <div id="username">
    <label for="input-username"><?php echo __('Username:') ?></label>
    <input type="text" id="input-username" name="username" value="<?php echo $user->username ?>" alt="<?php echo __('Username') ?>" maxlength="20" />
    <label for="input-password"><?php echo __('Password:') ?></label>
    <input type="password" id="input-password" name="password" class="password" autocomplete="off" size="5"/>
  </div>
  <div id="email">
    <label for="input-email"><?php echo __('Email:') ?></label>
    <input type="text" id="input-email" name="email" value="<?php echo $user->email ?>" alt="<?php echo __('email') ?>" maxlength="200" />
  </div>
  <div id="firstname">
    <label for="input-firstname"><?php echo __('Firstname:') ?></label>
    <input type="text" id="input-firstname" name="firstname" value="<?php echo $user->firstname ?>" alt="<?php echo __('Firstname') ?>" maxlength="20" />
  </div>
  <div id="lastname">
    <label for="input-lastname"><?php echo __('Lastname:') ?></label>
    <input type="text" id="input-lastname" name="lastname" value="<?php echo $user->lastname ?>" alt="<?php echo __('Lastname') ?>" maxlength="20" />
  </div>
  
  <?php endif ?>
  
  <input type="hidden" name="is_admin" id="is_admin-input" value="0" />
  <input type="hidden" name="is_locked" id="is_locked-input" value="0" />
  <ul id="options">
    <?php /* 
      <li id="is_admin-item">
      <input type="checkbox" name="is_admin" id="is_admin" <?php echo ($user->is_admin==1) ? "checked" : "" ?> />
      <label for="is_admin" title="<?php echo __('This user can administrate FileZ') ?>">
        <?php echo __('Admin') ?>
      </label>
    </li> 
    */ ?>
    <li id="is_locked-item">
      <input type="checkbox" name="is_locked" id="is_locked" 
      <?php echo ($user->is_locked==1) ? "checked" : "" ?> />
      <label for="is_locked" title="
      <?php echo __('This user is excluded from FileZ usage.') ?>">
        <?php echo __('User locked') ?>
      </label>
    </li>
    <li id="lock_reason-item">
      <input type="text" name="lock_reason" id="lock_reason" value="
 <?php echo ($user->is_locked==1 && $user->lock_reason != '') 
        ? $user->lock_reason : "" ?>" />
      <label id="lock_reason_label" for="lock_reason" title="
      <?php echo __('This user is excluded from FileZ usage.') ?>">
        <?php echo __('Lock reason') ?>
      </label>
    </li>
  </ul>

  <div id="upload">
    <input type="submit" id="update_user" name="update_user" class="awesome blue large" value="&raquo; <?php echo __('Update') ?>" />
  </div>
  </form>
</section>

<script type="text/javascript">
    $(document).ready (function () {
      
      checkReason();
      $('#is_locked').click(function (e) {
        checkReason();
      });
      
      function checkReason() {
        if ($("#is_locked:checked").length) {
          $('#lock_reason-item').show();
        } else {
          $('#lock_reason-item').hide();
        }
      }
    });
</script>
