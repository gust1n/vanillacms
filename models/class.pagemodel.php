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
    * Main Get-function. Pass arguments to alter function
    *
    * @return object SQL results.
    * @author Jocke Gustin
    */
	public function Get($Args = array()) {
	   
	   $Defaults = array(
   		'OrderBy' => 'Sort', 'ParentPageID' => '',
   		'PageID' => '', 'Exclude' => '', 'IncludeDeleted' => false
   	);
   	
   	$r = VanillaCMSController::ParseArgs($Args, $Defaults);	   	
   	extract( $r, EXTR_SKIP );
	   
	      $this->SQL
	         ->Select('p.*')
			   ->Select('uu.Name as UpdateUserName')
			   ->Select('ui.Name as InsertUserName')
			   ->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
			   ->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
	         ->From('Page p')
			   ->OrderBy('p.' . $OrderBy);
	      
	      if (!$IncludeDeleted) {
	        $this->SQL->Where('p.Status <>', 'deleted');
	      }
	      if ($PageID) {
	        $this->SQL->Where('p.PageID', $PageID);
	      }
	      if ($ParentPageID) {
	        $this->SQL->Where('p.ParentPageID', $ParentPageID);
	      }
	      $Exclude = explode(",", $Exclude);
	      foreach ($Exclude as $ExcludeID) {
	        $this->SQL->Where('p.PageID <>', $ExcludeID);
	      }
	         
	      $Data = $this->SQL->Get();
	      
	      return $Data;
	}
	
	/**
    * Saves the page tree based on a provided tree array. We are using the
    * Nested Set tree model.
    * THANKS VANILLAFORMS TEAM!
    * @ref http://articles.sitepoint.com/article/hierarchical-data-database/2
    * @ref http://en.wikipedia.org/wiki/Nested_set_model
    *
    * @since 2.0.16
    * @access public
    *
    * @param array $TreeArray A fully defined nested set model of the category tree. 
    */
   public function SaveTree($TreeArray) {

      $PermTree = $this->SQL->Select('PageID, TreeLeft, TreeRight, Depth, Sort, ParentPageID, UrlCode')->From('Page')->Where('Status <>', 'deleted')->Get();
      $PermTree = $PermTree->Index($PermTree->ResultArray(), 'PageID');

      usort($TreeArray, array('PageModel', '_TreeSort'));

      foreach($TreeArray as $I => $Node) {
         $PageID = GetValue('item_id', $Node);
         if ($PageID == 'root')
            $PageID = -1;
            
         $ParentPageID = GetValue('parent_id', $Node);
         if (in_array($ParentPageID, array('root', 'none')))
            $ParentPageID = -1;
            
         
         
         // Only update if the tree doesn't match the database.
         $Row = $PermTree[$PageID];
         if ($Node['left'] != $Row['TreeLeft'] || $Node['right'] != $Row['TreeRight'] || $Node['depth'] != $Row['Depth'] || $ParentPageID != $Row['ParentPageID'] || $Node['left'] != $Row['Sort'] || $PermCatChanged) {
            
            $Parent = self::Get(array('PageID' => $ParentPageID))->FirstRow();
            
            $PageUrlCodeExploded = explode('/', $PermTree[$PageID]['UrlCode']);
	         $PageUrlCode = $PageUrlCodeExploded[count($PageUrlCodeExploded) - 1];
            
            if ($Parent->PageID == -1) {
               $UrlCode = $PageUrlCode;
            } else {
               $UrlCode = $Parent->UrlCode . '/' . $PageUrlCode;
            }
                                    
            $this->SQL->Update(
               'Page',
               array(
                  'TreeLeft' => $Node['left'],
                  'TreeRight' => $Node['right'],
                  'Depth' => $Node['depth'],
                  'Sort' => $Node['left'],
                  'ParentPageID' => $ParentPageID,
                  'UrlCode' => $UrlCode,
               ),
               array('PageID' => $PageID)
            )->Put();
            //And update page route
            self::SetRoute($PageID);
         }
      }
   }
   /**
    * Utility method for sorting via usort.
    *
    * @since 2.0.18
    * @access protected
    * @param $A First element to compare.
    * @param $B Second element to compare.
    * @return int -1, 1, 0 (per usort)
    */
   protected function _TreeSort($A, $B) {
      if ($A['left'] > $B['left'])
         return 1;
      elseif ($A['left'] < $B['left'])
         return -1;
      else
         return 0;
   }
	
	/**
    * Rebuilds the Pagetree. We are using the Nested Set tree model.
    * Built by the vanillaforums.org team
    * @ref http://articles.sitepoint.com/article/hierarchical-data-database/2
    * @ref http://en.wikipedia.org/wiki/Nested_set_model
    *  
    * @since 2.0.0
    * @access public
    */
   public function RebuildTree() {
      // Grab all of the pages.
      $Pages = $this->SQL->Get('Page', 'TreeLeft, Sort, Name');
      $Pages = Gdn_DataSet::Index($Pages->ResultArray(), 'PageID');

      // Make sure the tree has a root.
      if (!isset($Pages[-1])) {
         $RootCat = array('PageID' => -1, 'TreeLeft' => 1, 'TreeRight' => 4, 'Depth' => 0, 'InsertUserID' => 1, 'UpdateUserID' => 1, 'DateInserted' => Gdn_Format::ToDateTime(), 'DateUpdated' => Gdn_Format::ToDateTime(), 'Name' => 'Root', 'UrlCode' => '', 'Body' => 'Root of Pagetree. Users should never see this.', 'InMenu' => 0, 'Sort' => 0, 'ParentPageID' => NULL);
         $Pages[-1] = $RootCat;
         $this->SQL->Insert('Page', $RootCat);
      }

      // Build a tree structure out of the pages.
      $Root = NULL;
      foreach ($Pages as &$Page) {
         if (!isset($Page['PageID']))
            continue;
         if ($Page['Status'] == 'deleted')
            continue;
         
         // Backup Page settings for efficient database saving.
         try {
            $Page['_TreeLeft'] = $Page['TreeLeft'];
            $Page['_TreeRight'] = $Page['TreeRight'];
            $Page['_Depth'] = $Page['Depth'];
            //$Page['_PermissionCategoryID'] = $Page['PermissionCategoryID'];
            $Page['_ParentPageID'] = $Page['ParentPageID'];
         } catch (Exception $Ex) {
            $Foo = 'Bar';
         }

         if ($Page['PageID'] == -1) {
            $Root =& $Page;
            continue;
         }

         $ParentID = $Page['ParentPageID'];
         if (!$ParentID) {
            $ParentID = -1;
            $Page['ParentPageID'] = $ParentID;
         }
         if (!isset($Pages[$ParentID]['Children']))
            $Pages[$ParentID]['Children'] = array();
         $Pages[$ParentID]['Children'][] =& $Page;
      }
      unset($Page);

      // Set the tree attributes of the tree.
      $this->_SetTree($Root);
      unset($Root);

      // Save the tree structure.
      foreach ($Pages as $Page) {
         if (!isset($Page['PageID']))
            continue;
         if ($Page['_TreeLeft'] != $Page['TreeLeft'] || $Page['_TreeRight'] != $Page['TreeRight'] || $Page['_Depth'] != $Page['Depth'] || $Page['_ParentPageID'] != $Page['ParentPageID'] || $Page['Sort'] != $Page['TreeLeft']) {
            $this->SQL->Put('Page',
               array('TreeLeft' => $Page['TreeLeft'], 'TreeRight' => $Page['TreeRight'], 'Depth' => $Page['Depth'], 'ParentPageID' => $Page['ParentPageID'], 'Sort' => $Page['TreeLeft']),
               array('PageID' => $Page['PageID']));
         }
      }
      //$this->SetCache();
   }
	
	
   /**
    *
    * Built by the vanillaforums.org team
    * @access protected
    * @param array $Node
    * @param int $Left
    * @param int $Depth
    */
   protected function _SetTree(&$Node, $Left = 1, $Depth = 0) {
      $Right = $Left + 1;
      
      if (isset($Node['Children'])) {
         foreach ($Node['Children'] as &$Child) {
            $Right = $this->_SetTree($Child, $Right, $Depth + 1);
            $Child['ParentPageID'] = $Node['PageID'];
         }
         unset($Node['Children']);
      }

      $Node['TreeLeft'] = $Left;
      $Node['TreeRight'] = $Right;
      $Node['Depth'] = $Depth;

      return $Right + 1;
   }
   
   
   
   
   public function Save($FormPostValues) {
      $Session = Gdn::Session();
      
      // Define the primary key in this model's table.
      $this->DefineSchema();
      
      // Add & apply any extra validation rules:      
      $this->Validation->ApplyRule('Body', 'Required');
      
      // Validate $PageID and whether this is an insert
      $PageID = ArrayValue('PageID', $FormPostValues);
      $PageID = is_numeric($PageID) && $PageID > 0 ? $PageID : FALSE;
      $Insert = $PageID === FALSE;
      if ($Insert)
         $this->AddInsertFields($FormPostValues);
      else
         $this->AddUpdateFields($FormPostValues);
      
      // Prep and fire event
      $this->EventArguments['FormPostValues'] = &$FormPostValues;
      $this->EventArguments['PageID'] = $PageID;
      $this->FireEvent('BeforeSavePage');
      
      // Validate the form posted values
      if ($this->Validate($FormPostValues, $Insert)) {
         
            $Fields = $this->Validation->SchemaValidationFields();
            
            if ($Insert === FALSE) {
               // Log the save.
               LogModel::LogChange('Edit', 'Page', array_merge($Fields, array('PageID' => $PageID)));
               // Save the new value.
               $this->SQL->Put($this->Name, $Fields, array('PageID' => $PageID));
            } else {
                  $PageID = $this->SQL->Insert($this->Name, $Fields);
                  $this->EventArguments['PageID'] = $PageID;

                  $this->FireEvent('AfterSavePage');
            }

      }
      
      $PageID = GetValue('PageID', $FormPostValues);

      return $PageID;
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
	
   /**
    * Updates the page status to published, draft or deleted
    *
    * @param int $PageID Selected page
    * @param string $Status Status to be set, eg 'draft' or published
    * @return none
    * @author Jocke Gustin
    */
	public function Update($Field = null, $PageID = null, $Value = null)
	{
	   
	   $AvailableFields = array(
	      'PageID', 'Name', 'UrlCode', 'Status', 'Type', 'InsertUserID', 'UpdateUserID', 'DateInserted', 'DateUpdated', 'ParentPageID', 'InMenu', 'AllowDiscussion', 'RouteIndex', 'Template', 'Body', 'Format', 'Sort' 
	      );
	      
	   if (in_array($Field, $AvailableFields) && isset($PageID) && isset($Value)) {
	      $Res = $this->SQL->Update('Page')
               ->Set($Field, $Value)
               ->Where('PageID', $PageID)
               ->Put();
         return true;
	   }  
	   return false;
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
}