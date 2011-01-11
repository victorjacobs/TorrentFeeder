<?php

	abstract class parsePluginBase {
		protected $dom, $torrents;
		
		public function __construct() {
			$this->dom = new DOMDocument;
			$this->dom->preserveWhiteSpace = false;
		}
		
		public function load($data) {
			if(is_null($data)) Core::fatalError("parsePluginBase::load didn't get any \$data!!");
			
			// Supress warnings caused by non-validness + remove all html entities
			@$this->dom->loadHTML(preg_replace("/&#?[a-z0-9]{2,8};/i", "", str_replace("&nbsp;", " ", $data)));
		}
		
		public function reset() {
			unset($this->data, $this->dom);
			
			$this->dom = new DOMDocument;
			$this->dom->preserveWhiteSpace = false;
		}
		
		public function getData() {
			return $this->torrents;
		}
		
		protected abstract function parse();
		
		public abstract function hasUsefulData();
	}

?>