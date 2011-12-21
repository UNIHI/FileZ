<form method="POST" enctype="application/x-www-form-urlencoded"
action="" id="edit-form">
  <div id="edit-file">
    <div id="input-file">
      <label for="edit-file-input"><?php echo __('File') . ': ' ?></label>
      <input type="text" id="edit-file-input" name="file" 
      value="<?php echo $file; ?>" disabled="disabled" />
    </div>
  </div>
  <div id="edit-start-from">
    <label for="edit-input-available-from"><?php echo __('Starts from:') ?></label>
    <input type="text" id="edit-input-available-from" name="start-from"
    value="<?php echo $availableFrom ?>" disabled="disabled" />
  </div>
  <div id="edit-available-until">
    <label for="edit-input-available-until"><?php echo __('Available until:') ?></label>
    <input type="text" id="edit-input-available-until" name="available-until"
    value="<?php echo $availableUntil ?>"
    title="<?php echo __('Select a ending date') ?>" />
    <?php echo __('Format: MM.DD.YYYY') ?>
  </div>
  <div id="edit-comment">
    <label for="edit-input-comment"><?php echo __('Comment (optional):') ?></label>
    <input type="text" id="edit-input-comment" name="comment"
    value="<?php echo $file->comment ?>"
    title="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div id="edit-folder">
    <label for="edit-input-folder">
      <?php echo __('Assign file to folder (optional):') ?></label>
    <input type="text" id="edit-input-folder" name="folder" 
    value="<?php echo $file->folder ?>"
    title="<?php echo __('Assign file to folder (optional)') ?>" maxlength="200" />
  </div>
  <ul id="edit-options">
    <li>
      <input id="nopassword" type="radio" name="use-password" value="0"
      <?php echo empty($file->password)?'checked="checked"':''?>>
      <label for="nopassword">
        <?php echo __('No password or remove password') ?>
      </label>
    </li>
    <?php if (!empty($file->password)): ?>
      <li>
        <input id="samepassword" type="radio" name="use-password" value="2" 
          checked="checked">
        <label for="samepassword">
          <?php echo __('Do not change password') ?>
        </label>
      </li>
    <?php endif ?>
    <li>
      <input id="changepassword" type="radio" name="use-password" value="1">
      <label for="changepassword">
        <?php echo __('Add or change password') ?>
      </label>
      <label for="edit-input-password"
        title="<?php echo __('Add or change password') ?>">
        <input type="password" id="edit-input-password" name="password" 
          class="password" autocomplete="off" size="5"/> 
      </label>
    </li>
    <li>
      
    </li>
    <li id="edit-option-password-field">
      
    </li>
    <?php if (fz_config_get ('app', 'login_requirement', 'on') == 'on'): ?>
    <li id="edit-option-require-login">
      <label for="edit-input-require-login" title="<?php
      echo __('Require the user to login to grant access to your file.') ?>">
      <input type="checkbox" name="require-login" id="edit-input-require-login" 
      <?php echo $file->require_login==1?'checked="checked"':'' ?>/>
        <?php echo __('Require login') ?>
      </label>
    </li>
    <?php endif ?>
  </ul>
  <div id="edit">
    <input type="submit" id="do-edit" name="edit" class="awesome blue large"
    value="&raquo; <?php echo __('Edit') ?>" />
    <div id="delete">
    <?php
    echo a(array('href'=>$file->getDownloadUrl ().'/delete', 
      'id'=>'do-delete',
      'class'=>'awesome blue large'),
      __('Delete'));
    ?>
    </div>
  </div>
</form>
