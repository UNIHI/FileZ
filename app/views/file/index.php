<?php if ($numberOfFiles > fz_config_get('app','items_per_page')): ?>

<div id="pagination"></div>
<input id="is-deleted" type="checkbox" name="isDeleted" value="true"
  <?php 
  if (array_key_exists('isDeleted', $_COOKIE) && $_COOKIE["isDeleted"] == 'true')
    echo 'checked="checked"';
  ?>>
<?php echo __('Include already deleted files') ?>

<?php endif ?>

<table id="file_list" class="data">
  <tr id="table-header-row">
    <th><?php echo __('Name') ?></th>
    <th><?php echo __('Uploader') ?></th>
    <th><?php echo __('Availability') ?></th>
    <th><?php echo __('Size') ?></th>
    <th><?php echo __('Download counter') ?></th>
    <th><?php echo __('Actions') ?></th>
  </tr>



<?php foreach ($files as $file): ?>
  <tr class="table-data-row">
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
  </tr>
<?php endforeach ?>
</table>
<script type="text/javascript">
  $(document).ready(function(){
    $('#is-deleted', this).on('click', function(){
      if ($(this).attr('checked') != undefined) {
        $.cookie('isDeleted', 'true', { expires: 30});
      } else
        $.cookie('isDeleted', 'false', { expires: 30});
      location.reload();
    });
    
    $("#pagination").paginate({
      count                  : <?php echo ceil($numberOfFiles / fz_config_get('app','items_per_page')) ?>,
      start                  : 1,
      display                : 7,
      border                 : true,
      border_color           : '#fff',
      text_color             : '#fff',
      background_color       : 'black',  
      border_hover_color     : '#ccc',
      text_hover_color       : '#000',
      background_hover_color : '#fff', 
      images                 : false,
      mouse                  : 'press',
      onChange               : function(page) {
        var postData = {
          currentPage : page,
          isDeleted   : $.cookie('isDeleted')
        };
        $.post('files', postData, function(data) {
          if (data.status == 'success') {
            $('tr.table-data-row').remove();
            $('#table-header-row').after(data.items);
          }
        }, 'json');
      }
    });
  });
</script>
