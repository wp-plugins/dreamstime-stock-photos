
<div id="lightboxes" class="dt_images_panel">
  <form method="post" class="dt_form" id="lightboxes-form">
    <input type="hidden" name="tab" value="dreamstime" />
    <input type="hidden" name="dt_tab_index" value="1" />
    <input type="hidden" name="action" value="getLightbox" />
    <label>Lightboxes</label>
    <select id="select-lightbox" name="lightbox_id">
      <?php foreach ($this->user->Lightboxes as $lightbox):?>
        <?php if($this->lightboxId == $lightbox->LightboxId) $the_lightbox = $lightbox;?>
        <option value="<?php echo $lightbox->LightboxId?>" <?php if($this->lightboxId == $lightbox->LightboxId) echo 'selected="selected"'?>><?php echo $lightbox->Name?></option>
      <?php endforeach; ?>
    </select>
  </form>


  <?php if($the_lightbox) echo $this->renderImagesPanel($the_lightbox->Images['images'], $the_lightbox->Images['count'], 'lightbox')?>
  <div class="dt_clear"></div>
</div>

<script type="text/javascript">
  jQuery(function($){
    $('#lightbox').removeClass('dt_images_panel');
    $('#select-lightbox').change(function(){
      $('#lightboxes-form').submit();
    });
  });
</script>