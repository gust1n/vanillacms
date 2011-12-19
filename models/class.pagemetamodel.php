<?php if (!defined('APPLICATION')) exit();

class PageMetaModel extends Gdn_Model {
   
	public function __construct() {
		parent::__construct('PageMeta');
	}
	
	/**
	 * Adds meta information to pages
	 *
	 * @param int $PageID Selected page
	 * @param string $Meta Array of all information to be saved 
	 * @return none
	 * @author Jocke Gustin
	 */
	public function AddPageMeta($PageID, $Meta)
	{
	   foreach ($Meta as $PageMeta) {
            $this->SQL->Insert('PageMeta', array('PageID' => $PageID, 'MetaKey' => $PageMeta['MetaKey'],'MetaKeyName' => $PageMeta['MetaKeyName'], 'MetaValue' => $PageMeta['MetaValue'], 'MetaAsset' => $PageMeta['MetaAsset'], 'MetaAssetName' => $PageMeta['MetaAssetName']));
      }
	}
	
	/**
	 * Removes all page meta from selected page. Do this before adding every time
	 *
	 * @param int $PageID Selected page
	 * @return none
	 * @author Jocke Gustin
	 */
	public function ClearPageMeta($PageID)
	{
	   $this->SQL->Delete('PageMeta', array('PageID' => $PageID));
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