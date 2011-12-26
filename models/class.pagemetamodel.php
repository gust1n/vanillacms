<?php if (!defined('APPLICATION')) exit();

class PageMetaModel extends Gdn_Model {
   
	public function __construct() {
		parent::__construct('PageMeta');
	}
	
	/**
	 * Deletes PageMeta by ID
	 *
	 * @param int $PageMetaID the ID to delete
	 * @return bool
	 * @author Jocke Gustin
	 */
	public function Delete($PageMetaID = '')
	{
	  $this->SQL->Delete('PageMeta', array('PageMetaID' => $PageMetaID));
	  return TRUE;
	}
	
	/**
	 * Retrieves all pagemeta from selected page
	 *
	 * @param int $PageID Selected page
	 * @param string $MetaKey (optional) set metakey to get information for a specific metakey
	 * @return object SQL results.
	 * @author Jocke Gustin
	 */
	public function Get($PageID, $MetaKey = '')
	{
	   if ($MetaKey) {
	      return $this->SQL
            ->Select('pm.*')
            ->Where('pm.PageID', $PageID)
            ->Where('pm.MetaKey', $MetaKey)
            ->From('PageMeta pm')
            ->Get()
            ->FirstRow();
	   } else {
	      return $this->SQL
            ->Select('pm.*')
            ->Where('pm.PageID', $PageID)
            ->From('PageMeta pm')
            ->Get();
      }
	}
} ?>