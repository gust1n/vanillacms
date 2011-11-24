<?php if (!defined('APPLICATION')) exit();

/**
 * VanillaCMS.Modules
 */

/**
 * Renders the image of the current topic
 */
class YoutubeModule extends Gdn_Module {

	public $YoutubeID = '';
	public $Photourl = '';
	
	public function __construct(&$Sender = '', $YoutubeID = '') {
		$this->_ApplicationFolder = 'vanillacms';
		$this->YoutubeID = $YoutubeID;
	}
	
   public function AssetTarget() {
      return 'FullWidth';
   }
}