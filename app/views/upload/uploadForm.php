<form method="POST" enctype="multipart/form-data"
  action="<?php echo url_for ('upload') ?>" id="upload-form">
  <input type="hidden" name="MAX_FILE_SIZE"
  value="<?php echo $max_upload_size ?>" />
  <div id="file">
    <label for="file-input"><?php echo __r('File (Max size: %size%):',
    array ('size' => bytesToShorthand ($max_upload_size))) ?></label>
    <div id="input-file">
      <input type="file" id="file-input" name="file" value=""
      title="<?php echo __('File') ?>" />
    </div>
  </div>
  <div id="start-from">
    <label for="input-start-from"><?php echo __('Available from:') ?></label>
    <input type="text" id="input-start-from" name="start-from"
    value="<?php echo $start_from ?>"
    title="<?php echo __('Select a starting date') ?>" />
    <?php echo __('Format: MM.DD.YYYY') ?>
  </div>
  <div id="available-until">
    <label for="input-available-until"><?php echo __('Available until:') ?></label>
    <input type="text" id="input-available-until" name="available-until"
    value="<?php echo $available_until ?>"
    title="<?php echo __('Select a ending date') ?>" />
    <?php echo __('Format: MM.DD.YYYY') ?>
  </div>
  <div id="comment">
    <label for="input-comment"><?php echo __('Comment (optional):') ?></label>
    <input type="text" id="input-comment" name="comment" value=""
    title="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div id="folder">
    <label for="input-folder">
      <?php echo __('Assign file to folder (optional):') ?></label>
      <input type="text" id="input-folder" name="folder" value=""
      title="<?php echo __('Assign file to folder (optional)') ?>"
      maxlength="200" />
  </div>
  <ul id="options">
    <li id="option-use-password">
      <label for="use-password" title="<?php
      echo __('Ask a password to people who will download your file') ?>">
      <input type="checkbox" name="use-password" id="use-password"/>
        <?php echo __('Use a password to download') ?>
      </label>
      <input type="password" id="input-password" name="password"
      class="password" autocomplete="off" size="5"/>
    </li>
    <?php if (fz_config_get ('app', 'login_requirement', 'on') == 'on'): ?>
    <li id="option-require-login">
      <label for="require-login" title="<?php
      echo __('Require the user to login to grant access to your file.') ?>">
      <input type="checkbox" name="require-login" id="require-login"
      checked="checked"/>
        <?php echo __('Require login') ?>
      </label>
    </li>
    <?php endif ?>
  </ul>
  <ul id="options">
  <?php if (fz_config_get('app' , 'require_user_agreement', true)): ?>
    <li id="option-accept-user-agreement">
      <label for="accept-user-agreement"
      title="<?php echo __('You have to accept this user agreement before '
      .'you can upload the file.')?>">
      <input type="checkbox" name="user-agreement" id="accept-user-agreement"/>
         <?php
         $a=a(array(
          'href' => 'disclaimer',
          'target' => 'user-agreement',
          'id' => 'user-agreement',
          'class' => 'underlined',
          'title' => __('user agreement')
          ),
          __('user agreement'));
         echo __r('I have read and do understand the %userAgreement%.',
          array('userAgreement' => $a));
          ?>
      </label>
    </li>
  <?php endif ?>
    <li id="option-email-notifications">
      <?php if (fz_config_get('app', 'force_notification', true) == true): ?>
      <label for="email-notifications" title="<?php
            echo __('Send me email notifications when the file is uploaded and before it will be deleted.'); 
            echo __('This option cannot be disabled.') 
      ?>">
      <input type="checkbox" name="email-notifications"
        id="email-notifications" checked="checked" disabled="disabled" />
      <?php echo __('Send me email notifications') ?>
      </label>
      <?php else: ?>
      <label for="email-notifications" title="<?php
      echo __('Send me email notifications when the file is uploaded and before it will be deleted') ?>">
        <input type="checkbox" name="email-notifications"
          id="email-notifications" checked="checked"/>
        <?php echo __('Send me email notifications') ?>
      </label>
      <?php endif ?>          
    </li>
  </ul>
  <div id="upload">
    <input type="submit" id="start-upload" name="upload"
    class="awesome blue large" value="&raquo; <?php echo __('Upload') ?>" />
  </div>
</form>
