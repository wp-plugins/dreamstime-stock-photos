<form method="post" class="dt_form" id="search-form">
  <input type="hidden" name="tab" value="dreamstime" />
  <input type="hidden" name="dt_tab_index" value="1" />
  <label>Search: </label><input type="hidden" name="action" value="search" />
  <input type="text" name="keywords" id="keywords" value="<?php echo $this->keywords?>" />
  <input type="submit" value="Search Images" />
</form>


<div id="dt_search_results">
    <ul>
      <?php if($this->images['featured']):?><li><a href="#featured">Editors' Choice Images</a></li><?php endif;?>
      <?php if($this->lightboxId):?><li><a href="#lightboxes">My Lightboxes</a></li><?php endif;?>
      <?php if($this->images['paid']):?><li><a href="#paid">Commercial Stock Images</a></li><?php endif;?>
      <?php if($this->images['free']):?><li><a href="#free">Free Stock Images</a></li><?php endif;?>
      <?php if($this->images['downloaded']):?><li><a href="#downloaded">My Downloads</a></li><?php endif;?>
      <?php if($this->images['uploaded']):?><li><a href="#uploaded">My Images</a></li><?php endif;?>

    </ul>

<?php
?>

  <?php if($this->images['featured']) echo $this->renderImagesPanel($this->images['featured']['images'], $this->images['featured']['count'], 'featured')?>
  <?php if($this->lightboxId) include 'lightboxes.php'?>
  <?php if($this->images['paid']) echo $this->renderImagesPanel($this->images['paid']['images'], $this->images['paid']['count'], 'paid')?>
  <?php if($this->images['free']) echo $this->renderImagesPanel($this->images['free']['images'], $this->images['free']['count'], 'free')?>
  <?php if($this->images['downloaded']) echo $this->renderImagesPanel($this->images['downloaded']['images'], $this->images['downloaded']['count'], 'downloaded')?>
  <?php if($this->images['uploaded']) echo $this->renderImagesPanel($this->images['uploaded']['images'], $this->images['uploaded']['count'], 'uploaded')?>

  <div class="dt_clear"></div>
</div>


<script type="text/javascript">
  jQuery(function($){
    $( "#dt_search_results" ).tabs({
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
    dt_more('downloaded', {action: 'more', type: 'downloaded'});
    dt_more('uploaded', {action: 'more', type: 'uploaded'});

    <?php if(!$this->images['paid']['count'] && !$this->images['free']['count'] && !$this->isSearchFormUsed):?>
      $('#keywords').val('');
    <?php endif;?>


    <?php if($action == 'search'):?>
      var isSearchPerfomed = true;
    <?php else:?>
      var isSearchPerfomed = false;
    <?php endif;?>

    activateImagesTab('<?php echo $this->activeImagesTab?>');

    function activateImagesTab(lastActive){
      var tabs = $('#dt_search_results ul li').map( function() {
        return $(this).attr('aria-controls');
      }).get();
      var index = 0;
      //if free and paid, select last active from those two, or paid if last active is not paid or free
      if(isSearchPerfomed && tabs.indexOf('paid') != -1 && tabs.indexOf('free') != -1) {
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





