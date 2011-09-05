<!DOCTYPE html>
<html>
  <head>
    <title>FileZ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut Icon" href="favicon.ico" type="image/x-icon" />
    <?php
    $styles = array(
      'html5-reset', 'jquery-ui-1.8.16.custom', 'jquery.qtip', 'main',
      'jquery.autocomplete','admin'
    );
    if (fz_config_get ('looknfeel', 'custom_css', '' != ''))
      array_unshift($styles, fz_config_get ('looknfeel', 'custom_css'));
    foreach ($styles as $style)
      echo '<link rel="stylesheet" href="'
        . public_url_for ('resources/css/'. $style . '.css')
        .'" type="text/css" media="all" />';
    ?>

    <?php
    $scripts = array(
      'jquery-1.6.2.min', 'jquery.form.min', 'jquery.progressbar.min',
      'jquery.autocomplete.min', 'jquery.cookie.min',
      'jquery-ui-1.8.16.custom.min', 'jquery.qtip.pack',
      'i18n/jquery.ui.datepicker-'.option ('locale')->getLanguage (),
      'jquery.zclip.min', 'filez');
    foreach($scripts as $script)
      echo '<script type="text/javascript" src="'
        . public_url_for ('resources/js/' . $script . '.js') . '"></script>';
    ?>
    <!--[if lt IE 9]>
    <script type="text/javascript" src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
    <![endif]-->
    <script type="text/javascript">
      function checkPortal() {
        if (top.location != self.document.location) {
          //if filez is displayed through a web portal hide logos and logout box
          document.getElementById('your-logo').style.display = 'none';
          document.getElementById('filez-logo').style.display = 'none';
          document.getElementById('auth-box').style.display = 'none';
        }
      }
    </script>
  </head>
  <body id="admin">

    <?php echo partial ('layout/_header.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <div id="content">

      <nav>
        <ul>
          <li><a href="<?php echo url_for ('admin') ?>"><?php echo __('Dashboard') ?></a></li>
          <li><a href="<?php echo url_for ('admin/users') ?>"><?php echo __('Users') ?></a></li>
          <li><a href="<?php echo url_for ('admin/files') ?>"><?php echo __('Files') ?></a></li>
          <li><a href="<?php echo url_for ('admin/config') ?>"><?php echo __('Config') ?></a></li>
          <li><a href="<?php echo url_for ('admin/statistics') ?>"><?php echo __('Statistics') ?></a></li>
        </ul>
      </nav>
      <article>
        <?php echo $content ?>
      </article>

      <div class="clearboth"></div>
    </div>

    <?php echo partial ('layout/_footer.php', (isset ($fz_user) ? array('fz_user' => $fz_user) : array())); ?>

    <div id="modal-background"></div>

    <script type="text/javascript">
      // small snippet to select an item in the menu
      $(document).ready (function () {
        $('nav a').each (function () {
          console.log (document.location.href.indexOf ($(this).attr ('href')));
          if (document.location.href.indexOf ($(this).attr ('href')) != -1) {
            $('nav .selected').removeClass ('selected');
            $(this).addClass ('selected');
          }
        });
      });
    </script>
  </body>
</html>
