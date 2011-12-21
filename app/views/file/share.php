<div id="share-modal" class="fz-modal">
  <p class="instruction">
    <?php echo __('Give this link to the person you want to share this file with') ?>
  </p>
  <p id="share-link">
    <a href="<?php echo $file->getDownloadUrl()?>">
      <?php echo $file->getDownloadUrl()?>
    </a>
  </p>
  <p class="instruction">
    <?php echo __('or share using:') ?>
  </p>
  <ul id="share-destinations">
      <li class="email"><a href="<?php echo $file->getDownloadUrl()?>/email">
        <?php echo __('your email') ?></a>
      </li>
      <li class="facebook">
        <a href="http://www.facebook.com/sharer.php?u=<?php echo $file->getDownloadUrl()?>&t=<?php echo $file?>" target="_blank">
          <?php echo __('Facebook') ?>
        </a>
      </li>
      <li class="twitter">
        <a href="http://twitter.com/home?status=<?php echo $file?> <?php echo $file->getDownloadUrl()?>" target="_blank">
          <?php echo __('Twitter') ?>
        </a>
      </li>
  </ul>
  <div class="cleartboth"></div>
</div>