<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['EmailFormModule'] = array(
	'Name' => 'E-mail Form',
   'Description' => "Simple e-mail form",
   'HelpText' => "Insert custom HTML here",
   'ContentType' => "none",
   'ShowAssets' => true,
   'Author' => "Jocke Gustin"
);

/**
* Renders a form that allows adding modules to pages.
*/
class EmailFormModule extends Gdn_Module {

	public $Form;

	public function __construct(&$Sender = '') {
		$Session = Gdn::Session();
		$this->_ApplicationFolder = 'vanillacms';
		$this->Form = Gdn::Factory('Form', 'EmailForm');
		$Sender->AddJsFile('/js/library/jquery.autogrow.js');
      $Sender->AddCssFile('modules.css');
		if ($this->Form->AuthenticatedPostBack()) {
		 // echo "hej";
         //return;
			$Validation = new Gdn_Validation();
			$Validation->ApplyRule('Email', 'Email');
			$Validation->ApplyRule('Name', 'Required');
			$Validation->ApplyRule('Body', 'Required');
			$Validation->Validate($this->Form->FormValues());
			$this->Form->SetValidationResults($Validation->Results());

			if ($this->Form->ErrorCount() == 0) {
				$Subject = $this->Form->GetFormValue('Subject', T('Contact'));
				$SenderEmail = $this->Form->GetFormValue('Email');
				$SenderName = $this->Form->GetFormValue('Name');
				$Body = $this->Form->GetFormValue('Body');

				$Email = new Gdn_Email();


				$Email->Subject($Subject);
				$Email->To(C('Garden.Email.SupportAddress'));
				$Email->From($SenderEmail, $SenderName);
				$Email->Message(
					T('Email sent from Jesus People Contact Form') . '<br />' .
					T('Sent by') . ': ' . $SenderName . ', ' . $SenderEmail . 
					T('Time') . ': ' . Gdn_Format::ToDateTime() . 
					$Body
					);
				$Email->Send();

				$Sender->StatusMessage = T('Your Email was sent!');
			}

		}  
	} 

	public function AssetTarget() {
		return 'Panel';
	}

	public function ToString() {
		return parent::ToString();
	}
}