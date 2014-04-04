<?php if($this->user):?>
  <div style="float:left" class="account_info"><?php include 'account_info.php'?></div>

  <form method="post" class="dt_form" style="float: right">
    <input type="hidden" name="tab" value="dreamstime" />
    <input type="hidden" name="dt_tab_index" value="2" />
    <input type="hidden" name="action" value="logout" />
    <input type="submit" value="Logout" />
  </form>


  <div class="dt_clear"></div>

  <div class="referral">
    <input type="checkbox" id="toggle-referral" <?php if(get_option('dreamstime_referral_state')) echo 'checked="checked"'?>>
    <label for="toggle-referral">Automatically append referral ID to the URLs</label> <span>(Include your Dreamstime referral ID in the image links, so that you earn money when people click on them)</span>
  </div>



<?php else: ?>
  <div class="account_panel">
    <h3>Login</h3>
    <form method="post" class="dt_form">
      <input type="hidden" name="tab" value="dreamstime" />
      <input type="hidden" name="dt_tab_index" value="2" />
      <input type="hidden" name="action" value="login" />
      <div><label>Username: </label><input type="text" name="username" value="<?php echo get_option('dreamstime_username')?>" /></div>
      <div><label>Password: </label><input type="password" name="password" autocomplete="off"/></div>
      <input type="submit" value="Login" />
    </form>
  </div>

  <div class="account_panel">
    <h3>Create Account</h3>
    <form method="post" class="dt_form">
      <input type="hidden" name="tab" value="dreamstime" />
      <input type="hidden" name="dt_tab_index" value="2" />
      <input type="hidden" name="action" value="createAccount" />
      <div>
        <label>Username: </label><input type="text" name="username" id="username" value="<?php echo $_POST['username']?>" autocomplete="off" />
        <a href="javascript:;" id="check-username">Check availability</a>
        <span id="availability"></span>
      </div>
      <div><label>Password: </label><input type="password" name="password" autocomplete="off" /></div>
      <div><label>Email: </label><input type="text" name="email" value="<?php echo $_POST['email']?>" autocomplete="off" /></div>
      <input type="submit" value="Create Account" />
    </form>
  </div>

  <div class="dt_clear"></div>
<?php endif; ?>


<script type="text/javascript">
  jQuery(function($){
    $('#check-username').click(function(){
      dt_checkUsername($('#username').val());
    });
    $('#toggle-referral').change(function(){
      dt_toggleReferral($(this).prop('checked') ? 1 : 0);
    });
  });

</script>