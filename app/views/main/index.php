<?php if (array_key_exists('download_url', $_SESSION)) {
    // used in case a login was required. user will get redirected after having logged in
    $download_url = $_SESSION['download_url'];
    unset($_SESSION['download_url']);
    redirect_to($download_url);
} ?>

<h2 class="new-file"><?php echo __('Upload a new file') ?></h2>
<section class="new-file fz-modal" style="display:none">
  <form method="POST" enctype="multipart/form-data" action="<?php echo url_for ('upload') ?>" id="upload-form">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_upload_size ?>" />
  <div id="file">
    <label for="file-input"><?php echo __r('File (Max size: %size%)', array ('size' => bytesToShorthand ($max_upload_size))) ?> :</label>
    <div id="input-file">
      <input type="file" id="file-input" name="file" value="" alt="<?php echo __('File') ?>" />
    </div>
  </div>
  <div id="lifetime">
    <label for="select-lifetime"><?php echo __('Lifetime') ?> :</label>
    <select id="select-lifetime" name="lifetime" alt="<?php echo __('Select a lifetime') ?>">
      <?php $default = fz_config_get ('app', 'default_file_lifetime', 10);
            $max     = fz_config_get ('app', 'max_file_lifetime',     20);
            for ($i = 1; $i <= $max; ++$i  ): ?>
        <option value=<?php echo "\"$i\"".($i == $default ? ' selected="selected" ' : '') ?>>
          <?php echo str_replace ('%n%', $i, ($i > 1 ? __('%n% days') : __('%n% day'))) // FIXME ugly fix for handling plural ?>
        </option>
      <?php endfor ?>
    </select>
  </div>
  <div id="start-from">
    <label for="input-start-from"><?php echo __('Starts from') ?> :</label>
    <input type="text" id="input-start-from" name="start-from" value="<?php echo $start_from ?>" alt="<?php echo __('Select a starting date') ?>" />
  </div>
  <div id="comment">
    <label for="input-comment"><?php echo __('Comments') ?> :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div id="folder">
    <label for="input-folder"><?php echo __('Assign file to folder (optional)') ?> :</label>
    <input type="text" id="input-folder" name="folder" value="" alt="<?php echo __('Assign file to folder (optional)') ?>" maxlength="200" />
  </div>
  <ul id="options">
    <li id="option-email-notifications">
      <?php if (fz_config_get('app', 'force_notification', true) == true): ?>
      <label for="email-notifications" title="
      <?php echo __('Send me email notifications when the file is uploaded and before it will be deleted.'); 
            echo __('This option cannot be disabled.') 
      ?>">
      <input type="checkbox" name="email-notifications" id="email-notifications" checked="checked" disabled="disabled" />
      <?php echo __('Send me email notifications') ?>
      </label>
      <?php else: ?>
      <label for="email-notifications" title="<?php echo __('Send me email notifications when the file is uploaded and before it will be deleted') ?>">
      <input type="checkbox" name="email-notifications" id="email-notifications" checked="checked"/>
        <?php echo __('Send me email notifications') ?>
      </label>
      <?php endif ?>          
    </li>
    <li id="option-use-password">
      <label for="use-password" title="<?php echo __('Ask a password to people who will download your file') ?>">
      <input type="checkbox" name="use-password" id="use-password"/>
        <?php echo __('Use a password to download') ?>
      </label>
      <input type="password" id="input-password" name="password" class="password" autocomplete="off" size="5"/>
    </li>
    <?php if (fz_config_get ('app', 'login_requirement', 'on') == 'on'): ?>
    <li id="option-require-login">
      <label for="require-login" title="<?php echo __('Require the user to login to grant access to your file.') ?>">
      <input type="checkbox" name="require-login" id="require-login" checked="checked"/>
        <?php echo __('Require login') ?>
      </label>
    </li>
    <?php endif ?>
  </ul>
  <?php if (fz_config_get('app' , 'require_user_agreement', true) == true): ?>
  <ul id="options">
    <li id="option-use-password">
      <label for="accept-user-agreement" title="<?php echo __('You have to accept this user agreement before you can upload the file.')?>">   
      <input type="checkbox" name="user-agreement" id="accept-user-agreement"/>
       <a id="user-agreement" target="user-agreement" href="disclaimer" class="underlined">
         <?php echo __('I have read and understood the user agreement.') ?>
       </a>
      </label>
    </li>
  </ul>
  <?php endif ?>
  <div id="upload">
    <input type="submit" id="start-upload" name="upload" class="awesome blue large" value="&raquo; <?php echo __('Upload') ?>" />
    <div id="upload-loading"  style="display: none;"></div>
    <div id="upload-progress" style="display: none;"></div>
  </div>
  </form>
</section>


<h2 id="uploaded-files-title"><?php echo __('Uploaded files') ?></h2>
<section id="uploaded-files">
  <ul id="files">
    <?php $odd = true; foreach ($files as $file): ?>
      <li class="file <?php echo $odd ? 'odd' : 'even'; $odd = ! $odd ?>" id="<?php echo 'file-'.$file->getHash() ?>">
        <?php echo partial ('main/_file_row.php', array ('file' => $file)) ?> 
      </li>
    <?php endforeach ?>
  </ul>
</section>


<div id="share-modal" class="fz-modal" style="display: none;">
    <p class="instruction"><?php echo __('Give this link to the person you want to share this file with') ?></p>
    <p id="share-link"><a href=""></a></p>
    <p class="instruction"><?php echo __('or share using') ?> :</p>
    <ul id="share-destinations">
        <li class="email"   ><a href="" data-url="%url%/email"><?php echo __('your email') ?></a></li>
        <!-- TODO make next share destinations configurable ! -->
        <li class="facebook"><a href="" target="_blank" data-url="http://www.facebook.com/sharer.php?u=%url%&t=%filename%"><?php echo __('Facebook') ?></a></li>
        <li class="twitter" ><a href="" target="_blank" data-url="http://twitter.com/home?status=%filename% %url%"><?php echo __('Twitter') ?></a></li>
    </ul>
    <div class="cleartboth"></div>
</div>


<section id="edit-modal" class="edit-file fz-modal" style="display:none">
  <form method="POST" enctype="application/x-www-form-urlencoded" action="" id="edit-form">
  <div id="edit-comment">
    <label for="input-comment"><?php echo __('Comments') ?> :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div id="edit-folder">
    <label for="input-folder"><?php echo __('Assign file to folder (optional)') ?> :</label>
    <input type="text" id="edit-input-folder" name="folder" value="" alt="<?php echo __('Assign file to folder (optional)') ?>" maxlength="200" />
  </div>
  <ul id="edit-options">
    <li id="edit-option-use-password">
      <input type="checkbox" name="use-password" id="edit-use-password"/>
      <label for="edit-use-password" title="<?php echo __('Ask a password to people who will download your file') ?>">
        <?php echo __('Use a password to download') ?>
      </label>
      <input type="password" id="edit-input-password" name="password" class="password" autocomplete="off" size="5"/>
    </li>
  </ul>
  <div id="edit">
    <input type="submit" id="do-edit" name="edit" class="awesome blue large" value="&raquo; <?php echo __('Edit') ?>" />
  </div>
  </form>
</section>

<script type="text/javascript">
    $(document).ready (function () {
      $('#input-start-from').datepicker ({minDate: new Date()});
      $('#upload-form').initFilez ({
        fileList:         'ul#files',
        progressBox:      '#upload-progress',
        loadingBox:       '#upload-loading',
        maxFileSize:      <?php echo $max_upload_size ?>,
        progressBar: {
          enable:        <?php echo ($use_progress_bar ? 'true':'false') ?>,
          upload_id_name: '<?php echo $upload_id_name ?>',
          barImage:     '<?php echo public_url_for ('resources/images/progressbg_green.gif') ?>',
          boxImage:     '<?php echo public_url_for ('resources/images/progressbar.gif') ?>',
          refreshRate:   <?php echo $refresh_rate ?>,
          progressUrl:  '<?php echo url_for ('upload/progress/') ?>'
        },
        messages: {
          confirmDelete: <?php echo  json_encode (__('Are you sure to delete this file ?')) ?>,
          confirmToggleOn: <?php echo json_encode(__('Do you want to toggle on login requirement for this file?')) ?>,
          confirmToggleOff: <?php echo json_encode(__('Do you want to toggle off login requirement for this file ?')) ?>,
          unknownError: <?php echo  json_encode (__('Unknown error')) ?>,
          unknownErrorHappened: <?php echo  json_encode (__('An unknown error hapenned while uploading the file')) ?>,
          cancel: <?php echo  json_encode (__('Cancel')) ?>,
          emailMessage: <?php echo  json_encode (__('You can download the file I uploaded here')) ?>,
          editFile: <?php echo json_encode(__("Edit file")) ?>,
          copiedToClipboard: <?php echo json_encode(__('Copied to clipboard')) ?> 
        }
      });

      // Modal box generic configuration
      $(".fz-modal").dialog({
        bgiframe: true,
        autoOpen: false,
        resizable: false,
        width: '650px',
        modal: true
      });

      // Set title for each modal
      $('section.new-file').dialog ('option', 'title', <?php echo json_encode(__('Upload a new file')) ?>);

      // Replace upload form with one big button, and open a modal box on click
      $('h2.new-file').wrapInner ($('<a href="#" class="awesome large"></a>'));
      $('h2.new-file a').click (function (e) {
        $('section.new-file').dialog ('open');
        e.preventDefault();
      });
      
      // Show password box on checkbox click
      $('input.password').hide();
      $('#use-password, #option-use-password label').click (function () { // IE quirk fix
        if ($('#use-password').attr ('checked')) {
            $('input.password').show().focus();
        } else {
            $('input.password').val('').hide();
        }

      });
      
      $('#edit-use-password, #edit-option-use-password label').click (function () { // IE quirk fix
        if ($('#edit-use-password').attr ('checked')) {
            $('input.password').show().focus();
        } else {
            $('input.password').val('').hide();
        }

      });
      
    
      <?php if (fz_config_get('app','privacy_mode',false) == true): ?>
      $('#use-password, #require-login').click(function(event) {
        if (!$('#use-password').is(':checked') && !$('#require-login').is(':checked')) {
          $('#require-login').attr('checked','checked')
          alert(<?php echo "'" . __('You have to give at least a password or require a login.') . "'" ?>);
        }
      });
      <?php endif ?>
    });


</script>

