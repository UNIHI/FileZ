<!-- der paginator muss wissen wieviele datensätze vorhanden sind,
  um zu ermitteln, ob er überhaupt benötigt und falls ja, wieviele 
  links zu generieren sind -->
<?php if (count($files) > fz_config_get('app','items_per_page')): ?>
<div id="pagination">
  <ul>
    <?php
    $numPages = ceil(count($files) / fz_config_get('app','items_per_page'));
    for($i=0;$i<$numPages;$i++) {
      echo '<li class="item-page" href="#">' . ($i+1) . '</li>';
    }
    ?>
  </ul>
</div>
<?php endif ?>


<table id="file_list" class="data">
  <tr>
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Uploader') ?></th>
    <th><?php echo __('Availability') ?></th>
    <th><?php echo __('Size') ?></th>
    <th><?php echo __('Download counter') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>



<?php foreach ($files as $file): ?>
  <tr>
    <td>
      <?php
      echo a(array('href'=>$file->getDownloadUrl ()), $file->file_name);
      ?>
    </td>
    <td>
      <?php
      echo a(array('href'=>url_for ('/admin/users/'.$file->getUploader()->id)),
        h($file->getUploader ()));
      ?>
    </td>
    <td>
      <?php
      echo __r('from %from% to %to%', array ('from' =>
        $file->getAvailableFrom()->toString(option ('localeDateFormat')),
        'to' => '<b>'.$file->getAvailableUntil ()->toString (
        option ('localeDateFormat')).'</b>')
      );
      ?>
    </td>
    <td><?php echo $file->getReadableFileSize () ?></td>
    <td><?php echo (int) $file->download_count ?></td>
    <td>
    <?php
    echo a(array('href'=>$file->getDownloadUrl () . '/delete',
      'class'=>'admin-delete'),
      '<img src="../resources/images/icons/remove.png">'.__('Delete'));
    ?>
    </td>
<?php endforeach ?>
</table>

<script type="text/javascript">
  $(document).ready(function(){
    $('.item-page',this).on('click', function() {
      //alert ($(this).html());
      
      // Hier muss nun geprüft werden, ob nachträglich weitere dateien
      // hinzugefügt worden sind, falls ja, dann muss eventuell noch eine
      // weitere zahl am ende hinzukommen
      
      //der klick auf die datei soll dann ein request auslösen, dem
      //die notwendigen daten übergeben werden. das ist die zahl, die der
      // benutzer angeklickt hat. diese zahl wird dann durch eine funktion
      // auf dem server auf gültigkeit überprüft. ist die zahl gültig, dann
      // wird ein respose geschickt. der respose könnte 1. reines html sein
      // so dass de javascript ufnktion nur den inhalt ersetzen muss
      // oder es kann json sein, der erst auf dem client ausgewertet wird.
      
    });
  });
</script>
