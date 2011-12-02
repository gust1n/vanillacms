<?php if (!defined('APPLICATION')) exit();

class VanillaCMSController extends Gdn_Controller {

	public function __construct() {
		parent::__construct();
	}

	public function Initialize() {
		$this->Head = new HeadModule($this);		
		$this->AddJsFile('jquery.js');
	   $this->AddJsFile('jquery.livequery.js');
	   
	   //These are needed for popup ajax, do not remove
	   $this->AddJsFile('jquery.form.js');
	   $this->AddJsFile('jquery.gardenhandleajaxform.js');
	   $this->AddJsFile('jquery.popup.js');
	   
	   //$this->AddJsFile('global.js', 'vanillacms'); //For menu
	   //$this->AddCssFile('global.css'); //For menu
	   
		$this->AddCssFile('style.css');
		
		parent::Initialize();
	}
	
	/*
	public function PageTree($Pages)
	  {
	    foreach ($Pages as $Page) {
	       if ($Page['ParentPageID'] > 0) {
	          echo 'The page' . $Page['Name'] . ' has a parent';
	          
	          $Parent = self::ArraySearch2($Pages,'PageID', $Page['ParentPageID']);
	          
	          $Parent[0]['Children'] = $Page;
	          
	          print_r($Parent);
	       } 
	    }
	  }
	  
	  public function ArraySearch($array, $key, $value)
	  {
	      $results = array();

	      if (is_array($array))
	      {
	         if ($array[$key] == $value)
	            $results[] = $array;

	            foreach ($array as $subarray)
	               $results = array_merge($results, self::ArraySearch($subarray, $key, $value));
	      }

	      return $results;
	   }
	   
	   public function ArraySearch2($array, $key, $value)
	  {
	       $arrIt = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

	    foreach ($arrIt as $sub) {
	       $subArray = $arrIt->getSubIterator();
	       if ($subArray[$key] === $value) {
	           $outputArray[] = iterator_to_array($subArray);
	       }
	   }
	   return $outputArray;
	   }*/
	
}
