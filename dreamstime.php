<?php

/**
 * Plugin Name: Dreamstime Stock Photos
 * Plugin URI: http://www.dreamstime.com/wordpress-photo-image-plugin
 * Description: Search and insert images into your posts and pages from Dreamstime's vast database of Free and Royalty-Free stock photos & illustrations.
 * Version: 2.0
 * Author: Dreamstime
 * Author URI: http://www.dreamstime.com
 * License: GPL2
 */

/*  Copyright 2014 Dreamstime

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('DREAMSTIME_STATIC_URL', plugin_dir_url(__FILE__).'static/' );



class Dreamstime
{
  public $data = array('images', 'user', 'postId', 'image', 'auth', 'keywords', 'lightboxId', 'settings', 'post', 'isSearchFormUsed', 'activeImagesTab');
  public $api;
  public $error;

  public function __construct()
  {
    global $wp_version;

//    register_activation_hook(__FILE__,array(&$this, 'activate'));

    $this->_startSession();
    add_action('wp_logout', array($this, 'logout'));


    if ($wp_version < 3.5) {
      if ( basename($_SERVER['PHP_SELF']) != "media-upload.php" ) return;
    } else {
      if ( basename($_SERVER['PHP_SELF']) != "media-upload.php" && basename($_SERVER['PHP_SELF']) != "post.php" && basename($_SERVER['PHP_SELF']) != "post-new.php" && basename($_SERVER['PHP_SELF']) != "admin-ajax.php") return;
    }


    add_filter("media_upload_tabs",array($this,"buildTab"));
    add_action("media_upload_dreamstime", array($this, "menuHandle"));
    add_action("admin_enqueue_scripts", array($this, "loadCssJs"));

    add_action('media_buttons', array($this, 'media_buttons'));

    add_action("wp_ajax_more", array($this, "more"));
    add_action("wp_ajax_getImage", array($this, "getImage"));
    add_action("wp_ajax_downloadImage", array($this, "downloadImage"));
    add_action("wp_ajax_ajxLogin", array($this, "ajxLogin"));
    add_action("wp_ajax_getLightbox", array($this, "getLightbox"));
    add_action("wp_ajax_checkUsername", array($this, "checkUsername"));
    add_action("wp_ajax_refreshAccountInfo", array($this, "refreshAccountInfo"));
    add_action("wp_ajax_toggleReferral", array($this, "toggleReferral"));
    add_action("wp_ajax_ajxSearch", array($this, "ajxSearch"));
    add_action("wp_ajax_ajxSaveActiveImagesTab", array($this, "ajxSaveActiveImagesTab"));
    add_action("wp_ajax_ajxReview", array($this, "ajxReview"));
    add_action("admin_head", array($this, "setCurrentPost"));

//unset($_SESSION['dreamstime']);
    require_once 'api.php';
    $this->api = new DreamstimeApi();

    if($postId = $_REQUEST['post_id']) {
      $this->postId = $postId;
    }
  }

  function setCurrentPost() {
    //get current post
    global $post;

    if($post instanceof WP_Post) {
      $lastPost = $this->post;
      if(!$lastPost instanceof WP_Post) {
        $this->post = $post;
        $this->keywords = null;
        $this->isSearchFormUsed = null;
        $this->emptySearchCache();
      } elseif($lastPost->ID != $post->ID) {
        $this->post = $post;
        $this->keywords = null;
        $this->isSearchFormUsed = null;
        $this->emptySearchCache();
      }
    }
    return;
//    $script = basename($_SERVER['PHP_SELF']);
//    if(!$this->keywords && $script == 'media-upload.php' && $_REQUEST['tab'] == 'dreamstime' && $this->post instanceof WP_Post) {
//      $postTitle = trim(strip_tags($this->post->post_title));
//      if($postTitle) {
//        $this->_search($postTitle);
//        return;
//      }
//      $postContent = trim(strip_tags($this->post->post_content));
//      if($postContent) {
//        return;
//      }
//
//    }
  }


  function media_buttons($editor_id = 'content')
  {
    $img = '<img src="'.DREAMSTIME_STATIC_URL.'logo.gif" class="dreamstime-logo" />';

    echo '<a href="#" id="dreamstime-media-button" class="button add_media" data-editor="' . esc_attr( $editor_id ) . '" title="' . esc_attr__( 'Add Dreamstime Media' ) . '">' . $img . 'Add Dreamstime Media' . '</a>';

  }

  public function loadCssJs() {

    wp_register_style( 'dreamstime', DREAMSTIME_STATIC_URL.'css/style.css' );
    wp_enqueue_style('dreamstime');
    wp_register_script('dreamstime', DREAMSTIME_STATIC_URL.'js/dreamstime.js', array('jquery'));
    wp_enqueue_script('dreamstime');

    $screen = get_current_screen();
    if($screen->id == 'media-upload') {
      wp_enqueue_style('jquery.ui.theme', '//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');

      wp_deregister_script('jquery-ui-core');
      wp_register_script('jquery-ui-core', '//code.jquery.com/ui/1.10.3/jquery-ui.js');
      wp_enqueue_script('jquery-ui-core');
    }

  }


  public function buildTab($tabs)
  {
    $tabs['dreamstime'] = "Add images from Dreamstime";
    return($tabs);
  }

  public function menuHandle()
  {
    // wp_iframe() adds css for "media" when callback function has "media_" as prefix
    return wp_iframe(array($this,"mediaController"));
  }

  public function mediaController()
  {
    media_upload_header();

    if($action = $_REQUEST['action']) {
      $this->$action();
    }

    $this->getEditorsChoiceImages();
    $this->getLightbox();
    $this->getMyDownloads();
    $this->getMyImages();

    $isUploadsDirAvailable = $this->isUploadsDirAvailable();
    $displayReviewNote = $this->displayReviewNote();
    include 'interface.php';

  }

  public function isUploadsDirAvailable() {
    $uploadDir = wp_upload_dir();
    return $uploadDir['error'] === false;
  }

  public function displayReviewNote() {
    if(!get_option('dreamstime_is_images_downloaded')) return false;

    if($option = get_option('dreamstime_review')) {
      if($option == 'later' && get_option('dreamstime_review_later') <= date('Y-m-d')) {
        return true;
      }
      return false;
    }
    return true;
  }

  public function search()
  {
    $keywords = sanitize_text_field($_REQUEST['keywords']);
    try {
      if(!strlen($keywords)) {
        throw new Exception("Search string can\'t be empty!");
      }
      $this->isSearchFormUsed = 1;
      $this->_search($keywords);
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }

  }

  public function ajxSearch()
  {
    $keywords = sanitize_text_field($_REQUEST['keywords']);

    try {
      if($keywords) {
        $this->_search($keywords);
        $images = $this->images;
      } else {
        $images = $this->images;
        unset($images['paid'], $images['free']);
        $this->keywords = '';
      }
      //remove images if no result
      if(!$images['paid']['count'] && !$images['free']['count']) {
        unset($images['paid'], $images['free']);
      }
      $this->images = $images;
      $this->__set('images', $images);
      ob_start();
      $action = 'search';
      include 'search.php';
      $search = ob_get_contents();
      ob_end_clean();
      echo $search;
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function ajxSaveActiveImagesTab()
  {
    $this->activeImagesTab = sanitize_text_field($_REQUEST['tab']);
    exit;
  }


  /**
   * Get more images using ajax calls
   */
  public function more()
  {
    $keywords = sanitize_text_field($_REQUEST['keywords']);
    $lightboxId = sanitize_text_field($_REQUEST['lightbox_id']);
    $type = sanitize_text_field($_REQUEST['type']);

    $api = new DreamstimeApi();

    try {
      switch ($type) {
        case 'free':
        case 'paid':
        case 'featured':
        case 'downloaded':
        case 'uploaded':
          $images = $this->images;
          $cachedImages = $images[$type]['images'];
          $page = floor(count($cachedImages) / $api->pageSize) + 1;
          switch ($type) {
            case 'free':
              $api->search($keywords, 'SearchFreeImages', $page);
              $api->apiCall();
              $newImages = $api->getSearchResults('SearchFreeImages');
              break;
            case'paid':
              $api->search($keywords, 'SearchPaidImages', $page);
              $api->apiCall();
              $newImages = $api->getSearchResults('SearchPaidImages');
              break;
            case 'featured':
              $newImages = $api->getEditorsChoiceImages($page);
              break;
            case 'downloaded':
              $newImages = $api->getDownloadedImages($page);
              break;
            case 'uploaded':
              $newImages = $api->getUploadedImages($page);
              break;
          }

          $images[$type]['images'] += $newImages['images'];
          $this->images = $images;
          break;
        case 'lightbox':
          $user = $this->user;
          $cachedImages = $user->Lightboxes[$lightboxId]->Images['images'];
          $page = floor(count($cachedImages) / $api->pageSize) + 1;
          $newImages = $api->getLightboxImages($lightboxId, $page);
          $user->Lightboxes[$lightboxId]->Images['images'] += $newImages['images'];
          $this->user = $user;
          break;
      }
      $images = $newImages['images'];
      include 'images.php';
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function getImage()
  {
    $image = sanitize_text_field($_REQUEST['image']);
    $image = explode('-', $image);

    try {
      $api = new DreamstimeApi();
      $response = $api->getImage($image[1], $image[0]);
      $image = $response['image'];
      if($accountInfo = $response['account_info']) {
        $user = $this->user;
        $merged = (object) array_merge((array) $user, (array) $accountInfo);
        $this->user = $merged;
      }
      $this->image = $image;
      include 'image.php';
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function downloadImage()
  {
    $image = sanitize_text_field($_REQUEST['image']);
    $size_license = sanitize_text_field($_REQUEST['size']);
    $image = explode('-', $image);
    $imageId = $image[1];
    $imageType = $image[0];
    if ($size_license) {
      $size_license = explode('-', $size_license);
      $size = (string)$size_license[0];
      $license = (string)$size_license[1];
    } else {
      $size = '';
      $license = '';
    }

    try {
      $user = $this->user;
      $hash = md5($imageId.$size.$license);
      if(!array_key_exists($hash, (array)$user->DownloadedUrls)) {
        $downloadUrl = $this->api->downloadImage($imageId, $imageType, $size, $license);
        $user->DownloadedUrls[$hash] = $downloadUrl;
        $this->user = $user;
      }
      $uploaded = $this->_uploadImage($hash);
      $attach_id = $this->_attachToPost($uploaded);
      update_option('dreamstime_is_images_downloaded', 1);
      echo $this->_media_upload_type_form('image', null, $attach_id);
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function login()
  {
    try {
      $this->_login($_POST['username'], $_POST['password']);
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function ajxLogin()
  {
    try {
      $this->_login($_POST['username'], $_POST['password']);
      $tabs = array();
      ob_start();
      $this->getLightbox();
      $this->getMyDownloads();
      $this->getMyImages();
      include 'search.php';
      $tabs['search'] = ob_get_contents();
      ob_end_clean();
      ob_start();
      include 'account.php';
      $tabs['account'] = ob_get_contents();
      ob_end_clean();
      echo json_encode($tabs);
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function refreshAccountInfo()
  {
    try {
      $user = $this->user;
      $refreshed = $this->api->getAccountInfo();
      $merged = (object) array_merge((array) $user, (array) $refreshed);
      $this->user = $merged;
      include 'account_info.php';
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function logout()
  {
    $_SESSION['dreamstime'] = array();
  }

  public function checkUsername()
  {
    $username = sanitize_text_field($_POST['username']);
    try {
      $response = $this->api->checkUsername($username);
      if($response['taken'] == 1) {
        echo '<div><span style="color: #e22882">Username already exists. Please choose another. Suggested: <a href="javascript:;" onclick="return setUsername(\''.$response['suggested'].'\');">'.$response['suggested'].'</a></span></div>';
      } else {
        echo '<span style="color: #66a800;">Username is available.</span>';
      }
    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function createAccount()
  {
    $username = sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);
    $email = sanitize_text_field($_POST['email']);

    try {
      $this->api->createAccount($username, $password, $email);
      $this->_login($username, $password);
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function getEditorsChoiceImages(){
    try {
      $images = $this->images;
      if(empty($images['featured'])) {
        $images['featured'] = $this->api->getEditorsChoiceImages();
        $this->images = $images;
      }
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function getLightbox()
  {
    if(!$this->user->Lightboxes) {
      return;
    }
    if(!$this->lightboxId) {
      $this->lightboxId = key($this->user->Lightboxes);
    }
    if($_REQUEST['lightbox_id'] ) {
      $this->lightboxId = sanitize_text_field($_REQUEST['lightbox_id']);
    }

    try {
      $images = $this->api->getLightboxImages($this->lightboxId);
      $user = $this->user;
      foreach ($user->Lightboxes as $key => &$lightbox) {
        if($key == $this->lightboxId) {
          $lightbox->Images = $images;
          break;
        }
      }
      $this->user = $user;
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function getMyDownloads(){
    if(!isset($this->user)) return;

    try {
      $images = $this->images;
      if($this->user->Downloads > 0 && empty($images['downloaded'])) {
        $downloaded = $this->api->getDownloadedImages();
        if($downloaded['count'] > 0) {
          $images['downloaded'] = $downloaded;
          $this->images = $images;
        }
      }
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function getMyImages(){
    if(!isset($this->user)) return;

    try {
      $images = $this->images;
      if($this->user->Uploads > 0 && empty($images['uploaded'])) {
        $uploaded = $this->api->getUploadedImages();
        if($uploaded['count'] > 0) {
          $images['uploaded'] = $uploaded;
          $this->images = $images;
        }
      }
    } catch (Exception $e) {
      $this->error = $e->getMessage();
    }
  }

  public function renderImagesPanel($images, $count, $type)
  {
    echo '<div id="'.$type.'" class="dt_images_panel" rel="'.$count.'">'."\n";
    include 'images.php';
    if($count > $this->api->pageSize) {
      echo '<div class="dt_clear"><a class="more" href="javascript:;">more</a></div>'."\n";
      echo '<div class="dt_progressbar dt_clear"></div>'."\n";
    }
    echo '</div>'."\n";
  }

  public static function renderTemplate($tpl, $search_replace)
  {
    $html = $tpl;
    foreach ($search_replace as $search=>$replace) :
      $str_search = '{'.$search.'}';
      $html = str_replace($str_search, $replace, $html);
    endforeach;
    return $html;
  }

  public function toggleReferral()
  {
    try {
      $state = sanitize_text_field($_REQUEST['state']);
      //save to db
      update_option('dreamstime_referral_state', $state);

    } catch (Exception $e) {
      header('HTTP/1.0 500 '. $e->getMessage());
    }
    exit;
  }

  public function ajxReview()
  {
    $action = sanitize_text_field($_REQUEST['data']);
    update_option('dreamstime_review', $action);
    if($action == 'later') {
      update_option('dreamstime_review_later', date('Y-m-d', strtotime('+3 days')));
    }
    exit;
  }

  public function emptySearchCache() {
    $images = $this->images;
    unset($images['free'], $images['paid']);
    $this->images = $images;
  }

  protected function _search($keywords = null) {
    $images = $this->images;
    $this->api->search($keywords, 'SearchFreeImages');
    $this->api->search($keywords, 'SearchPaidImages');
    $this->api->apiCall();
    $images['free'] = $this->api->getSearchResults('SearchFreeImages');
    $images['paid'] = $this->api->getSearchResults('SearchPaidImages');

    $this->images = $images;
    $this->keywords = $keywords;
  }
  protected function _login($username, $password)
  {
    $username = sanitize_text_field($username);
    $password = sanitize_text_field($password);
    $user = $this->api->login($username, $password);
    update_option('dreamstime_username', $username);
    $lightboxes = $this->api->getLightboxes();
    if(count($lightboxes)) {
      $user->Lightboxes = $lightboxes;
    }
    $this->user = $user;
    $settings = $this->settings;
    $settings = array_merge((array)$settings, $this->api->getCreditsLink());
    $this->settings = $settings;
  }
  protected function _media_upload_type_form($type = 'file', $errors = null, $id = null) {

//    media_upload_header();

    $post_id = $this->postId;

    $form_action_url = admin_url("media-upload.php?type=$type&tab=type&post_id=$post_id");
    $form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
    $form_class = 'media-upload-form type-form validate';

    if ( get_user_setting('uploader') )
      $form_class .= ' html-uploader';
    ?>

    <form enctype="multipart/form-data" method="post" action="<?php echo esc_url( $form_action_url ); ?>" class="<?php echo $form_class; ?>" id="<?php echo $type; ?>-form">
      <?php submit_button( '', 'hidden', 'save', false ); ?>
      <input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
      <?php wp_nonce_field('media-form'); ?>


      <script type="text/javascript">
        //<![CDATA[
        jQuery(function($){
          var preloaded = $(".media-item.preloaded");
          if ( preloaded.length > 0 ) {
            preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
          }
          updateMediaForm();
        });
        //]]>
      </script>
      <div id="media-items"><?php

        if ( $id ) {
          if ( !is_wp_error($id) ) {
            add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2);
            add_filter('attachment_fields_to_edit',array($this, 'attachment_fields_to_edit'),10,2);
            echo get_media_items( $id, $errors );
          } else {
            echo '<div id="media-upload-error">'.esc_html($id->get_error_message()).'</div></div>';
            exit;
          }
        }
        ?></div>

    </form>
  <?php
  }
  protected function _uploadImage($hash)
  {
    $user = $this->user;
    $source_url = $user->DownloadedUrls[$hash];

    add_filter('http_request_timeout', array($this,'setHttpRequestTimeout'));
    $response = wp_remote_get( $source_url );
    if($response instanceof WP_Error) {
//      $error = implode('<br>', reset($response->errors));
      throw new Exception('The request could not be completed at this time, please try again later.');
//      throw new Exception($error);
    }
    if(strpos($response['headers']['content-type'], 'application/download') === false) {
      //url probably expired
      unset($user->DownloadedUrls[$hash]);
      $this->user = $user;
      throw new Exception('Image download error! Incorrect headers...');
    }

    $data = $response['body'];
    if(!strlen($data)) {
      throw new Exception('Error: 0 bytes downloaded ...');
    }

    $filename = $this->_getFilenameFromHeaders($response['headers']);

    $uploaded = wp_upload_bits($filename, null, $data);
    if($uploaded['error']) {
      throw new Exception($uploaded['error']);
    }

    return $uploaded;
  }
  public function setHttpRequestTimeout(){
    return 30;
  }
  protected function _attachToPost($uploaded)
  {
    $filename = $uploaded['file'];
    $wp_filetype = wp_check_filetype($filename, null );

    $caption = '© <a href="'.$this->image->Image->AuthorUrl.'" target="dreamstime.com" class="dt-caption">' . $this->image->Image->Author.'</a> | ';
    $caption .= '<a href="'.$this->settings['image_credits_link'].'" title="'.$this->settings['image_credits_description'].'" target="dreamstime.com" class="dt-caption">'.$this->settings['image_credits_text'].'</a>';


    $attachment = array(
      'post_mime_type' => $wp_filetype['type'],
      'guid' => $uploaded['url'],
      'post_parent' => $this->postId,
      'post_title' => $this->image->Image->Title,
      'post_content' => '',
      'post_excerpt' => $caption,
    );

    $attach_id = wp_insert_attachment( $attachment, $filename, $this->postId );
    if ($attach_id == 0) throw new Exception("wp_insert_attachment() ERROR");
    update_post_meta( $attach_id, '_wp_attachment_image_alt', addslashes( $this->image->Image->Title ) );


//    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    $result = wp_update_attachment_metadata( $attach_id, $attach_data );

    if ($result === false) throw new Exception( "wp_update_attachment_metadata() ERROR");

    return $attach_id;
  }
  public function attachment_fields_to_edit($form_fields,$post) {
//    $url = get_permalink($post->ID);
    $url = $this->image->Image->DetailUrl;
    if(get_option('dreamstime_referral_state')) {
      $url .= '#'.$this->user->ClientId;
    }

    $form_fields['url']['html'] = '<input type="text" class="text urlfield" name="attachments['.$post->ID.'][url]" value="'.$url.'" />';
    unset($form_fields['url']['helps']);

    return $form_fields;
  }

  protected function _startSession()
  {
    if(!session_id()) {
      session_start();
    }
  }
  protected function _getFilenameFromHeaders($headers)
  {
    $header = $headers['content-disposition'];
    if(($start = strpos($header, '"')) !== false) {
      $start ++;
      $end = strpos($header, '"', $start);
      $length = $end - $start;
      $filename = substr($header, $start, $length);
      return $filename;
    } else {
      throw new Exception('Can\'t get the file name');
    }

  }
  public function __get($var)
  {
    if(in_array($var, $this->data) && !isset($this->$var)) {
      return $_SESSION['dreamstime'][$var];
    }
    return $this->$var;
  }
  public function __set($var, $val)
  {
    if(in_array($var, $this->data)) {
      $_SESSION['dreamstime'][$var] = $val;
    }
    $this->$var = $val;
  }

}

new Dreamstime();
?>
