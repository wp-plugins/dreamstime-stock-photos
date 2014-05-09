<form method="post" class="dt_form" id="search-form">
  <input type="hidden" name="tab" value="dreamstime" />
  <input type="hidden" name="dt_tab_index" value="1" />
  <label>Search: </label><input type="hidden" name="action" value="search" />
  <input type="text" name="keywords" id="keywords" value="<?php echo $this->keywords?>" />
  <input type="submit" value="Search Images" />
</form>


<?php
if(!$this->images) {
//  return;
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
<!--    var index = --><?php //echo intval($_POST['lightbox_id']) ? 1 : ($this->keywords && $this->lightboxId ? 2 : ($this->keywords ? 1 : 0))?><!--;-->
    $( "#dt_search_results" ).tabs({
//      active: index,
      activate: function(event, ui){
        var activeTab = ui.newPanel.attr('id');
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: {action: 'ajxSaveActiveImagesTab', tab: activeTab}
        });
      }
    });

    $('.dt_progressbar').progressbar({value: false });

    dt_more('featured', {action: 'more', type: 'featured'});
    dt_more('free', {action: 'more', keywords: $('#keywords').val(), type: 'free'});
    dt_more('paid', {action: 'more', keywords: $('#keywords').val(), type: 'paid'});

    <?php if(!$this->images['paid']['count'] && !$this->images['free']['count'] && !$this->isSearchFormUsed):?>
      $('#keywords').val('');
    <?php endif;?>


    <?php if(!$this->isSearchFormUsed ): ?>
      <?php if(is_null($this->keywords)):?>
        initialSearch();
      <?php else:?>
        initialSearch('<?php echo $this->keywords?>');
      <?php endif;?>
    <?php endif;?>


    activateImagesTab('<?php echo $this->activeImagesTab?>');

    function activateImagesTab(lastActive){
      var tabs = $('#dt_search_results ul li').map( function() {
        return $(this).attr('aria-controls');
      }).get();
      var index = 0;
      //if free and paid, select last active from those two, or paid if last active is not paid or free
      if(tabs.indexOf('paid') != -1 && tabs.indexOf('free') != -1) {
        if(lastActive == 'paid' || lastActive == 'free') {
          index = tabs.indexOf(lastActive);
        } else {
          index = tabs.indexOf('paid');
        }
        //activate the other tab if current is empty (if the other is not empty)
        var isPaidPanelEmpty = $('#dt_search_results div#paid').find('.no_images').length;
        var isFreePanelEmpty = $('#dt_search_results div#free').find('.no_images').length;
        var currentPanel = $($('#dt_search_results div.dt_images_panel').get(index));
        var isCurrentPanelEmpty = currentPanel.find('.no_images').length;
        if(isCurrentPanelEmpty) {
          if(currentPanel.attr('id') == 'paid' && !isFreePanelEmpty) index = tabs.lastIndexOf('free');
          if(currentPanel.attr('id') == 'free' && !isPaidPanelEmpty) index = tabs.lastIndexOf('paid');
        }
      } else {
        index = tabs.indexOf(lastActive);
        if(index == -1) {
          index = 0;
        }
      }
      $( "#dt_search_results" ).tabs({active: index});
    }
  });

</script>





