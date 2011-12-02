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
	
   function parse_args( $args, $defaults = '' ) {
   	if ( is_object( $args ) )
   		$r = get_object_vars( $args );
   	elseif ( is_array( $args ) )
   		$r =& $args;
   	else
   		wp_parse_str( $args, $r );

   	if ( is_array( $defaults ) )
   		return array_merge( $defaults, $r );
   	return $r;
   }
	
}
