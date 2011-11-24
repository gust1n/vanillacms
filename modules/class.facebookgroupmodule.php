<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['FacebookGroupModule'] = array(
	'Name' => 'FaceBook Group',
   'Description' => "Displays your FaceBook group",
   'HelpText' => "Facebook group ID",
   'ContentType' => "text",
   'ShowAssets' => true,
   'Author' => "Jocke Gustin"
);
/**
* Renders the like group on facebook box
*/
class FacebookGroupModule extends Gdn_Module {

	public function __construct(&$Sender = '') {
	      $this->_ApplicationFolder = 'vanillacms';
	   }
	
	public function AssetTarget() {
		return 'Panel';
	}
}