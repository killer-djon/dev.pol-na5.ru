<?php

/**
 * @link http://www.flickr.com/services/api/
 */
class ServiceSynchr_Flickr {
	
	private $FLICKR_API_KEY = null;
	//http://api.flickr.com/services/rest/?method=flickr.test.echo&name=value
	private $API_URL_REST = "http://api.flickr.com/services/rest/";
	private $SECRET = "dbdc0734c5b0cf57";
	private $debugMod = false;
	private $auth_token = false;
	

	/**
	 * test key: 1a3c4ee62c3d530f178e8708e122b71c
	 * test secret: dbdc0734c5b0cf57
	 * @param string $FLICKR_API_KEY
	 */
	public function __construct($FLICKR_API_KEY = "1a3c4ee62c3d530f178e8708e122b71c",
								$SECRET = "dbdc0734c5b0cf57") 
	{
		$this->FLICKR_API_KEY = $FLICKR_API_KEY;
		$this->SECRET = $SECRET;
	}
	public function debugMod($value = false) {
		$this->debugMod = $value;
	}
	
	/**
	 * @param array $params
	 * @return array
	 */
	private function sendQuery($params, $isSig = false) {
		if ( is_array( $params ) ) {
			
			if ( $this->auth_token )
				$isSig = true;
			
			$query_params = array_merge(array(
				'api_key'	=> $this->FLICKR_API_KEY,
				'format'	=> 'php_serial'
			), $params	);
			
			if ($this->auth_token) {
				$query_params = array_merge($query_params, array(
					'auth_token' => $this->auth_token
				));
			}
			
			ksort(&$query_params);
			$encoded_params = array();
			$sign = array();
			foreach ($query_params as $k => $v){
			
				$encoded_params[] = urlencode($k).'='.urlencode($v);
				$sign[] = urlencode($k).urlencode($v);
			}
			$sig = md5($this->SECRET.implode( '', $sign ));
			
			if ($isSig)
				$url = $this->API_URL_REST ."?". implode('&', $encoded_params) . '&api_sig=' . $sig;
			else
				$url = $this->API_URL_REST ."?". implode('&', $encoded_params);
			
			
			if ( $this->debugMod ) {
				print "<pre>";
				print_r( $url);
				print_r( $query_params);
				print "</pre>";
				return 0;
			}
			
			$rsp = file_get_contents($url);
			$rsp_obj = unserialize($rsp);
			if ( $rsp_obj['stat'] == 'ok' ) {
				return $rsp_obj;
			}
			else
				throw new RuntimeException ("Flickr api query error: ".$rsp_obj['message']);			
		}
	}
	/**
	 * @param string $name
	 * @return string
	 */
	public function getIdByUsername($name) {
		//?method=flickr.people.findByUsername&api_key=a7ca7829325ef2f771091e5ae6e5775c&username=naokiro_sky
		$result = $this->sendQuery(array(
			"method" => "flickr.people.findByUsername",
			"username" => $name
		));				
		return (string)$result['user']['id'];
	}
	
	
	public function getToken($frob) {
		$result = $this->sendQuery(array(
			"method" => "flickr.auth.getToken",
			"frob" => $frob			
		), true);		
		
		return $result['auth'];
		
	}
	
	public function setAuthToken($token) {
		$this->auth_token = $token;
	}

	/**
	 * @param string $user_id
	 * @return array
	 */
	public function getListPhotosets($user_id) {
		if ( $this->auth_token )
			$result = $this->sendQuery(array(
				"method" => "flickr.photosets.getList"
			), true);
		else
			$result = $this->sendQuery(array(
				"method" => "flickr.photosets.getList",
				"user_id" => $user_id
			));
		return $result['photosets']['photoset'];
	}
	
	public function getPhotoListByPhotosetId($photoset_id) {
		$result = $this->sendQuery(array(
			"method" => "flickr.photosets.getPhotos",
			"photoset_id" => $photoset_id
		));
		return $result['photoset'];
	}
	
	public function getPhotoInfoById($photo_id) {
		$result = $this->sendQuery(array(
			"method" => "flickr.photos.getInfo",
			"photo_id" => $photo_id
		));
		
		return $result['photo'];
	}
	
	public function getSizeById($photo_id) {
		$result = $this->sendQuery(array(
			"method" => "flickr.photos.getSizes",
			"photo_id" => $photo_id
		));
		
		return $result['sizes'];
	}
	/**
	 * @param array $d is return info function getPhotoInfoById
	 */
	static function photoDateToUrl($d) {
		return "http://farm{$d['farm']}.static.flickr.com/{$d['server']}/{$d['id']}_{$d['secret']}_b.jpg";
		
	}
	
	public function getAuthUrl($perms = "read") {
		$api_sig = md5($this->SECRET . 'api_key' . $this->FLICKR_API_KEY . 'perms' . $perms) ;
		return "http://flickr.com/services/auth/?api_key={$this->FLICKR_API_KEY}&perms={$perms}&api_sig={$api_sig}";
	}
	
}

?>