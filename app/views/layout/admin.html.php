<!DOCTYPE html>
<html>
  <head>
    <?php
    $styles = array('admin'); // add admin specific css
    $scripts = array('jquery.paginate.min');
    include('_html.head.php');
    ?>
  </head>
  <body id="admin">
    <?php
    echo partial ('layout/_header.php', (isset ($fz_user) ?
      array('fz_user' => $fz_user) : array()));
    ?>
    <div id="content">
      <nav>
        <ul>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin')), __('Dashboard'))
            ?>
          </li>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin/users')), __('Users'))
            ?>
          </li>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin/files')), __('Files'))
            ?>
          </li>
          <li>
            <?php
            echo a(array('href'=>url_for ('admin/statistics')),
              __('Statistics'));
            ?>
          </li>
        </ul>
      </nav>
      <article>
        <?php echo $content ?>
      </article>
      <div class="clearboth"></div>
    </div>
    <?php
    echo partial ('layout/_footer.php', (isset ($fz_user) ?
      array('fz_user' => $fz_user) : array()));
    ?>
    <div id="modal-background"></div>
  </body>
</html>
