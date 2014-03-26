<?php

$image_tpl =
<<<HTML
<a href="javascript:;" id="{Type}-{ImageId}" title="{Title}"><img src="{ThumbnailUrl}" alt="{Title}" /></a>
<div class="title" title="{Title}">{Title}</div>
<div class="dt_image_downloads">{Downloads} downloads</div>
<div class="dt_image_views">{Views} views</div>
HTML;

?>


<?php if(count($images)):?>
    <?php foreach ($images as $image):?>
      <?php $image->Type = $type?>
      <div class="dt_image_th"><?php echo Dreamstime::renderTemplate($image_tpl, $image)?></div>
    <?php endforeach;?>
<?php else:?>
  <div class="no_images">No images!</div>
<?php endif;?>
