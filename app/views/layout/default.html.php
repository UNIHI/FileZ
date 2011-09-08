<!DOCTYPE html>
<html>
  <head>
    <?php
    include('/app/views/layout/_html.head.php');
    ?>
  </head>
  <body onLoad="checkPortal();">

    <?php
    echo partial (
      'layout/_header.php',
      (isset ($user) ? array('user' => $user) : array())
    );
    ?>

    <article>
      <?php echo $content ?>
    </article>

    <?php
    echo partial (
      'layout/_footer.php',
      (isset ($user) ? array('user' => $user) : array())
    );
    ?>

    <div id="modal-background"></div>
  </body>
</html>
