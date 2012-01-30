<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['DiscussPageModule'] = array(
	'Name' => 'Page Discussion',
   'Description' => "Allows Page Comments",
   'HelpText' => "None",
   'ContentType' => "none",
   'ShowAssets' => false,
   'Author' => "Jocke Gustin"
);
/**
 * Renders the discussion part of each page
 */
class DiscussPageModule extends Gdn_Module {

	public $DiscussionData = '';
	public $PageID;
	public $DiscussionID;
	public $Offset;
	
	public function __construct($Sender = '') {
	   $this->_ApplicationFolder = 'vanillacms';
		$this->_ThemeFolder = 'default';
		$this->PageID = $Sender->Page->PageID;
		$this->DiscussionID = $Sender->Page->DiscussionID;
		$this->Form = Gdn::Factory('Form', 'Comment');
		$this->CommentModel = new CommentModel();
		
		if (!is_numeric($this->PageID) || $this->PageID <= 0) {
			return;
		}
		
		//If Page doesn't have a corresponding DiscussionID we must create one
		if (!isset($this->DiscussionID)) {
			$FakeFormPostValues = array(
				'Name' => 'Page Comments to page no' . $this->PageID, 
				'CategoryID' => 1, 
				'Body' => 'This should not be visible, anywhere!', 
				'Post_Discussion' => 'Post Discussion',
				'PageID' => $this->PageID
			);
			if ($DiscussionID = $Sender->DiscussionModel->Save($FakeFormPostValues)) {
				$Sender->PageModel->Update('DiscussionID', $this->PageID, $DiscussionID);
				$this->DiscussionID = $DiscussionID;
			}
		}
		
		//If adding a comment
		if ($this->Form->AuthenticatedPostBack()) {
				$FormValues = $this->Form->FormValues();
				$CommentID = $this->CommentModel->Save($FormValues);
	         $this->Form->SetValidationResults($this->CommentModel->ValidationResults());
	
	         if ($this->Form->ErrorCount() > 0) {
				/*
					TODO Fix error-handling
				*/
				}		
	   }
	
	      
	      /*
	      	TODO Fix voting
	      */
	      // if (C('EnabledPlugins.Voting') == true) {
	      // 	         $Sender->AddCSSFile('plugins/Voting/design/voting.css');
	      // 	         $Sender->AddJSFile('plugins/Voting/voting.js');
	      // 	      }
			
			$Sender->CommentModel->PageComments = true;
			$this->DiscussionData = $Sender->CommentModel->Get($this->DiscussionID);			
			
			/*
				TODO Build a pager
			*/
	
		parent::__construct($Sender);
	}

   public function AssetTarget() {
      return 'AfterBody';
   }

}