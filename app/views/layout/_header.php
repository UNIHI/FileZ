
    <header>
      <h1>
        <?php if (fz_config_get ('looknfeel', 'your_logo', '') != ''): ?>
          <span id="your-logo">
            <img src="<?php echo public_url_for (fz_config_get ('looknfeel', 'your_logo')) ?>"/>
          </span>
        <?php endif ?>
        <span id="filez-header">
          <a href="<?php echo public_url_for ('/') ?>" id="filez-logo">
            <img width="70" height="30" src="<?php echo public_url_for ('resources/images/filez-logo.png') ?>" title="filez" />
          </a>
          <?php echo __('Share files for a limited time.') ?>
        </span>
        <span style="display: block; clear: both;"></span>
      </h1>
      <?php if (array_key_exists ('notification', $flash)): ?>
      <p class="notif ok"><?php echo $flash ['notification'] ?></p>
      <?php endif ?>
      <?php if (array_key_exists ('error', $flash)): ?>
      <p class="notif error"><?php echo $flash ['error'] ?></p>
      <?php endif ?>
      <p id="auth-box">
      <?php
      $user_nav = array();
      if (fz_config_get('looknfeel', 'help_url'))
        array_push($user_nav,
          a( array( 'href'=>url_for (fz_config_get('looknfeel', 'help_url')),
            'class'=>'help','target'=>'#_blank' ), __( 'Find help' ) ) );
      if (isset ($fz_user) && $fz_user->is_admin) {
        array_push($user_nav, a(array('href'=>url_for ('/admin'),
          'title'=>__('Administration')), __( 'Administration' ) ) );
      }
      if (isset ($fz_user)) {
        array_push($user_nav, $fz_user->email);
        array_push($user_nav, a(array('href'=>url_for ('/logout'),
          'id'=>'logout', 'title'=>__('Log out') ), '&nbsp;' ) );
      }
      echo implode(' | ', $user_nav);
      ?>
      </p>
    </header>
