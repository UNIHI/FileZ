<?php if ($isInternal): ?>
<p>
  <a href="<?php echo url_for ('/admin/users/new') ?>" class="awesome">
    <?php echo __('Create a new user') ?>
  </a>
</p>
<?php endif ?>

<?php if ($numberOfUsers > fz_config_get('app','items_per_page')): ?>
<div id="pagination"></div>
<?php endif ?>

<table id="user_list" class="data">
  <tr id="table-header-row">
    <th class="sorting-enabled" data-order="name"><?php echo __('Name') ?></th>
    <th class="sorting-enabled" data-order="email"><?php echo __('eMail') ?></th> 
    <th class="sorting-enabled" data-order="role"><?php echo __('Role') ?></th>
    <th><?php echo __('Valid files') ?> (MB)</th>
    <th><?php echo __('Expired files') ?> (MB)</th>
    <th><?php echo __('Disk usage') ?> (MB)</th>
    <th><?php echo __('Actions') ?></th>
  </tr>
  <tr id="table-filter-row">
    <td id="name-filter">
      <input id="name-filter-input" type="text" value="">
      <img id="name-filter-clear-input" 
        src="../resources/images/icons/clear-input.png">
    </td>
    <td id="email-filter">
    </td>
    <td id="role-filter">
    </td>
    <td id="valid-files-count-filter">
    </td>
    <td id="expired-files-count-filter">
    </td>
    <td id="disk-usage-filter">  
    </td>
    <td>
    </td>
  </tr>
<?php 
foreach ($users as $user_item)
  echo partial('user/_user_row.php', array ('user_item' => $user_item));
?>
</table>

<script type="text/javascript">
  $(document).ready(function() {
    if ($.cookie('usersNameFilter') != undefined)
      $('#name-filter-input').val($.cookie('usersNameFilter'));
      
    var selectedColumn = 
      '#table-header-row th.sorting-enabled[data-order=' + $.cookie('usersOrder') + ']';
    if ($.cookie('usersOrderDirection') == 'asc')
      $(selectedColumn).append('<img src="../resources/images/icons/asc.png">');
    else
      $(selectedColumn).append('<img src="../resources/images/icons/desc.png">');
    
    // remove fitler and reload page
    $('#name-filter-clear-input').on('click', function() {
      $('#name-filter-input').val('');
      $.cookie('usersNameFilter',null);
      location.reload();
    });
    
    // reload page when pressing enter in name-filter input
    $('#name-filter-input').on('keypress', function(e) {
      var code = (e.keyCode ? e.keyCode : e.which);
      if (code == 13) location.reload();
    });
    
    // save filter value and reload page
    $('#name-filter-input').on('change', function() {
      $.cookie('usersNameFilter',$('#name-filter-input').val(), { expires: 30} );
      location.reload();
    });
    
    $('#table-header-row th.sorting-enabled',this).on('click', function() {
      if ($.cookie('usersOrder') == $(this).data('order') && $.cookie('usersOrderDirection') == 'asc')
        $.cookie('usersOrderDirection','desc', { expires: 30} );
      else if ($.cookie('usersOrder') == $(this).data('order') && $.cookie('usersOrderDirection') == 'desc')
        $.cookie('usersOrderDirection','asc', { expires: 30} );
      else
        $.cookie('usersOrderDirection','asc', { expires: 30} );
      $.cookie('usersOrder',$(this).data('order'), { expires: 30} );
      location.reload();
    });
    
    $("#pagination").paginate({
      count                  : <?php echo ceil($numberOfUsers / fz_config_get('app','items_per_page')) ?>,
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
          filesOrder  : $.cookie('usersOrder'),
          filesOrderDirection: $.cookie('usersOrderDirection'),
          filesNameFilter : $.cookie('usersNameFilter')
        };
        $.post('users', postData, function(data) {
          if (data.status == 'success') {
            $('tr.table-data-row').remove();
            $('#table-filter-row').after(data.items);
          }
        }, 'json');
      }
    });
  });
</script>