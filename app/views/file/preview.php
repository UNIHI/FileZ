<h2 class="filename preview">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype (), 48) ?>"
    class="mimetype" />
  <?php echo h($file->file_name) ?> (<?php echo $file->getReadableFileSize() ?>)
</h2>

<section id="preview-file">

  <?php if ($available && ! $checkPassword && $file->isImage ()): ?>
    <p id="preview-image">
      <a href="<?php echo $file->getDownloadUrl ()?>/view">
        <img src="<?php echo $file->getDownloadUrl ()?>/view"
          class="preview-image" width="617px"/>
      </a>
    </p>
  <?php endif ?>

  <p id="availability">
    <?php
    echo __r('Available from %available_from% to %available_until%',
      array (
        'available_from'  =>
          $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
        'available_until' => '<b>'
          . $file->getAvailableUntil()->toString(Zend_Date::DATE_LONG).'</b>'
      )
    )
    ?>
  </p>

  <p id="owner">
    <?php echo __('Uploaded by:') ?> <b><?php echo h($uploader) ?></b>
  </p>

  <?php if ($file->comment): ?>
  <p id="comment">
    <b><?php echo __('Comment:') ?></b> <?php echo h($file->comment) ?>
  </p>
  <?php endif ?>

  <?php if ($available): ?>
    <?php if (! $checkPassword && (! $requireLogin || ($requireLogin && $isLoggedIn))): 
    // you get here if there is no password required
    // AND (if login requirement is off OR if it's on and you are logged in) ?>

      <?php if ($file->isImage ()): ?>
        <p id="download" class="image">
          <?php
          echo a(array('href'=>$file->getDownloadUrl ().'/download',
            'class'=>'awesome blue'), __('Download'));
          ?>
        </p>
      <?php else: // no image ?>
        <div id="download">
          <?php if (fz_config_get('app', 'autostart_download', true)): ?>
            <?php
            echo __('Your download will start shortly...')
            . a(array('href'=>$file->getDownloadUrl ().'/download'),
              __('If not, click here')) . '.';
            ?>
            <script type="text/javascript">
              function startDownload () {
                window.location="<?php echo $file->getDownloadUrl()?>/download";
              }
              $(document).ready (function() {
                // Give chrome some time to finish downloading
                // images on the page
                setTimeout ('startDownload()', 1000);
              });
            </script>
          <?php else: // no autostart ?>
            <?php
            echo a(array('href'=>$file->getDownloadUrl (). '/download',
              'class'=>'awesome blue large'),
              __('Click here to download the file'));
            ?>
          <?php endif ?>
        </div>
        
        <?php if (fz_config_get('app', 'enable_reporting', true)): ?>
        <div id="report" class="report-file">
          <?php
          echo a(
            array(
              'href'=>$file->getDownloadUrl () .'/report',
              'id'=>'report-link',
              'class'=>'awesome blue large'),
            __('Report this file'));
          ?>
        </div>
        <?php endif ?>
        
      <?php endif // end no image ?>
    <?php elseif (! $checkPassword && $requireLogin && !$isLoggedIn): 
      // you get here if there is no password required
      // AND login requirement is enabled but you are NOT logged in ?>
      <p id="preview-message"><a href="login">
      <?php flash ('download_url', $file->getDownloadUrl () ); ?>
      <?php echo __('You need to login before you can access this file.') ?>
      </a></p>
    <?php else: // this file need a password ?>
      <form action="<?php echo $file->getDownloadUrl ()?>/download" method="POST" id="download">
        <label for="password">
          <?php echo __('You need a password to download this file') ?>
        </label>
        <input type="password" name="password" id="password" class="password"
          size="4"/>
        <input type="submit" value="<?php echo __('Download') ?>"
          class="awesome blue" />
      </form>
    <?php endif ?>
  <?php else: // availability condition is false ?>
    <?php echo __('The file is not available.') ?>
  <?php endif ?>
</section>

<section class="report-file fz-modal">
  <form method="POST" enctype="application/x-www-form-urlencoded"
    action="<?php echo $file->getDownloadUrl ()?>/report" id="report-form">
  <div id="report-reason">
    <label for="select-report-reason"><?php echo __('Report reason:')?></label>
    <select id="select-report-reason" name="report-reason"
      title="<?php echo __('Select a report reason') ?>" class="report-select">
      <option selected="selected" disabled="disabled"><?php echo __('Select a reason') ?></option>
      <option><?php echo __('File is corrupt') ?></option>
      <option><?php echo __('Copyright infringement') ?></option>
      <option><?php echo __('Offensive contents') ?></option>
      <option><?php echo __('Site rule violation') ?></option>
    </select>
  </div>
  <div id="report-comment">
    <label for="input-comment"><?php echo __('Comment (optional):') ?></label>
    <input type="text" id="input-comment" name="comment" value=""
      title="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
  <div class="submit-report">
    <input type="submit" id="send-report" name="upload" class="awesome blue"
      value="&raquo; <?php echo __('Report') ?>" />
  </div>
  </form>
</section>

<script type="text/javascript">
    $(document).ready (function () {

      // Modal box generic configuration
      $(".fz-modal").dialog({
        bgiframe: true,
        autoOpen: false,
        resizable: false,
        width: '320px',
        modal: true
      });

      // Set title for report file dialog
      $('section.report-file').dialog ('option', 'title',
        <?php echo json_encode(__('Report file')) ?>);

      // Open a modal box on click
      $('div.report-file').wrapInner ($('<a href="#"></a>'));
      $('div.report-file a').click (function (e) {
        $('section.report-file').dialog ('open');
        e.preventDefault();
      });

      // Hide report button unless user has chosen a valid reason 
      $('#send-report').hide();

      $('#select-report-reason').change(function () {
        if ($(this).val() != '<?php echo __('Select a reason') ?>') {
            $('#send-report').show();
        } else {
            $('#send-report').hide();
        }
      });

      $('#report-form').ajaxForm ({
        success:   onReportFinished, // post-submit callback
        resetForm: true,             // reset the form after successful submit
        dataType:  'json',           // force response type to JSON
        iframe:    true,       // force the form to be submitted using an iframe
        data:      {token: $.cookie('token') } // Validation token
      });

      // Let the server know it has to return JSON
      $('#report-form').attr ('action', 
        $('#report-form').attr ('action') + '?is-async=1');
    });
    
    /**
     * Function called once report request has processed
     */
    var onReportFinished = function (data, status) {
      $('section.report-file').dialog ('close');
      $('#report').html('<?php echo __('File has been reported.'); ?>');
      if (data.status == 'success') {
          notify (data.statusText);
      } else {
          notifyError (data.statusText);
      }
    };
    
    /**
     * Display an error notification and register the delete handler
     */
    var notifyError = function (msg) {
        $('.notif').remove();
        $('<p class="notif error">'+msg+'</p>')
            .appendTo ($('header'))
            .configureNotification ();
    };
    
    /**
     * Display a success notification and register the delete handler
     */
    var notify = function (msg) {
        $('.notif').remove();
        $('<p class="notif ok">'+msg+'</p>')
            .appendTo ($('header'))
            .configureNotification ();
    };
</script>
