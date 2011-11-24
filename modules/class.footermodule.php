<?php if (!defined('APPLICATION')) exit();
/**
* Renders the Footer for all pages
*/
class FooterModule extends Gdn_Module {

	public function __construct(&$Sender = '') {
	    $this->_ApplicationFolder = 'vanillacms';
		$Sender->AddJsFile('settings.js');
	}
	public function AssetTarget() {
		return 'Foot';
	}
}