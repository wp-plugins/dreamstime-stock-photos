<p>Hi <?php echo $this->user->Username?>! <br />
  You have
<?php
  $credits = intval($this->user->Credits);
  $subscriptions = intval($this->user->Subscriptions);
  if($credits > 0 &&  $subscriptions> 0) {
    echo $credits. ' credits and ' .$subscriptions .' subscription downloads ';
  } elseif($credits > 0) {
    echo $credits. ' credits ';
  } elseif($subscriptions > 0) {
    echo $subscriptions .' subscription downloads ';
  } else {
    echo '0 credits';
  }
?>

  available in your account. <a href="http://www.dreamstime.com/credits" target="_blank"><?php if ($credits > 0) { ?>Buy More Credits<?php } else { ?>Buy Credits<?php } ?></a>
</p>