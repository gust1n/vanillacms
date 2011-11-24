<?php if (!defined('APPLICATION')) exit();


class AdminController extends VanillaCMSController {

	public $Uses = array('Form', 'UserModel');

	public function Initialize() {
		parent::Initialize();      
	}

	public function Index($UrlCode = '')
	{
		$Session = Gdn::Session();
		$UserModel = new UserModel($this);
		$Roles = $UserModel->GetRoles($Session->UserID);
		$Count = 0;
		foreach ($Roles as $Role) {
			$Count = $Count + $Role->RoleID;
		}
		if ($Count > 4) {
			$this->SetData('Roles', $Roles, TRUE);
			$this->Render();
		}
		else
			Redirect('dashboard/home/filenotfound');
	}
}