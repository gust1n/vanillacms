<?php if (!defined('APPLICATION')) exit();

/**
* Settings Controller
*/
class EditController extends Gdn_Controller {

   public $Uses = array('Database', 'Form', 'PageModel');
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
      
      $this->AllPages = $this->PageModel->Get();
      
      $PublishedCount = 0;
      $UnpublishedCount = 0;
                  
      foreach ($this->AllPages->Result() as $Page) {
         if ($Page->PageID > 0) {
            if ($Page->Status == 'published') {
               $PublishedCount++;
            } else {
               $UnpublishedCount++;
            }
         } 
      }
      
      $this->PublishedCount = $PublishedCount;
      $this->UnpublishedCount = $UnpublishedCount;
         
      $this->Render();
   }
   public function AddPage() {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->Title(T('Add Page'));
      // Use the edit form with no MessageID specified.
      $this->View = 'editpage';
      $this->EditPage();
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
      $this->AddJsFile('editpage.js');

      $this->Page = FALSE;

      // Set the model on the form.
      $this->Form->SetModel($this->PageModel);

      // If were not adding, but editing an existing page
      if (is_numeric($PageID) && $PageID > 0) { 
         if ($this->Page = $this->PageModel->Get(array('PageID' => $PageID))) { //If page exists
            $this->Title(T('Edit Page'));
            $this->Form->AddHidden('PageID', $this->Page->PageID);
            //Set PageMeta
            $this->PageMetaData = $this->PageModel->GetPageMeta($this->Page->PageID);
            
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
         }	

      } 
      else { //If saving
         //$this->DeliveryType(DELIVERY_TYPE_BOOL);
         //$this->Validation = new Gdn_Validation();	
         //die(print_r($this->Form->FormValues()));         
         if ($PageID = $this->Form->Save($this->PageModel)) { //Successful save

            $this->PageModel->RebuildTree();
            
            //PAGEMETA
            $this->PageModel->ClearPageMeta($PageID);
            if ($MetaArray = $this->Form->GetFormValue('MetaKey')) {
               foreach ($MetaArray as $Key => $Meta) {
                  $ExplodedMeta = explode('|', $Meta);
                  $SingleMeta = array();
                  $SingleMeta['MetaKey'] = $ExplodedMeta[0];
                  $SingleMeta['MetaKeyName'] = $ExplodedMeta[1];
                  $SingleMeta['MetaValue'] = $ExplodedMeta[2];
                  $SingleMeta['MetaAsset'] = $ExplodedMeta[3];
                  $SingleMeta['MetaAssetName'] = $ExplodedMeta[4];
                  
                  echo $SingleMeta['MetaValue'];
                  
                  
                  $NewArray[$ExplodedMeta[0]] = $SingleMeta;
               }
            }
            if (isset($NewArray)) {
               $this->PageModel->AddPageMeta($PageID, $NewArray);
            }
            
            //ROUTES
            if (isset($this->Page->RouteIndex))
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
            if ($this->Form->GetFormValue('Status') == 'published') {
               $this->StatusMessage = T('Page published at') .' ' . Gdn_Format::Date(); 
            } elseif ($this->Form->GetFormValue('Status') == 'draft') {
               $this->StatusMessage = T('Page saved as draft at') .' '. Gdn_Format::Date(); 
            }
            
            
            if (!$this->Form->GetFormValue('PageID')) { //If new page, redirect
               $this->RedirectUrl = Url('/edit/' . $PageID);
            }
            
                  
         }
      }
            
      //$this->PageModel->RebuildTree();
      $this->AvailableParents = $this->PageModel->Get(array('Exclude' => $this->Page->PageID));      
      
      //Render array with available meta keys
      $this->AvailableMetaKeys = $this->_AvailableMetaKeys();
      
      //Render array with available modules
      $this->AvailableModules = $this->_AvailableModules();

      
      //Render array with available assets
      $this->AvailableAssets = $this->_AvailableAssets();
      
      //Render array with possible templates
      $this->TemplateOptions = $this->_AvailableTemplates();
      if (!isset($this->TemplateOptions)) {
         $this->TemplateOptions = array(
            '' => T('No templates available')
         );
          
      }
      
      //Get default permissions
      $PermissionModel = Gdn::PermissionModel();
      $Permissions = $PermissionModel->GetJunctionPermissions(array('JunctionID' => isset($this->Page->PageID) ? $this->Page->PageID : 0), 'Page');
		$Permissions = $PermissionModel->UnpivotPermissions($Permissions, TRUE);
	   //print_r($Permissions);
      //return;
      $this->SetData('PermissionData', $Permissions, TRUE);

      $this->Render();
   }
   
   function DropdownPages($args = '', $ShowEmpty = TRUE, $EmptyText = '', $echo = null) {

   	$PagesQuery = $this->PageModel->Get();
      $Pages = $PagesQuery->Result(DATASET_TYPE_ARRAY);
      
      die(print_r($Pages));
      
      $Pages = VanillaCMSController::PageTree($Pages);
      
      if ( ! empty($Pages) ) {
   		$output = "<select name=\"$name\" id=\"$id\">\n";
   		if ($ShowEmpty) {
   		   $output .= "\t<option value=\"-1\">$EmptyText</option>";
   		}

   		//$output .= walk_page_dropdown_tree($pages, $depth, $r);
   		$output .= "</select>\n";
   	}
   	
   	echo '<pre>';
   	print_r($Pages);
   	echo '</pre>';
   	//$pages = $this->PageModel->GetPagesJG();
   	//$output = '';

   	//if ( ! empty($pages) ) {
   	//	$output = "<select name=\"$name\" id=\"$id\">\n";
   	//	if ( $show_option_no_change )
   	//		$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
   	//	if ( $show_option_none )
   	//		$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
   	//	$output .= walk_page_dropdown_tree($pages, $depth, $r);
   	//	$output .= "</select>\n";
   	//}

   	if ( $echo )
   		echo $output;

   	return $output;
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
  
         $Info = array();
         $InverseRelation = array();
         if ($FolderHandle = opendir(PATH_VanillaCMSS . DS . $this->VanillaCMS . DS . 'views')) {
            if ($FolderHandle === FALSE)
               return $Info;
            
            // Loop through subfolders (ie. the actual plugin folders)
            while (($Item = readdir($FolderHandle)) !== FALSE) {   
               if (in_array($Item, array('.', '..')))
                  continue;        
               $Name = substr($Item,0,-11 );
               $AvailableTemplates[$Name] = $Name;
            }
            closedir($FolderHandle);
            
            return $AvailableTemplates;
         }
   }
   
   /**
    * Returns available meta info fields for Custom Fields
    *
    * @todo Render dynamically instead of this crappy hard-coded solution
    */
   private function _AvailableMetaKeys()
   {
      /*
         TODO Render dynamically
      */
      return array(
      'MetaDescription' => T('Meta Description'),
      'MetaKeywords' => T('Meta Keywords'),
      'CustomCss' => T('Custom CSS') 
      );
   }
   
   private function _AvailableModules() {
      $AvailableModules = array();

      $Info = array();
      if ($FolderHandle = opendir(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'modules')) {
         if ($FolderHandle === FALSE)
            return $Info;

         // Loop through subfolders (ie. the actual plugin folders)
         while (($Item = readdir($FolderHandle)) !== FALSE) {   
            if (in_array($Item, array('.', '..')))
               continue;        

            $ModuleFile = PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'modules' . DS . $Item;
            $ModuleName = $this->_ScanModule($ModuleFile);

            $Name = substr($Item,6,-4 );
            //$AvailableModules = array();

            //T(substr($Name,0,-6));
            
            $UpdateModel = new UpdateModel;
            $InfoArray = $UpdateModel::ParseInfoArray($ModuleFile, 'ModuleInfo');
            
            $AvailableModules[$ModuleName] = $InfoArray[$ModuleName];
            
            
            /*
            echo '<pre>';            
                                                print_r($InfoArray[$ModuleName]);
                                                die('</pre>');*/
            
            
            
         }
         closedir($FolderHandle);
      }
      
      unset($AvailableModules['HeaderModule']);
      unset($AvailableModules['FooterModule']);
      unset($AvailableModules['ShareModule']);
      unset($AvailableModules['DiscussPageModule']);
      
      /*
      echo '<pre>';            
                        print_r($AvailableModules);
                        die('</pre>');*/
      
      
      return $AvailableModules;
   }
   private function _ScanModule($ModuleFile) {
      // Find the $PluginInfo array
      $Lines = file($ModuleFile);

      foreach ($Lines as $Line) {

         if (strtolower(substr(trim($Line), 0, 6)) == 'class ') {
            $Parts = explode(' ', $Line);
            //if (count($Parts) > 2)
            $ClassName = $Parts[1];
            break;
         }

      }
      unset($Lines);      
      return $ClassName;
   }
   /**
    * Returns available assets for placing stuff in
    *
    * @todo Render dynamically instead of this crappy hard-coded solution
    */
   private function _AvailableAssets()
   {

      return array(
         'Quote' => T('QuoteAsset'),
         'FullWidth' => T('Full Width'),
         'Content' => T('Content'),
         'AfterContent' => T('After Content'),
         'Panel' => T('Panel'),
         'Box1' => T('Box1'),
         'Box2' => T('Box2'),
         'Box3' => T('Box3')  
      );
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
         if ($Status == 'published' || $Status == 'draft' || $Status == 'deleted') {

            if ($this->PageModel->Update('Status', $PageID, $Status) == true) {
               if ($Status == 'draft') {
                  $this->PageModel->DeleteRoute($PageID);
                  $this->StatusMessage = 'Page unpublished and saved as draft';
               } elseif ($Status == 'deleted') {
                  $this->PageModel->DeleteRoute($PageID);
                  $this->PageModel->RebuildTree();
                  $this->StatusMessage = 'Page deleted';
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