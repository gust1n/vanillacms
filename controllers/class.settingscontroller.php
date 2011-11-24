<?php if (!defined('APPLICATION')) exit();

/**
* Settings Controller
*/
class SettingsController extends Gdn_Controller {

   public $Uses = array('Database', 'Form', 'PageModel');

   public function Index() {
      $this->View = 'Pages';
      $this->Pages();
   }

   public function Pages($Filter = '')
   {      
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->AddSideMenu('vanillacms/settings/pages');
      $this->AddJsFile('js/library/jquery.tablednd.js');
      $this->AddJsFile('pages.js');
      $this->AddCssFile('pages.css');
      $this->Title(T('Pages'));
      if (!in_array($Filter, array('enabled', 'disabled')))
         $Filter = 'all';

      $this->Filter = $Filter;
      
      $this->AllParents = $this->PageModel->GetAllParents();

      $this->Pages = $this->AllParents->Result(DATASET_TYPE_ARRAY); 

      $i = 0;
      foreach ($this->Pages as $Parent) {
         //$Children = $this->PageModel->GetChildren($Parent['PageID']);
         foreach ($Children->Result() as $Child) {
            $this->Pages[$i]['Children'][$Child->PageID] = $Child;
         }
         unset($Children); 
         $i++;
      }
         
      $this->Render();
   }

   

   public function DeletePage($PageID = '', $TransientKey = FALSE) {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $Session = Gdn::Session();

      if ($TransientKey !== FALSE && $Session->ValidateTransientKey($TransientKey)) {
         $this->PageModel->DeleteRoute($PageID);
         $this->PageModel->Delete(array('PageID' => $PageID));
      } 
      $this->StatusMessage = T('Success!');		
      $this->Render();
   }
   public function EnablePage($PageID = '') {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->PageModel->Enable($PageID);
      Redirect('vanillacms/settings/pages');		
      $this->Render();      
   }

   public function DisablePage($PageID = '') {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->PageModel->Disable($PageID);
      Redirect('vanillacms/settings/pages');		
      $this->Render();      
   }
  
   public function Media()
   {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->AddSideMenu('vanillacms/settings/media');
      $this->SetData('MediaData', $this->MediaModel->Get(), TRUE);
      $this->Render();
   }
   public function UploadMedia()
   {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->Form->SetModel($this->MediaModel);
      if ($this->Form->AuthenticatedPostBack() === TRUE) {
         $Validation = new Gdn_Validation();
         $Validation->ApplyRule('Description', 'Required');
         $Validation->Validate($this->Form->FormValues());
         $this->Form->SetValidationResults($Validation->Results());

         if ($this->Form->ErrorCount() == 0) {
            $UploadImage = new Gdn_UploadImage();
            try {
               // Validate the upload
               $TmpImage = $UploadImage->ValidateUpload('Picture');

               // Generate the target image name
               $TargetImage = $UploadImage->GenerateTargetName(PATH_ROOT . DS . 'uploads');
               $ImageBaseName = pathinfo($TargetImage, PATHINFO_BASENAME);

               // Save the uploaded image in frontpage size
               $UploadImage->SaveImageAs(
                  $TmpImage,
                  PATH_ROOT . DS . 'uploads' . DS . 'media_'.$ImageBaseName,
                  C('VanillaCMS.Media.Image.MaxHeight'),
                  C('VanillaCMS.Media.Image.MaxWidth')
                  );

            } catch (Exception $ex) {
               $this->Form->AddError($ex->getMessage());
            }
            // If there were no errors, associate the image with the user
            if ($this->Form->ErrorCount() == 0) {
               //$PhotoModel = new Gdn_Model('Photo');
               //$PhotoID = $PhotoModel->Insert(array('Name' => $ImageBaseName));
               $this->Form->SetFormValue('Name', $ImageBaseName);
               $this->Form->SetFormValue('Type', 'Image');
            }					
         }

         if ($TopicID = $this->Form->Save()) {
            Redirect('vanillacms/settings/media');
         }

      }

      $this->Render();
   }

   public function DeleteMedia($MediaID = '', $TransientKey = FALSE)
   {
      $this->Permission('VanillaCMS.Pages.Manage');
      $this->DeliveryType(DELIVERY_TYPE_BOOL);
      $Session = Gdn::Session();

      if ($TransientKey !== FALSE && $Session->ValidateTransientKey($TransientKey)) {
         $ImageName = $this->MediaModel->GetName($MediaID);

         @unlink(PATH_ROOT . DS . 'uploads' . DS . 'media_' . $ImageName);
         $this->MediaModel->Delete($MediaID);
      }		
      $this->StatusMessage = T('Success!');		
      $this->Render();
   }

   public function Initialize() {
      $this->Head = new HeadModule($this);
      $this->AddJsFile('jquery.js');
      $this->AddJsFile('jquery.livequery.js');
      $this->AddJsFile('jquery.form.js');
      $this->AddJsFile('jquery.popup.js');
      $this->AddJsFile('jquery.gardenhandleajaxform.js');
      $this->AddJsFile('global.js');
      $this->AddJsFile('settings.js');

      if (in_array($this->ControllerName, array('profilecontroller', 'activitycontroller'))) {
         //$this->AddJsFile('jquery.menu.js');
         $this->AddCssFile('style.css');
      } else {
         $this->AddCssFile('admin.css');
      }

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
}