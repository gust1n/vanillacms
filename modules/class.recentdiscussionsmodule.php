<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['RecentDiscussionsModule'] = array(
	'Name' => 'Recent Discussions',
   'Description' => "Displays the x recent discussions",
   'HelpText' => "No. of discussions to display",
   'ContentType' => "text",
   'Author' => "Jocke Gustin"
);
/**
* Renders the 5 most recent discussions for use in a first page box
*/
class RecentDiscussionsModule extends Gdn_Module {

	protected $_CommentsData = FALSE;
	public $Form;
	public $Asset = 'Content';

	public function __construct(&$Sender = '', $Limit = 5) {
		$this->_CommentsData = FALSE;
		$Sender->AddCssFile('modules.css');
		$this->GetData($Limit);
		$this->_ApplicationFolder = 'vanillacms';
		parent::__construct($Sender);
	}


	public function GetData($Limit) {
		$DiscussionModel = new DiscussionModel();
		$DiscussionsData = $DiscussionModel->Get('', $Limit);
		$this->_CommentsData = $DiscussionsData;
	}

	public function AssetTarget() {
		return $this->Asset;
	}

	public function ToString() {
		if ($this->_CommentsData !== FALSE && $this->_CommentsData->NumRows() > 0)
			return parent::ToString();

		return '';
	}
}