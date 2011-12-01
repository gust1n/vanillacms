<?php if (!defined('APPLICATION')) exit();

class PageModel extends Gdn_Model {
   
	public function __construct() {
		parent::__construct('Page');
	}
	
	public function Search($SearchModel) {
		$SearchModel->AddSearch($this->PageSql($SearchModel));
	}
	
	/**
	 * Execute page search query.
	 * 
    * @since 2.0.0
    * @access public
	 * 
	 * @param object $SearchModel SearchModel (Dashboard)
	 * @return object SQL result.
	 */
	public function PageSql($SearchModel) {
		
		// Build search part of query
		$SearchModel->AddMatchSql($this->SQL, 'p.Name, p.Body', 'p.DateInserted');
		
		// Build base query
		$this->SQL
			->Select('p.PageID as PrimaryID, p.Name as Title, p.Body as Summary')
			->Select('p.UrlCode as Url')
			->Select('p.DateInserted')
			->Select('p.InsertUserID as UserID, u.Name, u.Photo')
			->From('Page p')
			->Join('User u', 'p.InsertUserID = u.UserID', 'left');
		
		// Execute query
		$Result = $this->SQL->GetSelect();
		$Result->Sender = 'page';
		// Unset SQL
		$this->SQL->Reset();
		return $Result;
	}   
   
   /**
    * Returns ALL pages, or by status
    *
    * @return object SQL results.
    * @author Jocke Gustin
    */
	public function Get() {
	      return $this->SQL
	         ->Select('p.*')
			   ->Select('uu.Name as UpdateUserName')
			   ->Select('ui.Name as InsertUserName')
			   ->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
			   ->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
	         ->From('Page p')
			   ->OrderBy('p.Sort')
	         ->Get();
	}
   
   /**
    * Returns ALL parent pages or pages without children
    *
    * @return object SQL results.
    * @author Jocke Gustin
    */
	public function GetAllParents($Status = '') {
	    if (!in_array($Status, array('published', 'draft'))) {
	       return $this->SQL
 	         ->Select('p.*')
 			   ->Select('uu.Name as UpdateUserName')
 			   ->Select('ui.Name as InsertUserName')
 			   ->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
 			   ->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
 	         ->From('Page p')
            ->Where('p.ParentPageID', 0)
            ->Where('p.Status <>', 'deleted')
 			   ->OrderBy('p.Sort')
 	         ->Get();
	    } else {
	       return $this->SQL
 	         ->Select('p.*')
 			   ->Select('uu.Name as UpdateUserName')
 			   ->Select('ui.Name as InsertUserName')
 			   ->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
 			   ->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
 	         ->From('Page p')
            ->Where('p.ParentPageID', 0)
            ->Where('p.Status', $Status)
            ->Where('p.Status <>', 'deleted')
 			   ->OrderBy('p.Sort')
 	         ->Get();
	    }
	      
	}
	/**
	 * Returns all pages that are ONLY parent pages
	 *
	 * @return object SQL results.
	 * @author Jocke Gustin
	 */
	public function GetParentsOnly() {
	      return $this->SQL
	         ->Select('p.*')
            ->Where('p.IsParentOnly', '1')
	         ->From('Page p')
	         ->Get();
	}
	
	/**
	 * Returns the published children of a selected parent page
	 *
	 * @param int $ParentPageID Selected parent page
	 * @return children by object SQL results.
	 * @author Jocke Gustin
	 */
	public function GetPublishedChildren($ParentPageID)
	{
		return $this->SQL
	      ->Select('p.*')
	      ->Select('uu.Name as UpdateUserName')
   		->Select('ui.Name as InsertUserName')
   		->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
   		->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
	      ->From('Page p')
			->Where('p.ParentPageID', $ParentPageID)
			->Where('p.Status', 'published')
			->OrderBy('p.Sort')
	      ->Get();
	}
	
	/**
	 * Returns ALL children of a selected parent page
	 *
	 * @return children by object SQL results.
	 * @author Jocke Gustin
	 **/
	public function GetAllChildren($ParentPageID)
	{
	   return $this->SQL
	      ->Select('p.*')
	      ->Select('uu.Name as UpdateUserName')
   		->Select('ui.Name as InsertUserName')
   		->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
   		->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
	      ->From('Page p')
			->Where('p.ParentPageID', $ParentPageID)
			->OrderBy('p.Sort')
	      ->Get();
	}
	
	
	/**
	 * Returns a single page by ID
	 * 
	 * @return object SQL results.
	 * @param int $PageID 
	 * @author Jocke Gustin
	 */
	public function GetByID($PageID) {
	      return $this->SQL
	         ->Select()
	         ->From('Page')
			->Where('PageID', $PageID)
	         ->Get()
			->FirstRow();
	}
   /**
    * Returns single page
    * 
    * @return object SQL results.
    * @param string $UrlCode from addressbar
    * @author Jocke Gustin
    */
	public function GetPublishedByUrlCode($UrlCode)
	{
		return $this->SQL
			->Select('p.*')
			->From('Page p')
			->Where('p.UrlCode', $UrlCode)
			->Where('Status', 'published')
			->BeginWhereGroup()
         //->Permission('VanillaCMS.Page.View', 'p', 'PageID')
         ->EndWhereGroup()
			->Get()
			->FirstRow();
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
	 * Retrieves all pagemeta from selected page
	 *
	 * @param int $PageID Selected page
	 * @param string $MetaKey (optional) set metakey to get information for a specific metakey
	 * @return object SQL results.
	 * @author Jocke Gustin
	 */
	public function GetPageMeta($PageID, $MetaKey = '')
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
	
/*
   public function Enable($PageID)
   {
      $this->SQL->Update('Page')
            ->Set('Enabled', 1)
            ->Where('PageID', $PageID)
            ->Put();
   }
   public function Disable($PageID)
   {
      $this->SQL->Update('Page')
            ->Set('Enabled', 0)
            ->Where('PageID', $PageID)
            ->Put();
   }*/
   /**
    * Publishes or unpuvlishes pages
    *
    * @param int $PageID Selected page
    * @param string $Status Status to be set, eg 'draft' or published
    * @return none
    * @author Jocke Gustin
    */
	public function Status($PageID, $Status)
	{
	      $this->SQL->Update('Page')
               ->Set('Status', $Status)
               ->Where('PageID', $PageID)
               ->Put();
	}
	
	
/*
   public function AddModules($PageID, $Modules)
   {
      $this->SQL->Update('Page')
            ->Set('Modules', $Modules)
            ->Where('PageID', $PageID)
            ->Put();
   }*/

/*
   public function CheckForModules($PageID)
   {
      $Check = $this->SQL
            ->Select()
            ->From('Page')
         ->Where('Modules', 'a:0:{}')
         ->Where('PageID', $PageID)
            ->Get()
         ->FirstRow();
         if($Check)
            return FALSE;
         else
            return TRUE;
   }*/

   /**
    * Autosets the route to ex /hello/world instead of /page/hello/world
    *
    * @param int $PageID Selected page
    * @return none
    * @author Jocke Gustin
    */
	public function SetRoute($PageID)
	{
	   $Page = $this->GetByID($PageID);
	   
	   Gdn::Router()->SetRoute( //Set new route, see Gdn::Router for more info
         $Page->UrlCode,
         T('page') . '/' . $Page->UrlCode, 
         'Internal'
      );
      
      /**
       * To be able to save the current route to the page
       * we need to loop through all routes to find our then 
       * save the routeindex (to be able to delete it later)
       */
      $MyRoutes = Gdn::Router()->Routes;
      $RouteExists = FALSE;
      foreach ($MyRoutes as $Route) {
         if($Route['Destination'] == 'page/' . $Page->UrlCode) {
            $RouteIndex = $Route['Key'];
         }       
      }
      if($RouteIndex) { //If route has been set, save to page db
         $this->SQL->Update('Page')
               ->Set('RouteIndex', $RouteIndex)
               ->Where('PageID', $Page->PageID)
               ->Put();
      }
      
	}
	
	/**
	 * Deletes the routeindex of the page
	 *
	 * @param int $PageID Selected page
	 * @return none
	 * @author Jocke Gustin
	 */
	public function DeleteRoute($PageID)
	{
	   $Page = $this->GetByID($PageID);
	   
      Gdn::Router()->DeleteRoute($Page->RouteIndex);
      $this->SQL->Update('Page')
            ->Set('RouteIndex', NULL)
            ->Where('PageID', $Page->PageID)
            ->Put();
      
	}
	/*
	public function GetStaff($StaffRoleID)
	  {
	     return $this->SQL
	        ->Select('u.*')
	        ->Select('um.Value as FullName')
	        ->Where('ur.RoleID', $StaffRoleID)
	        ->Where('um.Name', 'FullName')
	         ->From('User u')
	         ->Join('UserRole ur', 'u.UserID = ur.UserID')
	         ->LeftJoin('UserMeta um', 'u.UserID = um.UserID')
	         //->GroupBy('UserID')
	         ->OrderBy('u.UserID', 'desc')
	         ->Get();
	  }*/
		
}