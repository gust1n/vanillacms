<?php if (!defined('APPLICATION')) exit();

/**
 * VanillaCMS.Modules
 */

/**
 * Renders the retweet and facebooklike buttons
 */
class ShareModule extends Gdn_Module {
	
	public $FacebookUrl = '';
	
	public function __construct(&$Sender = '') {
		$this->_ApplicationFolder = 'vanillacms';
		$Sender->AddCssFile('modules.css');
	}
	
   public function AssetTarget() {
      return 'AfterCommentMeta';
   }
}