<?php if (!defined('APPLICATION')) exit();

class MediaModel extends Gdn_Model {
		/**
		* Class constructor.
		*/
	public function __construct() {
		parent::__construct('Media');
	}

	public function Get() {
	      return $this->SQL
	         ->Select('m.*')
			->Select('u.Name as UserName')
	         ->From('Media m')
			->Join('User u', 'm.InsertUserID = u.UserID')
	         ->Get();
	}
	public function GetName($MediaID)
	{
		$Temp = $this->SQL
			->Select('Name')
			->From('Media')
			->Where('MediaID', $MediaID)
			->Get()
			->FirstRow();
		return $Temp->Name;
	}
	
	public function DeleteImage($TopicID) {
	      $this->SQL
	         ->Update('Topic')
	         ->Set('PhotoID', 'null', FALSE)
	         ->Where('TopicID', $TopicID)
	         ->Put();
	}
	public function Delete($MediaID)
	{
		$this->SQL->Delete('Media', array('MediaID' => $MediaID));
	}
}