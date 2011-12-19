<?php if (!defined('APPLICATION')) exit();

$ModuleInfo['CustomHtmlModule'] = array(
	'Name' => 'Custom Html',
   'Description' => "Enables you to insert custom html",
   'HelpText' => "Insert custom HTML here",
   'ShowAssets' => true,
   'ContentType' => "textarea",
   'Author' => "Jocke Gustin"
);

class CustomHtmlModule extends Gdn_Module {

	public $BBCode;
	public $Asset = 'Content';


	public function __construct(&$Sender = '', $BBCode = '') {
		$this->BBCode = $BBCode;
		$this->_ApplicationFolder = 'vanillacms';
	}

	public function AssetTarget() {
		return $this->Asset;
	}

	public function ToString() {
		//if ($this->Html != '')
			return parent::ToString();

		return '';
	}
}