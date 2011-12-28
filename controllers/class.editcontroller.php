<?php if (!defined('APPLICATION')) exit();

/**
* Settings Controller
*/
class EditController extends Gdn_Controller {

   public $Uses = array('Database', 'Form', 'PageModel', 'PageMetaModel', 'ActivityModel');
   public function Initialize() {
      
      $this->Head = new HeadModule($this);
      $this->AddJsFile('jquery.js');
      $this->AddJsFile('jquery.livequery.js');
      $this->AddJsFile('jquery.form.js');
      $this->AddJsFile('jquery.popup.js');
      $this->AddJsFile('jquery.gardenhandleajaxform.js');
      $this->AddJsFile('global.js');
      $this->AddJsFile('settings.js');

      $this->AddCssFile('admin.css');

      $this->MasterView = 'admin';
      parent::Initialize();
   }
   public function AddSideMenu($CurrentUrl) {
      // Only add to the assets if this is not a view-only request
      if ($this->_DeliveryType == DELIVERY_TYPE_ALL) {
         $SideMenu = new SideMenuModule($this);
         $SideMenu->HtmlId = '';
         $SideMenu->HighlightRoute($CurrentUrl);
         $this->EventArguments['SideMenu'] = &$SideMenu;
         $this->FireEvent('GetAppSettingsMenuItems');
         $this->AddModule($SideMenu, 'Panel');
      }
   }
   public function Pages($Filter = 'all')
   {      
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->AddSideMenu('edit/pages');
      $this->AddJsFile('pages.js');
      $this->AddJsFile('js/library/jquery.alphanumeric.js');
      $this->AddJsFile('js/library/nestedSortable.1.2.1/jquery-ui-1.8.2.custom.min.js');
      $this->AddJsFile('js/library/nestedSortable.1.2.1/jquery.ui.nestedSortable.js');
      $this->AddCssFile('pages.css');
      $this->Title(T('Pages'));

      $this->Filter = $Filter;
      
      $this->AllPages = $this->PageModel->Get(array('Status' => $this->Filter));
      if ($this->Filter == 'trash') {
         $this->AllPages = $this->PageModel->Get(array('Status' => $this->Filter, 'IncludeTrash' => true));
      }
      
      
      $this->CountPages = $this->PageModel->Get(array('IncludeTrash' => true));
      $PublishedCount = 0;
      $UnpublishedCount = 0;
      $TrashCount = 0;
                  
      foreach ($this->CountPages->Result() as $Page) {
         if ($Page->PageID > 0) {
            if ($Page->Status == 'published') {
               $PublishedCount++;
            } elseif ($Page->Status == 'draft') {
               $UnpublishedCount++;
            } else {
               $TrashCount++;
            }
         } 
      }
      
      $this->PublishedCount = $PublishedCount;
      $this->UnpublishedCount = $UnpublishedCount;
      $this->TrashCount = $TrashCount;
         
      $this->Render();
   }
   public function Add($Type = 'page')
   {
      $this->View = 'index';
      $this->Index(null, 'page');
   }
   public function Index($PageID = '', $Type = '') {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->MasterView = 'editpage';
      $this->AddSideMenu('edit/pages');

      $this->AddCssFile('editpage.css');

      $this->AddJsFile('applications/vanillacms/js/ckeditor/ckeditor.js', 'VanillaCMS');
      $this->AddJsFile('applications/vanillacms/js/ckeditor/adapters/jquery.js');
      
      $this->AddJsFile('jquery.alphanumeric.js');
      $this->AddJsFile('jquery.autogrow.js');
      $this->AddJsFile('jquery.ui.packed.js');
      $this->AddJsFile('editpage.js');

      $this->Page = FALSE;
      $this->IsCoreTemplate = FALSE;
      $this->HeaderText = T('Add '. $Type);

      // Set the model on the form.
      $this->Form->SetModel($this->PageModel);
      
      $this->Form->AddHidden('IsCoreTemplate', '0');

      // If were not adding, but editing an existing page
      if (is_numeric($PageID) && $PageID > 0) { 
         if ($this->Page = $this->PageModel->Get(array('PageID' => $PageID))) { //If page exists
            
            $this->Title(T('Edit Page'));
            $this->HeaderText = T('Edit') . ': ' . $this->Page->Name;
            $this->Form->AddHidden('PageID', $this->Page->PageID);
            $this->Form->AddHidden('CodeIsDefined', '1'); //For the urlcode autofunction
            
            if (array_key_exists($this->Page->Template, C('VanillaCMS.CoreTemplates'))) {
               $this->IsCoreTemplate = TRUE;
               $this->Form->AddHidden('IsCoreTemplate', '1');
            }
            
         } else {
            Redirect('dashboard/home/filenotfound');
         }        
      } 
      /*else {
         $this->Form->AddHidden('Type', $Type);
         $this->SetData('Type', $this->Type = $Type, TRUE);
      }*/

      // If seeing the form for the first time...
      if ($this->Form->AuthenticatedPostBack() === FALSE) {

         $this->Form->AddHidden('CodeIsDefined', '0'); //For the urlcode autofunction
         

         if($this->Page) { //Set the form for editing existing page
            $this->Form->SetData($this->Page);
            $this->Form->AddHidden('CodeIsDefined', '1'); //For the urlcode autofunction
         } else {
            $this->Form->SetFormValue('InMenu', 1); //Show in menu by default
         }

      } 
      else { //If saving
         $this->DeliveryType(DELIVERY_TYPE_BOOL);
         $this->Validation = new Gdn_Validation();
         
         $FormValues = $this->Form->FormValues();
         $PageID = $this->PageModel->Save($FormValues);

         if ($PageID) { //Successful save
               
            $this->PageModel->RebuildTree();
            
            $this->SavedPage = $this->PageModel->Get(array('PageID' => $PageID));
                        
            //ROUTES
            if (isset($this->SavedPage->RouteIndex))
               $this->PageModel->DeleteRoute($PageID); //Always delete route in case UrlCode is changed
               
             $this->PageModel->SetRoute($PageID); //Auto set route to get rid of /page prefix
         
            /*
            //PERMISSIONS
            $PermissionModel = Gdn::PermissionModel();
            $Permissions = $PermissionModel->PivotPermissions(GetValue('Permission', $this->Form->FormValues(), array()), array('JunctionID' => $PageID));
            $PermissionModel->SaveAll($Permissions, array('JunctionID' => $PageID, 'JunctionTable' => 'Page'));
            //$Sender->SQL->Put('User', array('Permissions' => ''), array('Permissions <>' => ''));
            */
                                   
            //Page Status (detect which button clicked)
            if ($this->SavedPage->Status == 'published') {
               $this->StatusMessage = T('Page published') .' ' . T('at') . ' ' . Gdn_Format::Date();               
            } elseif ($this->SavedPage->Status == 'draft') {
               $this->StatusMessage = T('Page saved as draft') .' '. T('at') . ' ' . Gdn_Format::Date(); 
            } elseif ($this->SavedPage->SavedPage == 'trash') {
               $this->StatusMessage = T('Page moved to the Trash') .' '. T('at') . ' ' . Gdn_Format::Date();
               $this->RedirectUrl = Url('pages/trash');
            }
            
            //Set PageID for outputting if new page
            $this->SetJson('PageID', $PageID);
            
            if (!$this->SavedPage->DateUpdated) { //Never been updated = new page
               $NewActivityID = $this->ActivityModel->Add(
                  Gdn::Session()->UserID,
                  'NewPage',
                  '',
                  '',
                  '',
                  $this->SavedPage->UrlCode,
                  FALSE);
                  
               //$this->RedirectUrl = Url('edit/' . $PageID);  
               unset($this->SavedPage);
            } else {
               $NewActivityID = $this->ActivityModel->Add(
                  Gdn::Session()->UserID,
                  'EditPage',
                  '',
                  '',
                  '',
                  $this->SavedPage->UrlCode,
                  FALSE);
            }      
         }
         $this->ErrorMessage($this->Form->Errors());
      }
      
      /*END OF AuthenticatedPostBack function*/
            
      $this->AvailableParents = $this->PageModel->Get(array('Exclude' => $this->Page->PageID)); //Exclude own page   
            
      //Render array with available modules
      $this->VanillaCMSModules = $this->_AvailableModules();
      $this->DashboardModules = C('VanillaCMS.DashboardModules');
      $this->VanillaModules = C('VanillaCMS.VanillaModules');

      //Render array with available assets
      $this->AvailableAssets = $this->_AvailableAssets();
      
      //Render array with possible templates
      $this->TemplateOptions = $this->_AvailableTemplates();
      $this->CoreTemplates = C('VanillaCMS.CoreTemplates');
      
      //Get default permissions
      $PermissionModel = Gdn::PermissionModel();
      $Permissions = $PermissionModel->GetJunctionPermissions(array('JunctionID' => isset($this->Page->PageID) ? $this->Page->PageID : 0), 'Page');
		$Permissions = $PermissionModel->UnpivotPermissions($Permissions, TRUE);
	   //print_r($Permissions);
      //return;
      $this->SetData('PermissionData', $Permissions, TRUE);
      $this->MessagesLoaded = 1; //Trick dashboard hook that messages already been loaded since it only checks for the admin masterview
      $this->Render();
   }
   
   private function _ValidateUniqueUrlCode($UrlCode) {
      $Valid = FALSE;

      $TestData = $this->PageModel->Get(array('UrlCode' =>$UrlCode));
      if ($TestData) {
        // $this->Validation->AddValidationResult('Name', 'The name you entered is already in use by another member.');
         $Valid = TRUE;
      }

      return $Valid;
   }
   
   private function _AvailableTemplates($GetPlugin = NULL, $ForceReindex = FALSE) {
         $AvailableTemplates = array();
         $AvailableTemplates['default'] = T('Default');
  
         $Info = array();
         $InverseRelation = array();
         $CurrentTheme = C('Garden.Theme', '');
         /*
         $TemplatePaths = array(); // Potential places where the templates can be found in the filesystem.
                  $CurrentTheme = ''; // The currently selected theme
                  
                  if ($CurrentTheme != '') {
                     // Look for CSS in the theme folder:
                     $CssPaths[] = PATH_THEMES . DS . $CurrentTheme . DS . 'design' . DS . $MasterViewCss;
                     
                     // Look for Master View in the theme folder:
                     $MasterViewPaths[] = PATH_THEMES . DS . $CurrentTheme . DS . 'views' . DS . $MasterViewName;
                  }
               
                  
               // Look for CSS in the dashboard design folder.
               $CssPaths[] = PATH_APPLICATIONS . DS . 'dashboard' . DS . 'design' . DS . $MasterViewCss;*/
         
         
         if ($FolderHandle = opendir(PATH_THEMES . DS . $CurrentTheme . DS . 'views')) {
            if ($FolderHandle === FALSE)
               return $Info;
            
            // Loop through subfolders (ie. the actual plugin folders)
            while (($Item = readdir($FolderHandle)) !== FALSE) {   
               if (in_array($Item, array('.', '..')))
                  continue;    
                  
               $Check = substr($Item,-11 );
               if ($Check == '.master.php') {
                  $Name = substr($Item,0,-11 );
                  $AvailableTemplates[$Name] = $Name;
               }
               
            }
            closedir($FolderHandle);
            
         }

         return $AvailableTemplates;
   }
   
   private function _AvailableModules() {
      $AvailableModules = array();
      
      //$ExcludedModules = array('DiscussPageModule');

      $Info = array();
      if ($FolderHandle = opendir(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'modules')) {
         if ($FolderHandle === FALSE)
            return $Info;

         // Loop through subfolders (ie. the actual plugin folders)
         while (($Item = readdir($FolderHandle)) !== FALSE) {   
            if (in_array($Item, array('.', '..')))
               continue;        

            $ModuleFile = PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'modules' . DS . $Item;
            
            $UpdateModel = new UpdateModel;
            $InfoArray = $UpdateModel->ParseInfoArray($ModuleFile, 'ModuleInfo');
            if (in_array(key($InfoArray), $ExcludedModules)) {
               continue;
            }
            $AvailableModules[key($InfoArray)] = $InfoArray[key($InfoArray)];            
         }
         closedir($FolderHandle);
      }
            
      return $AvailableModules;
   }
   
   public function AvailableModules($Output = 'json')
   {
      $this->Permission('VanillaCMS.Pages.Manage');

      $VanillaCMSModules = self::_AvailableModules();
      $DashboardModules = C('VanillaCMS.DashboardModules');
      $VanillaModules = C('VanillaCMS.VanillaModules');
      
      $AllModules = $VanillaCMSModules + $DashboardModules + $VanillaModules;
      
      if ($Output == 'json') {
         $this->DeliveryType(DELIVERY_TYPE_BOOL);
         $this->DeliveryMethod(DELIVERY_METHOD_JSON);
         $this->SetJson('Modules', $AllModules);
      } else {
         return $AllModules;
      }
      
      $this->Render();
   }
   
   /**
    * Returns available assets for placing modules in. Retrieves it from ThemeInfo Array in themes about.php
    * The info array should hold structure: 'Assets' => array('AssetCallName' => 'AssetNiceName', 'AssetCallName2' => 'AssetNiceName2')
    * 
    */
   private function _AvailableAssets()
   {
      $AvailableAssets = array();
      $ThemeInfo = Gdn::ThemeManager()->EnabledThemeInfo(TRUE);
      $AssetsArray = $ThemeInfo['Assets'];
      foreach ($AssetsArray as $Key => $Name) {
         $AvailableAssets[$Key] = $Name;
      }

      if (count($AvailableAssets) > 0) {
         return $AvailableAssets;
      } else {
         return C('VanillaCMS.CoreAssets');
      }
   }
   
   /**
    * Adds PageMeta submitted by ajax
    *
    * @author Jocke Gustin
    **/
   public function AddPageMeta() {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $this->DeliveryMethod(DELIVERY_METHOD_JSON);
      $this->SetHeader('Content-Type', 'application/json');
      
      $TransientKey = GetIncomingValue('TransientKey', '');
      $PageMetaID = GetIncomingValue('PageMetaID');
      $PageID = GetIncomingValue('PageID', '');
      $MetaKey = GetIncomingValue('MetaKey', '');
      $MetaKeyName = GetIncomingValue('MetaKeyName', '');
      $MetaValue = htmlspecialchars(GetIncomingValue('MetaValue', '')) ;
      $MetaAsset = GetIncomingValue('MetaAsset', '');
      $MetaAssetName = GetIncomingValue('MetaAssetName', '');
      
      $InfoArray = self::AvailableModules('echo');
            
      $ApplicationFolder = 'vanillacms';
      if ($InfoArray[$MetaKey]['ApplicationFolder']) {
         $ApplicationFolder = $InfoArray[$MetaKey]['ApplicationFolder'];
      }
      $GetData = 0;
      if ($InfoArray[$MetaKey]['GetData'] == 1) {
         $GetData = 1;
      }
      $ConfigSetting = '';
      if ($InfoArray[$MetaKey]['ConfigSetting']) {
         $ConfigSetting = $InfoArray[$MetaKey]['ConfigSetting'];
      }
      
      $PageMeta = array(
         'TransientKey' => $TransientKey,
         'PageID' => $PageID,
         'MetaKey' => $MetaKey,
         'MetaKeyName' => $MetaKeyName,
         'MetaValue' => $MetaValue,
         'MetaAsset' => $MetaAsset,
         'MetaAssetName' => $MetaAssetName,
         'ApplicationFolder' => $ApplicationFolder,
         'GetData' => $GetData,
         'ConfigSetting' => $ConfigSetting,
      );
      
      if ($PageMetaID) { //If updating
          $PageMeta['PageMetaID'] = $PageMetaID;
      }
      
      $PageID = $this->PageMetaModel->Save($PageMeta);
      
      // if ($PageID) {
      //          if ($PageMeta = $this->PageMetaModel->Get($PageID)) {
      //             $this->SetJson('PageMeta', $PageMeta->Result());
      //          } else {
      //             $this->ErrorMessage(T('There was a problem retrieving your Custom Fields'));
      //          }
      //       }
      //$this->SetJson('PageMeta', $InfoArray);
      $this->Render();
   }
   
   /**
    * Fetches the PageMeta of the Page and returns via json
    *
    * @author Jocke Gustin
    **/
   public function GetPageMeta() {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $this->DeliveryMethod(DELIVERY_METHOD_JSON);
      $this->SetHeader('Content-Type', 'application/json');
      
      $PageID = GetIncomingValue('PageID', '');
      $PageMetaID = GetIncomingValue('PageMetaID', '');
      
      if ($PageID) {
         if ($PageMeta = $this->PageMetaModel->Get($PageID, $PageMetaID)) {
            $this->SetJson('PageMeta', $PageMeta->Result());
         } else {
            $this->ErrorMessage(T('There was a problem retrieving your Custom Fields'));
         }
      }
      
      $this->Render();
   }
   
   /**
    * This is the back-end function to the ajax delete call
    *
    * @author Jocke Gustin
    **/
   public function DeletePageMeta($PageMetaID = '', $TransientKey = '') {
      $this->Permission('VanillaCMS.Pages.Manage');
      $Session = Gdn::Session();
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $this->DeliveryMethod(DELIVERY_METHOD_JSON);
      $this->SetHeader('Content-Type', 'application/json');
      
      $TransientKey = GetIncomingValue('TransientKey', '');
      
      if ($Session->ValidateTransientKey($TransientKey)) {
         $PageMetaID = GetIncomingValue('PageMetaID', '');
         
         if ($PageMetaID) {
            if (!$this->PageMetaModel->Delete($PageMetaID)) {
               $this->ErrorMessage(T('There was a problem deleting the Custom Field'));
            }
         }   
      } else {
          $this->ErrorMessage(T('You do not have permission to do that.'));
      }
        
      $this->Render();
   }
   
   
   /**
    * Publishes, unpablishes (sets as draft) or deletes page
    *
    * @return void
    * @author Jocke Gustin
    **/
   public function Status($PageID = '', $Status = 'published', $TransientKey = FALSE) {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $Session = Gdn::Session();

      if ($TransientKey !== FALSE && $Session->ValidateTransientKey($TransientKey)) {
         if ($Status == 'published' || $Status == 'draft' || $Status == 'trash') {

            if ($this->PageModel->Update('Status', $PageID, $Status)) {
               if ($Status == 'draft') {
                  $this->PageModel->DeleteRoute($PageID);
                  $this->StatusMessage = 'Page unpublished and saved as draft';
               } elseif ($Status == 'trash') {
                  $this->PageModel->DeleteRoute($PageID);
                  $this->PageModel->RebuildTree();
                  $this->StatusMessage = 'Page moved to the Trash';
               }
               elseif ($Status == 'published') {
                  $this->PageModel->SetRoute($PageID);
                  $this->StatusMessage = 'Page Published';
               }
            } else {
               return FALSE;
               $this->StatusMessege = 'ERROR';
            }
            	
         }	
      }

      $this->Render();      
   }
   
   public function Delete($PageID = '', $TransientKey = FALSE)
   {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $Session = Gdn::Session();

      if ($TransientKey !== FALSE && $Session->ValidateTransientKey($TransientKey)) {
         if (is_numeric($PageID)) {
            $this->PageModel->Delete($PageID);
         } else {
            $this->PageModel->Delete();
            $this->RedirectUrl = Url('/edit/pages/trash');
         }
      }

      $this->Render();
   }
   
   /**
    * Sorting display order of pages.
    * All cred to vanillaforums team!
    * Accessed by ajax so its default is to only output true/false.
    * 
    * @access public
    */
   public function SortPages($PageID = null) {
      // Check permission
      $this->Permission('VanillaCMS.Pages.Manage');
      
      if (!$PageID) {
         return false;
      }

      // Set delivery type to true/false
      $this->_DeliveryType = DELIVERY_TYPE_BOOL;
		$TransientKey = GetIncomingValue('TransientKey');
      if (Gdn::Session()->ValidateTransientKey($TransientKey)) {
			$TreeArray = GetValue('TreeArray', $_POST);
			$this->PageModel->SaveTree($TreeArray);		
		}
         
      // Renders true/false rather than template  
      $this->Render();
   }
}