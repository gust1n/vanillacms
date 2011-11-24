<?php if (!defined('APPLICATION')) exit();
/**
 * VanillaCMS.Modules
 */

/**
 * Renders the quote on top of each page
 */
class QuoteModule extends Gdn_Module {
   
   public $Quote;
   
	public function __construct(&$Sender = '', $Quote = '') {
	   $this->Quote = $Quote;
		$this->_ApplicationFolder = 'vanillacms';
	}

   public function AssetTarget() {
      return 'Quote';
   }

   public function ToString() {
         return parent::ToString();
   }
}