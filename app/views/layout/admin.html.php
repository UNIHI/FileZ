<!DOCTYPE html>
<html>
  <head>
    <?php
    $styles = array('admin','jquery.paginate'); // add admin specific css
    $scripts = array('jquery.paginate'); // and js
    include('/app/views/layout/_html.head.php');
    ?>
  </head>
  <body id="admin">
    <?php
    echo partial ('layout/_header.php', (isset ($fz_user) ?
    array('fz_user' => $fz_user) : array()));
    ?>
    <div id="content">
      <div id="admin-tabs">
        <ul>
          <li>
            <?php echo a(array('href'=>url_for ('admin')), __('Dashboard')) ?>
          </li>
          <li>
            <?php echo a(array('href'=>url_for ('admin/users')), __('Users')) ?>
          </li>
          <li>
            <?php echo a(array('href'=>url_for ('admin/files')), __('Files')) ?>
          </li>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin/config')),
              __('Config'));
            ?>
          </li>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin/statistics')),
              __('Statistics'));
            ?>
          </li>
        </ul>
      </div>
      <div class="clearboth"></div>
    </div>
    <?php
    echo partial ('layout/_footer.php', (isset ($fz_user) ?
      array('fz_user' => $fz_user) : array()));
    ?>
    <div id="modal-background"></div>

    <script type="text/javascript">
      $(document).ready (function () {
        var tab_id_cookie = $.cookie('admin-tab');
        $('#admin-tabs').tabs({
          //ajaxOptions: {
          //  error: function( xhr, status, index, anchor ) {
          //    $( anchor.hash ).html( 'Could not load this tab.' );
          //  }
          //},
          cookie: { expires: 1, name: 'admin-tab' },
          idPrefix: 'admin-tab-',
          load: function(event, ui) {
            $('a', ui.panel).click( function() {
              $( ui.panel ).load(this.href);
              $( ui.panel ).find('.tab-loading').remove();
              return false;
            });
          },
          cache:true,
          selectd: tab_id_cookie,
          select: function ( event, ui ) {
            var $panel = $(ui.panel);
            if ($panel.is(':empty'))
              $panel.append('<div class="tab-loading">Loading...</div>');
          }
        });

        //TODO: preloading does not seem to work properly yet.
        //var total = $('#admin-tabs').find('li').length;
        //var currentLoadingTab = 1;
        //$('#admin-tabs').bind('tabsload', function() {
        //  if (currentLoadingTab < total) {
        //      $('#admin-tabs').tabs('load',currentLoadingTab);
        //  } else {
        //      $('#admin-tabs').unbind('tabsload');
        //  }
        //  currentLoadingTab++;
        //}).tabs('load',currentLoadingTab);
      });
    </script>
  </body>
</html>
