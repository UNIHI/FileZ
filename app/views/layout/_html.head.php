<title>FileZ</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="shortcut Icon" href="favicon.ico" type="image/x-icon" />
<?php
if (!isset($styles) || !is_array($styles))
  $styles = array();
array_unshift($styles,
'html5-reset', 'jquery-ui-1.8.16.custom', 'jquery.qtip', 'main',
'jquery.autocomplete'
);
if (fz_config_get ('looknfeel', 'custom_css', '' != ''))
  array_unshift($styles, fz_config_get ('looknfeel', 'custom_css'));
foreach ($styles as $style)
  echo '<link rel="stylesheet" href="'
    . public_url_for ('resources/css/'. $style . '.css')
    .'" type="text/css" media="all" />';
?>

<?php
if (!isset($scripts) || !is_array($scripts))
  $scripts = array();
array_unshift($scripts,
  'jquery-1.7.1.min', 
  'jquery.form.min', 
  'jquery.progressbar.min',
  'jquery.autocomplete.min', 
  'jquery.cookie.min',
  'jquery-ui-1.8.16.custom.min', 
  'jquery.qtip.pack',
  'i18n/jquery.ui.datepicker-'.option ('locale')->getLanguage (),
  'jquery.zclip.min', 'filez');
foreach($scripts as $script)
  echo '<script type="text/javascript" src="'
    . public_url_for ('resources/js/' . $script . '.js') . '"></script>';
?>
<!--[if lt IE 9]>
<script type="text/javascript" 
  src="<?php echo public_url_for ('resources/js/html5.js') ?>"></script>
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