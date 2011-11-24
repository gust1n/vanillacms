// This file contains javascript that is specific to the VanillaCMS/settings controller.
jQuery(document).ready(function($) {

   //Pages show edit link
   $('tr.More').hover(function() {
      $(this).children('td').children('.EditButton').show();
   }, function() {
      $(this).children('td').children('.EditButton').hide();
   });


   //PageMeta
   $('#MetaKeySelect').change(function() {  //Show/Hide Assets
      var id = $('#MetaKeySelect option:selected').val();
      
      if ($('#' + id + '_ShowAssets').val() == 'true') {
         $('.AssetShowHide').show();
      } else {
         $('.AssetShowHide').hide();
      };
      
      $('#MetaValueLabel').html($('#' + id + '_HelpText').val());
/*
      if ($('#MetaKeySelect option:selected').hasClass('ShowAsset')) {
         
      } else {
         
      }*/


   });
   
   $('textarea#MetaValue').livequery(function() {
      $(this).autogrow();
   });

   

   $('a#NewMetaSubmit').live('click', function() {//Submit new PageMeta
      $('#MetaAjaxResponse').empty();
      var key = $('#MetaKeySelect option:selected').val();
      var keyname = $('#MetaKeySelect option:selected').html();
      var asset = '';
      var assetname = '';
      if ($('#MetaKeySelect option:selected').hasClass('ShowAsset')) {
         asset = $('#MetaAssetSelect option:selected').val();
         assetname = $('#MetaAssetSelect option:selected').html();
      }
      var value = $('#MetaValue').val().trim();

      if (key == '' || keyname == '') {
         $('<p class="Alert"></p>').html("Du måste ange ett värde").appendTo('#MetaAjaxResponse');
      }
      else {
         $('<tr><td>'+keyname+'<a href="deletemeta" class="DeleteMeta">[Ta bort]</a><input type="hidden" id="Form_MetaKey[ ]" name="Page/MetaKey[ ]" value="'+key+'|'+keyname+'|'+value+'|'+asset+'|'+assetname+'" /></td><td>'+assetname+'</td><td>'+value+'</td></tr>').appendTo('#TheList');
         $('#MetaList').show();
         $('#MetaList').effect("highlight", {}, 1000);
      }
      
   });

   $('a.EditMeta').live('click', function() {
      var MetaArray = $(this).next('input').val().split('|');
      $('#MetaValue').val(MetaArray[2]);
      $('#MetaKeySelect option[value='+MetaArray[0]+']').attr('selected', 'selected');

      if ($('#MetaKeySelect option:selected').hasClass('ShowAsset')) {
         $('.AssetShowHide').show();
         $('#MetaAssetSelect option[value='+MetaArray[3]+']').attr('selected', 'selected');
      } else {
         $('.AssetShowHide').hide();
      }
      $(this).parents('tr').remove();
      $('#NewMeta').effect("highlight", {}, 2700);
      return false;
   });

   $('a.DeleteMeta').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').remove();
      }
   });


   // Hijack "publish" or "save as draft" clicks and handle via ajax...
   $.fn.handlePageForm = function() {
      this.click(function() {
         var button = this;
         $(button).attr('disabled', 'disabled');
         var frm = $(button).parents('form').get(0);
         var textbox = $(frm).find('textarea');
         // Post the form, and append the results to #Discussion, and erase the textbox
         var postValues = $(frm).serialize();
         postValues += '&DeliveryType=BOOL&DeliveryMethod=JSON'; // DELIVERY_TYPE_VIEW
         postValues += '&Page%2FStatus='+button.name;
         //alert(postValues);
         
         //alert('&Page%2FStatus='+button.name);
         //return false;

/*
         var prefix = textbox.attr('name').replace('Message', '');
         // Get the last message id on the page
         var messages = $('ul.Conversation li');
         var lastMessage = $(messages).get(messages.length - 1);
         var lastMessageID = $(lastMessage).attr('id');
         postValues += '&' + prefix + 'LastMessageID=' + lastMessageID;*/

         $(button).before('<span class="TinyProgress">&nbsp;</span>');
         $.ajax({
            type: "POST",
            url: $(frm).attr('action'),
            data: postValues,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
               $('div.Popup').remove();
               $.popup({}, XMLHttpRequest.responseText);
            },
            success: function(json) {
               json = $.postParseJson(json);
               
               //$('#Content').html(json.Data);
               $('span.Publish.Time').html(json.InformMessages['0']['Message'].substr(-5, 5));
               
               // Remove any old errors from the form
               $(frm).find('div.Errors').remove();
               
               if (json.FormSaved) {
                  gdn.inform(json);
               }
               if (json.RedirectUrl)
                 setTimeout("document.location='" + json.RedirectUrl + "';", 300);
            },
            complete: function(XMLHttpRequest, textStatus) {
               // Remove any spinners, and re-enable buttons.
               $('span.TinyProgress').remove();
               $(frm).find(':submit').removeAttr("disabled");
            }
         });
         return false;
      
      });
   };
   $('#Form_Page :submit').handlePageForm();
   


   $('a.DeleteMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').remove();
      }
   });
   
   $('a.PublishMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').addClass('Enabled');
         $(sender).parents('tr').removeClass('Disabled');
      }
   });
   $('a.UnpublishMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').removeClass('Enabled');
         $(sender).parents('tr').addClass('Disabled');
      }
   });





   if ($.fn.alphanumeric) {
      $('#Form_UrlCode').alphanumeric({allow:"-"});
   }
   
   // Map plain text category to url code
   $("#Form_Name").keyup(function(event) {
      if ($('#Form_CodeIsDefined').val() == '0') {
         $('#UrlCode').show();
         var val = $(this).val().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase();
         $("#Form_UrlCode").val(val);
         $("#UrlCode span").text(val);
      }
   });
   // Make sure not to override any values set by the user.
   $('#UrlCode span').text($('#UrlCode input').val());
   $("#Form_UrlCode").focus(function() {
      $('#Form_CodeIsDefined').val('1');
   });
   $('#UrlCode input, #UrlCode a.Save').hide();
   if ($('#UrlCode input').val() == '') {
      $('#UrlCode').hide();
   }
   
   // Reveal input when "change" button is clicked
   $('#UrlCode a, #UrlCode span').click(function() {
      $('#UrlCode').find('input,span,a').toggle();
      $('#UrlCode span').text($('#UrlCode input').val());
      $('#UrlCode input').focus();
      return false;
   });

   if ($('#Form_IsParentOnly').attr('checked')) {
      $('#Permissions,#UrlCode').hide();
   }
   
   //Hide the prompt text if input is clicked
   $("#Form_Name").click(function() {
      $("#NamePromtText").hide();
   });
   $("#Form_Quote").click(function() {
      $("#QuotePromtText").hide();
   });

   if(!$("#Form_Name").val()) {
      $("#NamePromtText").show();
   }
   if(!$("#Form_Quote").val()) {
      $("#QuotePromtText").show();
   }
   
   $('#Form_IsParentOnly').click(function() {
      if ($(this).attr('checked')) {
         $('.ParentNotOptional').slideUp('fast');
      } else {
         $('.ParentNotOptional').slideDown('fast');
      }
      
   });
       
   $( 'textarea.Editor' ).ckeditor({
      customConfig : gdn.definition('WebRoot') + 'applications/vanillacms/js/ckeditor/config.js',   
       filebrowserUploadUrl  :gdn.definition('WebRoot') + 'applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=files',
       filebrowserImageUploadUrl : gdn.definition('WebRoot') + 'applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=images',
      filebrowserBrowseUrl :gdn.definition('WebRoot') + 'applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=files',
       filebrowserImageBrowseUrl : gdn.definition('WebRoot') + 'applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=images',
   });
   
   $('#Form_ParentPageID').change(function() {
      var PageUrl = '';
      if ($('#Form_ParentPageID option:selected').val() > 0) {
         var ParentUrl = $('#Form_ParentPageID option:selected').html().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase();

         //val = $(this).val().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase();

         PageUrl = $('#UrlCode span').html();
         $('#UrlCode span').html(ParentUrl + '/' + PageUrl);
         $('#UrlCode input').val(ParentUrl + '/' + PageUrl);
      } else {
         var Test = $('#UrlCode input').val().split('/');
         for (var i=1; i < Test.length; i++) {
            if (i > 1) {
               PageUrl = PageUrl + '/';
            }
            PageUrl = PageUrl + Test[i];
         }
         $('#UrlCode span').html(PageUrl);
         $('#UrlCode input').val(PageUrl);
      }
      $('#UrlCode').effect("highlight", {}, 1000);
      //$('select.foo option:selected').val();
   });
});