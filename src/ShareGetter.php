<?php

namespace EasyFacebookShareCount;

use \Curl\Curl;

class ShareGetter {

	protected $app_id;
	protected $app_secret;
	protected $access_token;
	protected $cached_requests = array();

	/////////
	// Set //
	/////////

	public function set_app_id($id){
		$this->app_id = $id;
	}

	public function set_app_secret($secret){
		$this->app_secret = $secret;
	}

	public function set_access_token($token){
		$this->access_token = $token;
	}

	/////////
	// Get //
	/////////

	public function get_access_token(){

		// Access token could be directly provided or can be created from app ID and app secret
		if (isset($this->app_token)){
			return $this->app_token;
		}

		if (isset($this->app_id) && isset($this->app_secret)){
			return $this->app_id . '|' . $this->app_secret;
		}

		throw new \Exception('You must provide an access token OR both the app ID and app secret.');

	}

	public function get_data($url){

		if (isset($this->cached_requests[$url])){
			return $this->cached_requests[$url];
		}

		$curl = new Curl;
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

		$request_url = $this->create_facebook_request_url($url);

		$curl->get($request_url);

		if ($curl->error){
			return 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage;
		}

		$data = $curl->response;

		$this->cached_requests[$url] = $data;

		return $this->cached_requests[$url];

	}

	public function get_share_count($url){

		$data = $this->get_data($url);

		if (isset($data->share->share_count)){
			return $data->share->share_count;
		}
		else {
			return 0;
		}

	}

	public function get_comment_count($url){

		$data = $this->get_data($url);

		if (isset($data->share->comment_count)){
			return $data->share->comment_count;
		}
		else {
			return 0;
		}

	}

	///////////////
	// Protected //
	///////////////

	protected function create_facebook_request_url($url){

		$base_url = 'https://graph.facebook.com/v2.7';

		$params = array(
			'id' => $url,
			'access_token' => $this->get_access_token()
		);

		$url = $base_url . '?' . http_build_query($params);

		error_log($url);

		return $url;

	}

}

