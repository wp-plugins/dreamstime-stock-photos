<script type="text/javascript">
  var dt_tab_index = parseInt(<?php echo $_REQUEST['dt_tab_index'] ? $_REQUEST['dt_tab_index'] : 1 ?>);
  jQuery(function($){

    dt_tab(dt_tab_index);

    $( "#image" ).dialog({ modal: true, autoOpen: false, dialogClass: "image", width: 700, height: 'auto' , position: {my: 'top', at: 'top'} });

    $( "#dt_login" ).dialog({ modal: true, autoOpen: false, title: 'Login', dialogClass: "login", width: 'auto', height: 'auto' });

    $( "#loading" ).dialog({ modal: true, autoOpen: false, dialogClass: "loading", resizable: false  });



    $('#select-lightbox').change(function(){
      $('#lightboxes-form').submit();
    });
    dt_more('lightbox', {action: 'more', lightbox_id: <?php echo intval($this->lightboxId)?>, type: 'lightbox'});



    $(document.body).on('click', '.dt_image_th a' ,function(event){
      dt_getImage($(event.target).parent().attr('id'));
    });


    $('#create-account-link').click(function(){
      dt_tab(2);
      dt_dialog('dt_login', 'close');
      dt_dialog('image', 'close');
    });

    $( "#error" ).dialog({
      modal: true,
      autoOpen: false,
      resizable: false,
      title: 'Error!' ,
      dialogClass: 'msg-error',
      buttons: [
        {
          text: "OK",
          click: function() {
            $( this ).dialog( "close" );
          }
        }
      ]
    });

    <?php if($this->error):?>
    dt_error('<?php echo $this->error?>');
    <?php endif;?>

    $( document ).ajaxError(function( event, request, settings, exception ) {
      dt_loading('close');
      dt_error(exception);
    });

  });
</script>


<?php if($displayReviewNote):?>
<div class="updated review-note">
  <p>
    <div>
      Roses are red, violets are blue...</br>
      But what's a plugin without a review?</br></br>

      Please take a moment to rate our plugin and spread the word.</br></br>

      We really appreciate it!</br>
    </div>
    <form action="http://wordpress.org/support/view/plugin-reviews/dreamstime-stock-photos#topic" target="_blank" onsubmit="dt_review('add')">
      <input type="submit" value="Do it now !" id="review-add" />
    </form>
    <input type="button" value="Remind me later" onclick="dt_review('later')" />
    <input type="button" value="Don't ask again" onclick="dt_review('no')" />
  </p>
</div>
<?php endif;?>

<?php if(!$isUploadsDirAvailable):?>
  <div id="message" class="error">
    <p>
      In order to use the plugin, you have to make sure that the <code>wp-content/uploads/</code> folder exists and is writable by Wordpress. <br />
      How to do this? Check the official documentation page: <a href="http://codex.wordpress.org/Changing_File_Permissions" rel="nofollow" target="_blank">Changing File Permissions</a>.
    </p>
  </div>
<?php endif;?>

<div id="dt_tabs">
  <ul>
    <li><a href="#dt_search_tab">Search images on Dreamstime</a></li>
    <li><a href="#dt_my_account_tab">My Dreamstime Account</a></li>
  </ul>

  <div id="dt_search_tab">
    <?php include 'search.php'?>
  </div>


  <div id="dt_my_account_tab">
    <?php include 'account.php'?>
  </div>



  <div id="error"></div>


  <div id="image"></div>

  <div id="dt_login">
    <h2>Login or create an account to download the image</h2>
    <form id="login-form">
      <input type="hidden" name="action" value="ajxLogin">
      <div><label>Username: </label><input type="text" name="username" value="<?php echo $_COOKIE['dreamstime_username']?>" /></div>
      <div><label>Password: </label><input type="password" name="password" /></div>
      <input type="button" id="login_btn" value="Login" />
    </form>
    <a href="javascript:;" id="create-account-link">Create Account</a>
  </div>

  <div id="loading"><div class="dt_progressbar" style="display: block"></div></div>

</div>


<script type="text/javascript">
  jQuery(function($){

    <?php if(!$this->isSearchFormUsed ): ?>
      initialSearch('<?php echo $this->keywords?>');
    <?php endif;?>

  });
</script>
