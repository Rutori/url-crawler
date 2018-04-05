<?php
class lurker
{
	private $words;
	private $urls = array();
	private $valid;
	private $needData;
	private $data = array();
	private $curl_threads;
	private $validHTTPStatuses = array(
		"200",
		"201"
	);

	function __construct($needData = false,$threads = 100)
	{
		$this->needData = $needData;

		$this->curl_threads = intval($threads);

	}

	function add_url($newUrls)
	{
		if (is_array($newUrls)){
			$this->urls = array_merge($this->urls,$newUrls);			
		}
		else{
			$this->urls[] = $newUrls;			
		}
	}

	function get_valid()
	{
		return $this->valid;
	}

	function get_data()
	{
		return $this->data;
	}

	function clear()
	{
		$this->valid = array();
		$this->data = array(); 
	}

	function fetch()
	{
		$rounds = ceil(count($this->urls)/$this->curl_threads);
		for ($i=0; $i < $rounds; $i += $this->curl_threads) { 
			$this->check_url(array_splice($this->urls, 0, $this->curl_threads));
		}
		if ($needData){
			return $this->data;
		} else{
			return $this->valid;
		}
	}

	function check_url($urls)
	{
		set_time_limit($this->curl_threads);
		$valids = 0; //количество url вернувших нам 200
		$handle = curl_multi_init();
		foreach ($urls as $key => $url) {
			$ch[$key] = curl_init($url);
			curl_setopt($ch[$key], CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch[$key], CURLOPT_HEADER, TRUE);
			curl_multi_add_handle($handle,$ch[$key]);
		}
		do {
		    $status = curl_multi_exec($handle, $active);
		    $info = curl_multi_info_read($handle);
		    if (false !== $info) {
		    	$data = curl_getinfo($info['handle']);
		        if(in_array($data['http_code'], $this->validHTTPStatuses)){
		        	if ($this->needData){
		        		$this->data[$data['url']] = curl_multi_getcontent($info['handle']);		        		
		        	}
		        	$this->valid[] = $data['url'];
		        	$valids++;
		        }
		    }
		} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
		return $valids;
	}
}