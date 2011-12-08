<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['YoutubeModule'] = array(
	'Name' => 'YouTube Embed',
   'Description' => "Embeds YouTube-video",
   'HelpText' => "YouTube video-ID",
   'ShowAssets' => true,
   'ContentType' => "text",
   'Author' => "Jocke Gustin"
);

/**
 * Outputs YouTube embed from YouTubeID
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