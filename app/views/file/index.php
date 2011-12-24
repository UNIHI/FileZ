<?php if ($numberOfFiles > fz_config_get('app','items_per_page')): ?>
<div id="pagination"></div>
<?php endif ?>

<table id="file_list" class="data">
  <tr id="table-header-row">
    <th class="sorting-enabled" data-order="name"><?php echo __('Name') ?></th>
    <th data-order="uploader"><?php echo __('Uploader') ?></th>
    <th class="sorting-enabled" data-order="availability"><?php echo __('Availability') ?></th>
    <th class="sorting-enabled" data-order="size"><?php echo __('Size') ?></th>
    <th class="sorting-enabled" data-order="downloadCounter"><?php echo __('Download counter') ?></th>
    <th data-order="actions"><?php echo __('Actions') ?></th>
  </tr>
  <tr id="table-filter-row">
    <td id="name-filter">
      <input id="name-filter-input" type="text" value="">
      <img id="name-filter-clear-input" 
        src="../resources/images/icons/clear-input.png">
    </td>
    <td id="upload-filter">
    </td>
    <td id="availability-filter">
    </td>
    <td id="size-filter">
    </td>
    <td id="download-counter-filter">
    </td>
    <td id="actions-filter">  
      <input id="is-deleted" type="checkbox" name="isDeleted" value="true"
      <?php 
      if (array_key_exists('isDeleted', $_COOKIE) && $_COOKIE["isDeleted"] == 'true')
        echo 'checked="checked"';
      ?>>
      <label for="is-deleted">
        <?php echo __('Include already deleted files') ?>
      </label>
    </td>
  </tr>

<?php foreach ($files as $file)
  echo partial('admin/_file_row.php', array ('file' => $file));
?>
</table>

<script type="text/javascript">
  $(document).ready(function() {
    if ($.cookie('filesNameFilter') != undefined)
      $('#name-filter-input').val($.cookie('filesNameFilter'));
      
    var selectedColumn = 
      '#table-header-row th.sorting-enabled[data-order=' + $.cookie('filesOrder') + ']';
    if ($.cookie('filesOrderDirection') == 'asc')
      $(selectedColumn).append('<img src="../resources/images/icons/asc.png">');
    else
      $(selectedColumn).append('<img src="../resources/images/icons/desc.png">');
    
    // remove fitler and reload page
    $('#name-filter-clear-input').on('click', function() {
      $('#name-filter-input').val('');
      $.cookie('filesNameFilter',null);
      location.reload();
    });
    
    // reload page when pressing enter in name-filter input
    $('#name-filter-input').on('keypress', function(e) {
      var code = (e.keyCode ? e.keyCode : e.which);
      if (code == 13) location.reload();
    });
    
    // save filter value and reload page
    $('#name-filter-input').on('change', function() {
      $.cookie('filesNameFilter',$('#name-filter-input').val(), { expires: 30} );
      location.reload();
    });
    
    $('#table-header-row th.sorting-enabled',this).on('click', function() {
      if ($.cookie('filesOrder') == $(this).data('order') && $.cookie('filesOrderDirection') == 'asc')
        $.cookie('filesOrderDirection','desc', { expires: 30} );
      else if ($.cookie('filesOrder') == $(this).data('order') && $.cookie('filesOrderDirection') == 'desc')
        $.cookie('filesOrderDirection','asc', { expires: 30} );
      else
        $.cookie('filesOrderDirection','asc', { expires: 30} );
      $.cookie('filesOrder',$(this).data('order'), { expires: 30} );
      location.reload();
    });
    
    $('#is-deleted', this).on('click', function() {
      if ($(this).attr('checked') != undefined) {
        $.cookie('isDeleted', 'true', { expires: 30} );
      } else
        $.cookie('isDeleted', 'false', { expires: 30} );
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
          isDeleted   : $.cookie('isDeleted'),
          filesOrder  : $.cookie('filesOrder'),
          filesOrderDirection: $.cookie('filesOrderDirection'),
          filesNameFilter : $.cookie('filesNameFilter')
        };
        $.post('files', postData, function(data) {
          if (data.status == 'success') {
            $('tr.table-data-row').remove();
            $('#table-filter-row').after(data.items);
          }
        }, 'json');
      }
    });
  });
</script>
