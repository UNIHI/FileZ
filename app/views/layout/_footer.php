  <footer>
    <?php if (is_array (option ('debug_msg'))): ?>
      <div class="debug"><h3>Logged messages :</h3>
      <?php foreach (option ('debug_msg') as $msg): ?>
        <pre><?php echo $msg ?></pre>
      <?php endforeach ?>
      </div>
    <?php endif ?>

    <?php if (isset ($fz_user)): ?>
      <p id="disk-usage">
        <?php echo __r('Using %space% of %quota%', array (
        // TODO this code should not be here
        'space' => '<b id="disk-usage-value">'.bytesToShorthand (Fz_Db::getTable('File')->getTotalDiskSpaceByUser ($fz_user, true, false)).'</b>',
        'quota' => fz_config_get('app', 'user_quota')));
        ?>.
      </p>
    <?php endif ?>

    <div id="support">
      <?php if (fz_config_get('looknfeel', 'bug_report_href')): ?>
        <a href="<?php echo fz_config_get('looknfeel', 'bug_report_href') ?>" class="bug"><?php echo __('Report a bug') ?></a>
      <?php endif; ?>
    </div>

    <?php if (fz_config_get('looknfeel', 'show_credit')): ?>
      <a href="http://gpl.univ-avignon.fr" target="#_blank">
      <?php echo __('A free software from the University of Avignon'); ?>
      </a><br />
      <a href="http://www.uni-hildesheim.de/rz/" target="#_blank">
      <?php echo __('and the University of Hildesheim') ?>
      </a>
      <a href="http://www.uni-hildesheim.de/index.php?id=impressum" target="#_blank">
      (<?php echo __('Imprint') ?>)
      </a>
    <?php endif ?>

  </footer>
