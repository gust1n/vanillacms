<?php if (!defined('APPLICATION')) exit();

/**
 * Renders the discussion part of each page
 */
class DiscussPageModule extends Gdn_Module {

	public $DiscussionData = '';
	public $PageID;
	
	public function __construct($Sender = '', $PageID = '') {
	   if (isset($PageID)) {
	      $this->PageID = $PageID;
	      
	      
	      if (C('EnabledPlugins.Voting') == true) {
	         $Sender->AddCSSFile('plugins/Voting/design/voting.css');
	         $Sender->AddJSFile('plugins/Voting/voting.js');
	      }
         
	      $DiscussionModel = new DiscussionModel();
         $DiscussionModel->PageID = $PageID;
         $this->DiscussionData = $DiscussionModel->Get(0, 50);
         
      }
     		
		$this->_ApplicationFolder = 'vanillacms';
		$this->_ThemeFolder = 'default';
		
		parent::__construct($Sender);
	}

   public function AssetTarget() {
      return 'AfterBody';
   }
}