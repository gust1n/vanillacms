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
	
}
