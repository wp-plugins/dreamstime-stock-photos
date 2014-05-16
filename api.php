<?php

class DreamstimeApi {

  public $apiUrl = 'http://www.dreamstime.com/api/';
  public $ApplicationId = 'WP-Plugin v2.0';
  public $pageSize = 20;
  public $APIRequests = array();
  public $APIResponses = array();


  public function __construct () {

  }

  public function checkUsername($username)
  {
    $data['APIRequests']['checkUsername'] = array(
      'Verb' => 'CheckUsername',
      'AuthToken' => $this->_getToken(),
      'Username' => $username,
    );
    $response = $this->_post($data);
    return array(
      'taken' => $response->APIResponses->checkUsername->UsernameTaken,
      'suggested' => $response->APIResponses->checkUsername->SuggestedUsername,
    );
  }

  public function createAccount($username, $password, $email)
  {

    $data['APIRequests']['createAccount'] = array(
      'Verb' => 'CreateAccount',
      'AuthToken' => $this->_getToken(),
      'Username' => $username,
      'Password' => $password,
      'Email' => $email,
      'AppOS' => 'Wordpress',
    );
    $response = $this->_post($data);
    return $response->APIResponses->createAccount->ClientId;

  }

  public function search($keywords, $searchType, $page=1)
  {
    $this->APIRequests[$searchType] = array(
      'Verb' => $searchType,
      'AuthToken' => $this->_getToken(),
      'Keywords' => $keywords,
      'PageSize' => $this->pageSize,
      'Page' => $page,
    );
  }

  public function getSearchResults($searchType) {
//    $response = $this->APIResponses[$searchType];
    return array(
      'images' => (array)$this->APIResponses[$searchType]->Images,
      'count' => $this->APIResponses[$searchType]->ImagesCount,
    );
  }
//  public function search($keywords, $page=1, $searchType = 'SearchFreeImages')
//  {
//    $data['APIRequests'][$searchType] = array(
//      'Verb' => $searchType,
//      'AuthToken' => $this->_getToken(),
//      'Keywords' => $keywords,
//      'PageSize' => $this->pageSize,
//      'Page' => $page,
//    );
//    $response = $this->_post($data);
//    return array(
//      'images' => (array)$response->APIResponses->$searchType->Images,
//      'count' => $response->APIResponses->$searchType->ImagesCount,
//    );
//  }

  public function getEditorsChoiceImages($page=1)
  {
    $data['APIRequests']['GetEditorsChoiceImages'] = array(
      'Verb' => 'GetEditorsChoiceImages',
      'AuthToken' => $this->_getToken(),
      'PageSize' => $this->pageSize,
      'Page' => $page,
    );
    $response = $this->_post($data);
    return array(
      'images' => (array)$response->APIResponses->GetEditorsChoiceImages->Images,
      'count' => $response->APIResponses->GetEditorsChoiceImages->ImagesCount,
    );
  }

  public function getImage($imageId, $imageType)
  {
    $verb = $imageType == 'free' ? 'GetFreeImage' : 'GetPaidImage';
    $data['APIRequests'][$verb] = array(
      'Verb' => $verb,
      'AuthToken' => $this->_getToken(),
      'ImageId' => $imageId,
      'PageUrl' => $_SERVER['HTTP_REFERER'],
    );
    if($this->_getAuth('username')) {
      $data['APIRequests']['GetAccountInfo'] = array(
        'Verb' => 'GetAccountInfo',
        'AuthToken' => $this->_getToken(),
      );
    }
    $response = $this->_post($data);
    $return = array('image' => $response->APIResponses->$verb);
    if($this->_getAuth('username')) {
      $return['account_info'] = $response->APIResponses->GetAccountInfo;
    }
    return $return;
  }

  public function downloadImage($imageId, $imageType, $size = null, $license = null)
  {
    $verb = 'Download'.ucfirst($imageType).'Image';
    $data['APIRequests'][$verb] = array(
      'Verb' => $verb,
      'AuthToken' => $this->_getToken(),
      'ImageId' => $imageId,
      'PageUrl' => $_SERVER['HTTP_REFERER'],
    );
    if($size && $license) {
      $data['APIRequests'][$verb]['Size'] = $size;
      $data['APIRequests'][$verb]['License'] = $license;
    }
    $response = $this->_post($data);
    return $response->APIResponses->$verb->DownloadURL;
  }

  public function login($username, $password)
  {
     $this->_authenticate($username, $password);
     return $this->getAccountInfo();
  }

  public function getAccountInfo()
  {
    $data['APIRequests']['GetAccountInfo'] = array(
      'Verb' => 'GetAccountInfo',
      'AuthToken' => $this->_getToken(),
    );
    $response = $this->_post($data);
    return $response->APIResponses->GetAccountInfo;
  }

  public function getLightboxes()
  {
    $data['APIRequests']['GetLightboxes'] = array(
      'Verb' => 'GetLightboxes',
      'AuthToken' => $this->_getToken(),
    );
    $response = $this->_post($data);
    return (array)$response->APIResponses->GetLightboxes->Lightboxes;
  }

  public function getLightboxImages($lightboxId, $page = 1)
  {
    $data['APIRequests']['GetLightboxImages'] = array(
      'Verb' => 'GetLightboxImages',
      'AuthToken' => $this->_getToken(),
      'LightboxId' => $lightboxId,
      'PageSize' => $this->pageSize,
      'Page' => $page,
    );
    $response = $this->_post($data);

    return array(
      'images' => (array)$response->APIResponses->GetLightboxImages->Images,
      'count' => $response->APIResponses->GetLightboxImages->ImagesCount,
    );
  }

  public function getCreditsLink()
  {
    $data['APIRequests']['GetCreditsLink'] = array(
      'Verb' => 'GetCreditsLink',
      'AuthToken' => $this->_getToken(),
    );
    $response = $this->_post($data);
    return array(
      'image_credits_text' => $response->APIResponses->GetCreditsLink->Text,
      'image_credits_description' => $response->APIResponses->GetCreditsLink->Description,
      'image_credits_link' => $response->APIResponses->GetCreditsLink->Link,
    );
  }

  public function getDownloadedImages($page = 1){
    $data['APIRequests']['GetDownloadedImages'] = array(
      'Verb' => 'GetDownloadedImages',
      'AuthToken' => $this->_getToken(),
      'PageSize' => $this->pageSize,
      'Page' => $page,
    );
    $response = $this->_post($data);
    return array(
      'images' => (array)$response->APIResponses->GetDownloadedImages->Images,
      'count' => $response->APIResponses->GetDownloadedImages->ImagesCount,
    );
  }

  public function getUploadedImages($page = 1){
    $data['APIRequests']['GetOnlineFiles'] = array(
      'Verb' => 'GetOnlineFiles',
      'AuthToken' => $this->_getToken(),
      'PageSize' => $this->pageSize,
      'Page' => $page,
    );
    $response = $this->_post($data);
    return array(
      'images' => (array)$response->APIResponses->GetOnlineFiles->OnlineFiles,
      'count' => $response->APIResponses->GetOnlineFiles->OnlineFilesCount,
    );
  }

  public function apiCall()
  {
    if(empty($this->APIRequests)) throw new Exception ('API: No request data !');

    $data = array('APIRequests' => $this->APIRequests);
    $response = $this->_post($data);
    $this->APIResponses = (array)$response->APIResponses;
    $this->APIRequests = array();
  }
  protected function _post($data)
  {
    $dataString = json_encode($data);

    $ch = curl_init($this->_getApiUrl());
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/JSON',
        'Content-Length: ' . strlen($dataString)
      )
    );
    curl_setopt($ch, CURLOPT_USERAGENT, $this->ApplicationId);


    $response = json_decode(curl_exec($ch));
//    $request = curl_getinfo($ch);
    curl_close($ch);

    try {
      $this->_checkForErrors($response);
    } catch (Exception $e) {
      //if expired token, re-authenticate to get the new token and re-post data
      $expiredErrorCodes = array(200, 201, 202, 204, 205, 206, 207, 208);
      if(in_array($response->ErrorCode, $expiredErrorCodes)) {
        $this->_saveAuth('token', null);
        foreach ($data['APIRequests'] as &$value) {
          if(isset($value['AuthToken'])){
            $value['AuthToken'] = $this->_getToken();
          }
        }
        return $this->_post($data);
      } else {
        throw $e;
      }
    }

    return $response;
  }
  protected function _getApiUrl()
  {
    if($apiurl = $this->_getAuth('apiurl'))
    {
      return $apiurl;
    }

    return $this->apiUrl;
  }
  protected function _authenticate($username = null, $password = null)
  {
    $data['APIRequests']['authenticate'] = array(
      'Verb' => 'Authenticate',
      'ApplicationId' => $this->ApplicationId
    );

    if($username && $password) {
      $data['APIRequests']['authenticate']['Username'] = $username;
      $data['APIRequests']['authenticate']['Password'] = $password;
    }

    $response = $this->_post($data);
    if($response->APIResponses->authenticate->Status == 'Success') {
      $this->_saveAuth('token', $response->APIResponses->authenticate->AuthToken);
      $this->_saveAuth('apiurl', $response->APIResponses->authenticate->APIUrl);
      $this->_saveAuth('username', $username);
      $this->_saveAuth('password', $password);
    }
  }
  protected function _getToken()
  {
    if($token = $this->_getAuth('token')) {
      return $token;
    }

    $this->_authenticate($this->_getAuth('username'), $this->_getAuth('password'));

    return $this->_getAuth('token');
  }
  protected function _checkForErrors($response)
  {
    if(!$response instanceof stdClass) {
      throw new Exception('No valid response from API');
    }

    if($response->Status == 'Error') {
      throw new Exception($response->ErrorMessage, $response->ErrorCode);
    }

    foreach ($response->APIResponses as $api_response) {
      if($api_response->Status == 'Error') {
        throw new Exception($api_response->ErrorMessage, $api_response->ErrorCode);
      }
    }
  }

  protected function _saveAuth($var, $val)
  {
    $_SESSION['dreamstime']['auth'][$var] = $val;
  }

  protected function _getAuth($var)
  {
    return $_SESSION['dreamstime']['auth'][$var];
  }


}

