<?php if (!defined('APPLICATION')) exit();

/**
* Renders the header for all pages
*/
class HeaderModule extends Gdn_Module {
   
   public $Menu = '';

	public function __construct(&$Sender = '') {
	   $this->_ApplicationFolder = 'vanillacms';
	   $this->Menu = $Sender->Menu;
	   $this->Sender = $Sender;
	}
	public function AssetTarget() {
		return 'Header';
	}
}