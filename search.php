
<form method="post" class="dt_form" id="search-form">
  <input type="hidden" name="tab" value="dreamstime" />
  <input type="hidden" name="dt_tab_index" value="1" />
  <label>Search: </label><input type="hidden" name="action" value="search" />
  <input type="text" name="keywords" id="keywords" value="<?php echo $this->keywords?>" />
  <input type="submit" value="Search Images" />
</form>


<?php
if(!$this->images) {
  return;
}
?>

<div id="dt_search_results">
    <ul>
      <?php if($this->images['featured']):?><li><a href="#featured">Editors' Choice Images</a></li><?php endif;?>
      <?php if($this->lightboxId):?><li><a href="#lightboxes">My lightboxes</a></li><?php endif;?>
      <?php if($this->images['paid']):?><li><a href="#paid">Commercial Stock Images</a></li><?php endif;?>
      <?php if($this->images['free']):?><li><a href="#free">Free Stock Images</a></li><?php endif;?>

    </ul>

<?php
?>

  <?php if($this->images['featured']) echo $this->renderImagesPanel($this->images['featured']['images'], $this->images['featured']['count'], 'featured')?>
  <?php if($this->lightboxId) include 'lightboxes.php'?>
  <?php if($this->images['paid']) echo $this->renderImagesPanel($this->images['paid']['images'], $this->images['paid']['count'], 'paid')?>
  <?php if($this->images['free']) echo $this->renderImagesPanel($this->images['free']['images'], $this->images['free']['count'], 'free')?>

  <div class="dt_clear"></div>
</div>

<script type="text/javascript">
  jQuery(function($){
    var index = <?php echo intval($_POST['lightbox_id']) ? 1 : ($this->keywords && $this->lightboxId ? 2 : ($this->keywords ? 1 : 0))?>;
    $( "#dt_search_results" ).tabs({ active: index });
  });
</script>





