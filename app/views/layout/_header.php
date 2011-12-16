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
  if (fz_config_get('looknfeel', 'help_url')) {
    array_push($user_nav, a( 
      array( 'href'=>url_for (fz_config_get('looknfeel', 'help_url')),
      'class'=>'help',
      'title'=>__('Find help'),
      'target'=>'#_blank' ), 
      __( 'Find help' ) ) );
  }
  if (isset ($fz_user) && $fz_user->is_admin) {
    if ( strpos( $_SERVER['REQUEST_URI'], "/admin" ) !== false ) {
      array_push($user_nav, a(array('href'=>url_for ('/'),
        'title'=>__('Back')), __( 'Back' ) ) );
    }
    else {
      array_push($user_nav, a(array('href'=>url_for ('/admin'),
        'title'=>__('Administration')), __( 'Administration' ) ) );
    }
  }
  if (isset ($fz_user)) {
    array_push($user_nav, '<span title="'.$fz_user->email.'">'.$fz_user." (".$fz_user->username.")</span>");
    array_push($user_nav, a(array('href'=>'#','id'=>'switch-ui',
      'title'=>__('Switch user interface') ), '&nbsp;') );
    array_push($user_nav, a(array('href'=>url_for ('/logout'),
      'id'=>'logout', 'title'=>__('Log out') ), '&nbsp;' ) );
  }
  echo implode(' | ', $user_nav);
  ?>
  </p>
</header>

<script type="text/javascript">
  $(document).ready (function () {
    $('#switch-ui').click(function() {
      if ($.cookie('ui-switch') == 'simple')
      {
        $.cookie('ui-switch', '', { expires: 30 });
      } else {
        $.cookie('ui-switch', 'simple', { expires: 30});
      }
      location.reload();
    });
  });
</script>