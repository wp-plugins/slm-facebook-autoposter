<?php

if (!class_exists('CustomFacebook')) {
	
	require_once dirname(__FILE__).'/facebook.php';
	
	class CustomFacebook extends MV_Facebook {

		public function __construct($config) {
			if (!session_id() && !$config['nosession']) {
				session_start();
			}
			
			// copy from facebook_base
			//parent::__construct($config);
		    $this->setAppId($config['appId']);
		    $this->setAppSecret($config['secret']);
		    if (isset($config['fileUpload'])) {
		      $this->setFileUploadSupport($config['fileUpload']);
		    }
		    if (isset($config['trustForwarded']) && $config['trustForwarded']) {
		      $this->trustForwarded = true;
		    }
		    if (isset($config['allowSignedRequest'])
		        && !$config['allowSignedRequest']) {
		        $this->allowSignedRequest = false;
		    }
		    $state = $this->getPersistentData('state');
		    if (!empty($state)) {
		      $this->state = $state;
		    }
			
			// end
			if (!empty($config['sharedSession'])) {
				$this->initSharedSession();
		
				// re-load the persisted state, since parent
				// attempted to read out of non-shared cookie
				$state = $this->getPersistentData('state');
				if (!empty($state)) {
					$this->state = $state;
				} else {
					$this->state = null;
				}
		
			}
		}
		
		
		public function getAccessTokenByAuthorizationCode($code, $redirect_uri = null) {
			return $this->getAccessTokenFromCode($code, $redirect_uri,false);
		}
		
		/*public function getExtendedAccessToken() {
			try {
				// need to circumvent json_decode by calling _oauthRequest
				// directly, since response isn't JSON format.
				$access_token_response = $this->_oauthRequest(
				$this->getUrl('graph', '/oauth/access_token'),
				$params = array(
		          'client_id' => $this->getAppId(),
		          'client_secret' => $this->getAppSecret(),
		          'grant_type' => 'fb_exchange_token',
		          'fb_exchange_token' => $this->getAccessToken(),
				)
				);
			}
			catch (FacebookApiException $e) {
				// most likely that user very recently revoked authorization.
				// In any event, we don't have an access token, so say so.
				return false;
			}
		
			if (empty($access_token_response)) {
				return false;
			}
		
			$response_params = array();
			parse_str($access_token_response, $response_params);
		
			if (!isset($response_params['access_token'])) {
				return false;
			}
		
		    return $response_params['access_token']
		}*/
		
		
	}
}

?>