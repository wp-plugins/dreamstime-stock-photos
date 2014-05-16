<?php
$type = isset($image->Prices) ? 'paid' : 'free';
if($licenses = $image->Licenses) {
  foreach ($licenses as $license) {
    if($license->Default) {
      break;
    }
  }

  $prices = array();

  foreach ($image->Prices as $price) {
    if(($price->License == $license->License)) {
      $prices[$price->Size][$price->PriceUnit] = $price->Price;
    }
  }
}



?>

<div class="dt_image" rel="<?php echo $type .'-'.$image->Image->ImageId ?>">

  <?php if($this->user):?><div class="account_info"><?php include 'account_info.php'?></div><?php endif;?>

  <div class="image"><img src="<?php echo $image->Image->ThumbnailUrl?>" /></div>

  <div class="image-info">
    <h2><?php echo $image->Image->Title?></h2>
    <div>&copy; <a href="<?php echo $this->image->Image->AuthorUrl?>" target="dreamstime.com"><?php echo $image->Image->Author?></a></div>
    <div class="dt_image_downloads"><?php echo $image->Image->Downloads?> downloads</div>
    <div class="dt_image_views"><?php echo $image->Image->Views?> views</div>
  </div>

  <?php if(count($prices)) : ?>
    <div class="prices">
      <div class="license">
        License: <a href="<?php echo $license->Link?>" target="_blank"><?php echo $license->FullName?></a>
      </div>
      <div class="prices">
        <table>
          <tr>
            <th colspan="2">Size</th>
            <th colspan="2">Price</th>
          </tr>
        <?php foreach ($image->Sizes as $size):?>
          <?php
            $priceUnit = key ($prices[$size->Size]);
            $price = $prices[$size->Size][$priceUnit];
            if($priceUnit == 'Credit') {
              $priceStr = $price == 0 ? '<span class="free-price">Free</span>' : ($price == 1 ? $price.' credit' : $price.' credits');
            }
            if($priceUnit == 'Subscription') {
              $priceStr = $price == 1 ? $price.' subscription download' : $price.' subscriptions downloads';
            }
          ?>
          <tr>
            <td><?php echo $size->FullName?></td>
            <td><?php echo ($size->Width && $size->Height) ? $size->Width .'x'. $size->Height : '--'?></td>
            <td><?php  echo $priceStr?></td>
            <td><input type="radio" name="size" <?php if($size->Default) echo 'checked="checked"'?> value="<?php echo $size->Size.'-'.$license->License ?>"></td>
          </tr>
        <?php endforeach;?>
        </table>
        <a href="javascript:;" onclick="moreSizes()" class="dt_more_sizes" style="display: none">More Sizes</a>
      </div>
    </div>
  <?php endif;?>
  <div class="dt_clear"></div>
  <div class="download">
    <input type="button" id="<?php echo $type.'-'.$image->Image->ImageId?>" class="dt_download" value="<?php if($type == 'free') echo 'Free '?>Download" />
  </div>
</div>



<script type="text/javascript">
  jQuery(function($){

    $('#image').dialog('option', 'title', '<?php echo $image->Image->Title?>')

    $('.dt_download').click(function(){
      <?php if($this->user):?>
      dt_downloadImage($(this).attr('id'), $('input[name="size"]:checked').val());
      <?php else: ?>
      dt_login();
      <?php endif; ?>
    });

    $('div.prices table tr').each(function(index, value){
      if(index > 2 && $(this).find('input[type="radio"]:checked').length == 0) {
        $(this).hide();
        $('.dt_more_sizes').show();
      }
    });

    moreSizes = function()
    {
      $('div.prices table tr').fadeIn(1000);
      $('.dt_more_sizes').hide();
    }


  });
</script>

