

<h2 class="filename preview">
  <img src="<?php echo get_mimetype_icon_url ($file->getMimetype (), 48) ?>" class="mimetype" />
  <?php echo h($file->file_name) ?> (<?php echo $file->getReadableFileSize () ?>)
</h2>

<section id="preview-file">

  <?php if ($available && ! $checkPassword && $file->isImage ()): ?>
    <p id="preview-image">
      <a href="<?php echo $file->getDownloadUrl ()?>/view">
        <img src="<?php echo $file->getDownloadUrl ()?>/view" class="preview-image" width="617px"/>
      </a>
    </p>
  <?php endif ?>

  <p id="availability">
    <?php echo __r('Available from %available_from% to %available_until%', array (
        'available_from'  => $file->getAvailableFrom()->toString  (Zend_Date::DATE_LONG),
        'available_until' => '<b>'.$file->getAvailableUntil()->toString (Zend_Date::DATE_LONG).'</b>',
    )) ?>
  </p>

  <p id="owner">
    <?php echo __('Uploaded by') ?> : <b>
    <?php if (array_key_exists('firstname', $uploader)): ?>
      <?php echo h($uploader['firstname']).' '.h($uploader['lastname']) ?>
    <?php else: ?>
      <?php echo h($uploader['email']) ?>
    <?php endif ?>
    </b>
  </p>

  <?php if ($file->comment): ?>
    <p id="comment"><b><?php echo __('Comments') ?></b> : <?php echo h($file->comment) ?></p>
  <?php endif ?>

  <?php if (fz_config_get('app', 'enable_reporting', true) == true
    && !$file->prevent_reporting && $file->reported): ?>
    <p id="report">
      <?php __('This file has been reported.'); ?>
    </p>
  <?php elseif (!$file->prevent_reporting && !$file->reported): ?>
    <p id="report" class="report-file">
      <a href="<?php echo $file->getDownloadUrl () ?>/report" class="small">
        <?php echo __('Report this file'); ?>
      </a>
    </p>
  <?php endif ?>

  <?php if ($available): ?>
    <?php if (! $checkPassword && (! $requireLogin || ($requireLogin && $isLoggedIn))): 
    // no password, login required or login requirement while logged in ?>

      <?php if ($file->isImage ()): ?>
        <p id="download" class="image">
          <a href="<?php echo $file->getDownloadUrl ()?>/download" class="awesome blue">
            <?php echo __('Download') ?>
          </a>
        </p>
      <?php else: ?>
        <p id="download">
          <?php if (fz_config_get('app', 'autostart_download', true) == true): ?>
              <?php echo __('Your download will start shortly...') ?>
              <a href="<?php echo $file->getDownloadUrl ()?>/download">
                <?php echo __('If not, click here') ?>
              </a>.
              <script type="text/javascript">
                function startDownload () {window.location= "<?php echo $file->getDownloadUrl ()?>/download";}
                $(document).ready (function() {
                  setTimeout ('startDownload()', 1000); // Give chrome some time to finish downloading images on the page
                });
              </script>
          <?php else: ?>
              <a href="<?php echo $file->getDownloadUrl ()?>/download" class="awesome blue large">
                <?php echo __('Click here to download the file') ?>
              </a>
          <?php endif ?>    
        </p>
      <?php endif ?>
    <?php elseif (! $checkPassword && $requireLogin && !$isLoggedIn): 
    // no password required, but login requirement and is is not logged in ?>
          <p id="preview-message"><a href="<?php echo $file->getDownloadUrl ()?>/download">
          <?php echo __('You need to login before you can access this file.') ?>
          </a></p>
    <?php else: // this file need a password ?>

      <form action="<?php echo $file->getDownloadUrl ()?>/download" method="POST" id="download">
        <label for="password">
          <?php echo __('You need a password to download this file') ?>
        </label>
        <input type="password" name="password" id="password" class="password" size="4"/>
        <input type="submit" value="<?php echo __('Download') ?>" class="awesome blue" />
      </form>
    <?php endif ?>
  <?php else: ?>
    <?php echo __('The file is not available yet.') ?>
  <?php endif ?>
</section>


<section class="report-file fz-modal">
  <form method="POST" enctype="application/x-www-form-urlencoded" action="<?php echo $file->getDownloadUrl ()?>/report" id="report-form">
  <div id="report-reason">
    <label for="select-report-reason"><?php echo __('Report reason') ?> :</label>
    <select id="select-report-reason" name="report-reason" alt="<?php echo __('Select a report reason') ?>" class="report-select">
      <option value=""><?php echo __('Select a reason') ?></option>
      <option><?php echo __('Copyright infringement') ?></option>
      <option><?php echo __('Offensive contents') ?></option>
      <option><?php echo __('Site rule violation') ?></option>
    </select>
  </div>
  <div id="report-comment">
    <label for="input-comment"><?php echo __('Comments') ?> :</label>
    <input type="text" id="input-comment" name="comment" value="" alt="<?php echo __('Add a comment (optional)') ?>" maxlength="200" />
  </div>
    <div id="report">
    <input type="submit" id="send-report" name="upload" class="awesome blue" value="&raquo; <?php echo __('Report') ?>" />
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
        width: '250px',
        modal: true
      });

      // Set title for each modal
      $('section.report-file').dialog ('option', 'title', <?php echo json_encode(__('Report file')) ?>);

      // Replace upload form with one big button, and open a modal box on click
      $('p.report-file').wrapInner ($('<a href="#"></a>'));
      $('p.report-file a').click (function (e) {
        $('section.report-file').dialog ('open');
        e.preventDefault();
      });

    });
</script>


