<?php if (!defined('APPLICATION')) exit();


class TemaController extends VanillaCMSController {

	public $Uses = array('Form', 'TopicModel', 'DiscussionModel', 'CommentModel');

	public function Initialize() {
		parent::Initialize();
		$this->Menu->HighlightRoute('/tema');
		$this->ControllerName = 'topic';
		$this->CssClass = 'Topic';      

	}

	public function Index($UrlCode = '', $Offset = '', $Limit = '')
	{
		$this->Topic = FALSE;
		$this->AddJsFile('/js/library/jquery.resizable.js');
		$this->AddJsFile('/js/library/jquery.ui.packed.js');
		$this->AddJsFile('/js/library/jquery.autogrow.js');
		$this->AddJsFile('/js/library/jquery.gardenmorepager.js');
		$this->AddJsFile('/applications/vanilla/js/options.js');
		$this->AddJsFile('/applications/vanilla/js/bookmark.js');
		$this->AddJsFile('/applications/vanilla/js/discussion.js');
		$this->AddJsFile('/applications/vanilla/js/autosave.js');
		if($UrlCode) 
			$this->Topic = $this->TopicModel->GetByUrlCode(urldecode($UrlCode));
		if(!$this->Topic) {
			$this->Topic = $this->TopicModel->GetCurrent();
		}

		if($this->Topic->Enabled != 1)
			$this->Topic = FALSE;	

		if($this->Topic->DiscussionID != 0) {
			$this->SetData('Discussion', $this->Discussion = $this->DiscussionModel->GetID($this->Topic->DiscussionID), TRUE);
			if(!is_object($this->Discussion))
				Redirect('FileNotFound');
		

		$Limit = Gdn::Config('Vanilla.Comments.PerPage', 30);

		$this->Offset = $Offset;   
		if (!is_numeric($this->Offset) || $this->Offset < 0) {
			// Round down to the appropriate offset based on the user's read comments & comments per page
			$CountCommentWatch = $this->Discussion->CountCommentWatch > 0 ? $this->Discussion->CountCommentWatch : 0;
			if ($CountCommentWatch > $this->Discussion->CountComments)
				$CountCommentWatch = $this->Discussion->CountComments;

			// (((67 comments / 10 perpage) = 6.7) rounded down = 6) * 10 perpage = offset 60;
			$this->Offset = floor($CountCommentWatch / $Limit) * $Limit;
		}

		if ($this->Offset < 0)
			$this->Offset = 0;



		$this->SetData('TopicText', $this->temp = $this->Discussion->Body, TRUE);
		$this->SetData('CommentData', $this->CommentData = $this->CommentModel->Get($this->Topic->DiscussionID, $Limit, $this->Offset), TRUE);
		//$this->SetData('Discussion', $this->DiscussionModel->GetID($DiscussionID), TRUE);




		// Define the form for the comment input
		$this->Form = Gdn::Factory('Form', 'Comment');
		$this->Form->Action = Url('/vanilla/post/comment');
		$this->DiscussionID = $this->Discussion->DiscussionID;
		$this->Form->AddHidden('DiscussionID', $this->DiscussionID);
		$this->Form->AddHidden('CommentID', '');


		$PagerFactory = new Gdn_PagerFactory();
		$this->Pager = $PagerFactory->GetPager('Pager', $this);
		//$this->Pager->MoreCode = T('%1$s more comments');
		//$this->Pager->LessCode = T('%1$s older comments');
		$this->Pager->ClientID = 'Pager';
		$this->Pager->Configure(
			$this->Offset,
			$Limit,
			$this->Discussion->CountComments  - 1,
			T('topic') . '/' . $this->Topic->UrlCode . '/%1$s/%2$s/'
			);	
		}
		$this->Title(Gdn_Format::Text($this->Topic->Name));

		$QuoteModule = new QuoteModule($this);
		$QuoteModule->Quote = $this->Topic->Quote;
		$this->AddModule($QuoteModule);

		if($this->Topic->VideoLink) {
			$YoutubeModule = new YoutubeModule($this);
			$YoutubeModule->YoutubeID = $this->Topic->VideoLink;
			$this->AddModule($YoutubeModule);
		} else if($this->Topic->Photo) {
			$YoutubeModule = new YoutubeModule($this);
			$YoutubeModule->PhotoUrl = '/uploads/s' . $this->Topic->Photo;
			$YoutubeModule->Alt = T('Topic') . ' ' . $this->Topic->Name;
			$this->AddModule($YoutubeModule);
		}
			

		$this->AddSideMenu();

		$this->Render();
	}


	/**
	* Adds sub menu to the panel asset.
	*/
public function AddSideMenu($CurrentUrl = '') {
	if ($this->Topic !== FALSE) {
		$SideMenu = new SideMenuModule($this);
		$Session = Gdn::Session();
		$ViewingUserID = $Session->UserID;
		$SideMenu->AddItem(T('Topics'), '');

		$TopicModel = new TopicModel();
		$Topics = $TopicModel->GetActiveTopics();
		foreach ($Topics->Result() as $Topic) {
			$SideMenu->AddLink(T('Topics'), $Topic->Name, '/' . T('topic') .'/'. $Topic->UrlCode, FALSE);
		}

		$this->EventArguments['SideMenu'] = &$SideMenu;
		$this->FireEvent('AfterAddSideMenu');
		$this->AddModule($SideMenu, 'Panel');
	}
}
}