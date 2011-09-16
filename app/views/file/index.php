<div id="pagination"></div>
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
      '<img src="resources/images/icons/remove.png">'.__('Delete'));
    ?>
    </td>
<?php endforeach ?>
</table>
<script type="text/javascript">
  $(document).ready (function () {
    $("#pagination").paging(100, {
      format: "[(qq-) ncnnn (-pp)]",
      onFormat: function(type) {
        switch (type) {
          case 'block':
            if (!this.active)
              return '<span class="disabled">' + this.value + '</span>';
            else if (this.value != this.page)
              return '<em><a href="#' + this.value + '">' + this.value + '</a></em>';
            return '<span class="current">' + this.value + '</span>';
          case 'next':
            if (this.active) {
                return '<a href="#' + this.value + '" class="next">Next »</a>';
            }
            return '<span class="disabled">Next »</span>';
          case 'prev':
            if (this.active) {
              return '<a href="#' + this.value + '" class="prev">« Previous</a>';
            }
            return '<span class="disabled">« Previous</span>';
          case 'first':
            if (this.active) {
              return '<a href="#' + this.value + '" class="first">|<</a>';
            }
            return '<span class="disabled">|<</span>';
          case 'last':
            if (this.active) {
              return '<a href="#' + this.value + '" class="prev">>|</a>';
            }
            return '<span class="disabled">>|</span>';
          case 'fill':
            if (this.active) {
              return "...";
            }
        }
      },
      onSelect: function(page) {
        Spinner.spin();
        $.ajax({
          'url': '/data.php?start=' + this.slice[0] + '&end=' + this.slice[1] + '&page=' + page,
          'success': function(data) {
            Spinner.stop();
            // content replace
          }
        });
      }
    });  
  });

</script>