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
		   ->Select('p.PageID as PrimaryID, p.Name as Title, p.Body as Summary, "page", null')
		   ->Select("'/page/', p.UrlCode", "concat", 'Url')
		   ->Select('p.DateInserted')
			->Select('p.InsertUserID as UserID')
			->From('Page p')
			->Join('Discussion d', 'd.DiscussionID = p.PageID');
		   		
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
   		'PageID' => '', 'Exclude' => '', 'IncludeDeleted' => false,
   		'UrlCode' => '', 'Status' => 'all'
   	);
      if (!class_exists('VanillaCMSController')) {
         include(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'controllers' . DS . 'class.vanillacmscontroller.php');
      }
      $VanillaCMSController = new VanillaCMSController();
   	$r = $VanillaCMSController->ParseArgs($Args, $Defaults);	   	
   	extract( $r, EXTR_SKIP );
	   
	      $this->SQL
	         ->Select('p.*')
			   ->Select('uu.Name as UpdateUserName')
			   ->Select('ui.Name as InsertUserName')
			   ->LeftJoin('User uu', 'p.UpdateUserID = uu.UserID')
			   ->LeftJoin('User ui', 'p.InsertUserID = ui.UserID')
	         ->From('Page p')
			   ->OrderBy('p.' . $OrderBy);
			   
			$FirstRow = false;
	      
	      if (!$IncludeDeleted) {
	        $this->SQL->Where('p.Status <>', 'deleted');
	      }
	      if ($PageID) {
	        $this->SQL->Where('p.PageID', $PageID);
	        $FirstRow = true;
	      }
	      if ($UrlCode) {
	        $this->SQL->Where('p.UrlCode', $UrlCode);
	        $FirstRow = true;
	      }
	      if ($ParentPageID) {
	        $this->SQL->Where('p.ParentPageID', $ParentPageID);
	      }
	      if ($Status != 'all') {
	        $this->SQL->Where('p.Status', $Status);
	      }
	      $Exclude = explode(",", $Exclude);
	      foreach ($Exclude as $ExcludeID) {
	        $this->SQL->Where('p.PageID <>', $ExcludeID);
	      }
	         
	      $Data = $this->SQL->Get();
	      
	      if ($FirstRow) {
	        $Data = $Data->FirstRow();
	      }
	      
	      return $Data;
	}
	
	/**
    * Saves the page tree based on a provided tree array. We are using the
    * Nested Set tree model.
    * THANKS VANILLAFORMS TEAM!
    * @access public
    *
    * @param array $TreeArray A fully defined nested set model of the category tree. 
    */
   public function SaveTree($TreeArray) {

      $PermTree = $this->SQL->Select('PageID, TreeLeft, TreeRight, Depth, Template, Sort, ParentPageID, UrlCode')->From('Page')->Where('Status <>', 'deleted')->Get();
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
            
            $Parent = self::Get(array('PageID' => $ParentPageID));
            $ParentUrlCodeExploded = explode('/', $Parent->UrlCode);
            
            $PageUrlCodeExploded = explode('/', $PermTree[$PageID]['UrlCode']);
	         $PageUrlCode = $PageUrlCodeExploded[count($PageUrlCodeExploded) - 1];
            $SetRoute = true;
            //Prevent from saving over when page is set to a reserved UrlCode
      	   if (array_key_exists($PageUrlCodeExploded[0], C('VanillaCMS.ReservedUrlCodes'))) {
      	     $UrlCode = $PermTree[$PageID]['UrlCode'];
      	     $SetRoute = false;
      	   } else {
               if ($Parent->PageID == -1) {
               $UrlCode = $PageUrlCode;
               } else {
                  if (array_key_exists($ParentUrlCodeExploded[0], C('VanillaCMS.ReservedUrlCodes'))) {
                     $UrlCode = $PageUrlCode;
                  } else {
                     $UrlCode = $Parent->UrlCode . '/' . $PageUrlCode;
                  } 
               }
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
            if ($SetRoute) {
               self::DeleteRoute($PageID);
               self::SetRoute($PageID);
            }
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
   
   /**
    * Insert or update core data about the page.
    * 
    * Events: BeforeSavePage, AfterSavePage.
    * 
    * @access public
    *
    * @param array $FormPostValues Data from the form model.
    * @return int $PageID
    */
   public function Save($FormPostValues) {
      $Session = Gdn::Session();
                  
      // Define the primary key in this model's table.
      $this->DefineSchema();
      
      /*
         TODO Fix Field validation
      */
                        
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
      
      $UrlCode = GetValue('UrlCode', $FormPostValues);
      //die($UrlCode);

      // Validate the form posted values
      if ($this->Validate($FormPostValues, $Insert) && self::ValidateUniqueUrlCode($UrlCode, $PageID)) {
            
            $Fields = $this->Validation->SchemaValidationFields();
            
            if ($Insert === FALSE) {
               // Log the save.
               LogModel::LogChange('Edit', 'Page', array_merge($Fields, array('PageID' => $PageID)));
               // Save the new value.
               $this->SQL->Put('Page', $Fields, array('PageID' => $PageID));
            } else {
                  $PageID = $this->SQL->Insert($this->Name, $Fields);
                  $this->EventArguments['PageID'] = $PageID;

                  $this->FireEvent('AfterSavePage');
            }

      }
      
      return $PageID;
   }
   
   /**
	 * Validates the uniqueness of the UrlCode 
	 *
	 * @param string $UrlCode Code to be checked
	 * @param string $UrlCode (optional) If already is a page, prevent from getting own urlcode
	 * @return bool
	 * @author Jocke Gustin
	 */
   public function ValidateUniqueUrlCode($UrlCode, $PageID) {

      $Valid = TRUE;
      
      if (isset($UrlCode)) {
         $TestData = self::Get(array('UrlCode' => $UrlCode, 'Exclude' => $PageID));
         if (is_object($TestData)) {
            $this->Validation->AddValidationResult('UrlCode', T('The UrlCode you have entered is already in use'));
            $Valid = FALSE;
         }
      }
      
      return $Valid;
   }
			
   /**
    * Updates selected field with passed value
    *
    * @param int $PageID Selected page
    * @param string $Status Status to be set, eg 'draft' or published
    * @return none
    * @author Jocke Gustin
    */
	public function Update($Field = null, $PageID = null, $Value = null)
	{
	   
	   $AvailableFields = array(
	      'PageID', 'Name', 'UrlCode', 'Status', 
	      'Type', 'InsertUserID', 'UpdateUserID', 
	      'DateInserted', 'DateUpdated', 'ParentPageID', 
	      'InMenu', 'AllowDiscussion', 
	      'RouteIndex', 'Template', 'Body', 
	      'Format', 'Sort' 
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
		
   /**
    * Autosets the route to ex /hello/world instead of /page/hello/world
    *
    * @param int $PageID Selected page
    * @return none
    * @author Jocke Gustin
    */
	public function SetRoute($PageID)
	{
	   $Page = self::Get(array('PageID' =>$PageID));
	   
	   if (!is_object($Page)) {
	     return;
	   }
	   
	   if ($PageID == -1) { //Never save for page root
	     return;
	   }
	   
	   //Check for reserved UrlCodes
	   $UrlCodeExploded = explode('/', $Page->UrlCode); //First get the first part, so we're not saving over a reserved sub-url
	   if (array_key_exists($UrlCodeExploded[0], C('VanillaCMS.ReservedUrlCodes'))) {
	     return;
	   }
	   //Dont set route for drafts
	   if ($Page->Status == 'draft') {
	     return;
	   }
	   
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
	   $Page = self::Get(array('PageID' =>$PageID));
	   
      Gdn::Router()->DeleteRoute($Page->RouteIndex);
      $this->SQL->Update('Page')
            ->Set('RouteIndex', NULL)
            ->Where('PageID', $Page->PageID)
            ->Put();
      
	}	
}