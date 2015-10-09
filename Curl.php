<?php

namespace pentajeu\utils;

class Curl
{
	public $options;

	private $_ch;

	// default config
	private $_config = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_AUTOREFERER => true,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0'
		);

	private function _exec($url)
	{
		$ch = $this->getCh();
		$options = is_array($this->options) ? ($this->options + $this->_config) : $this->_config;
		$this->setOptions($options);

		$this->setOption(CURLOPT_URL, $url);
		$return = curl_exec($ch);
		if (!curl_errno($ch))
			return $return;
		throw new \Exception(curl_error($ch));
	}

	public function get($url, $params = array())
	{
		$this->setOption(CURLOPT_HTTPGET, true);
		return $this->_exec($this->buildUrl($url, $params));
	}

	public function post($url, $data = array())
	{
		$this->setOption(CURLOPT_POST, true);
		$this->setOption(CURLOPT_POSTFIELDS, $data);
		return $this->_exec($url);
	}

	public function put($url, $data, $params = array())
	{

        // write to memory/temp
		$f = fopen('php://temp', 'rw+');
		fwrite($f, $data);
		rewind($f);

		$this->setOption(CURLOPT_PUT, true);
		$this->setOption(CURLOPT_INFILE, $f);
		$this->setOption(CURLOPT_INFILESIZE, strlen($data));

		return $this->_exec($this->buildUrl($url, $params));
	}

	public function close()
	{
		curl_close($this->getCh());
	}

	protected function getCh()
	{
		if (isset($this->_ch))
			return $this->_ch;
		return $this->_ch = curl_init();
	}

	protected function buildUrl($url, $data = array())
	{
		$parsed = parse_url($url);
		isset($parsed['query']) ? parse_str($parsed['query'], $parsed['query']) : $parsed['query'] = array();
		$params = isset($parsed['query']) ? array_merge($parsed['query'], $data) : $data;
		$parsed['query'] = ($params) ? '?' . http_build_query($params) : '';
		if (!isset($parsed['path']))
			$parsed['path'] = '/';

		$port = '';
		if(isset($parsed['port'])){
			$port = ':' . $parsed['port'];
		}

		return $parsed['scheme'] . '://' . $parsed['host'] .$port. $parsed['path'] . $parsed['query'];
	}

	protected function setOptions($options = array())
	{
		curl_setopt_array($this->getCh(), $options);
		return $this;
	}

	protected function setOption($option, $value)
	{
		curl_setopt($this->getCh(), $option, $value);
		return $this;
	}

	public function getInfo($param = NULL)
	{
		if (empty($param))
			$param = CURLINFO_CONTENT_TYPE;
		return curl_getinfo($this->getCh(), $param);
	}
}
