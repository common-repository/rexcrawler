<?php
	// Our main crawler-class
	class Crawler{
		protected $markup = ""; // This will hold the fetched data (usually HTML)
		
		/*
		* The constructor of our class. Inputs 1 URI to the website we want to crawl
		*/
		public function __construct($uri){
			$this->markup = $this->getMarkup($uri);
		}
		
		/*
		* Gets the markup of a given URI
		*/
		public function getMarkup($uri){
			return file_get_contents($uri);
		}
		
		/*
		* Returns data based on a RegEx input pattern
		*/
		public function getFromRegex($patterns){
			// Check if we could get the page
			if(!empty($this->markup)){
			
				// Loop through all patterns
				foreach($patterns as $pattern){
				
					// Check if it's a valid pattern
					if($pattern != ''){
			
						// We do not want to do anything unless our array is empty
						if(empty($data) || empty($data[1])){
							// Get data!
							preg_match_all($pattern, $this->markup, $data);
						}else{
							return array_map("trim", $data[1]);
						}
					}
				}

				// Return data
				return !empty($data[1]) ? array_map("trim", $data[1]) : FALSE;
			}
		}
	}
?>