jQuery(document).ready(function($) {
   var modulesInfo = '';   //Global json array with info of all modules

   if ($('#Form_PageID').length == 0) {
      $('.PostMeta').hide();
   };


   //PageMeta
   $('#MetaKeySelect').change(function() {  //Show/Hide Assets
      
      if (modulesInfo.length == 0) { //Only fetch first time
          $.ajax({
              url: gdn.definition('WebRoot') + '/edit/availablemodules/json',
              async: false,
              dataType: 'json',
              success: function(json) {
                 json = $.postParseJson(json);
                 modulesInfo = json.Modules;
              }
          });   
      };
      
      var MetaKey = $('#MetaKeySelect option:selected').val();
            
      if (modulesInfo[MetaKey].ShowAssets == "true" || modulesInfo[MetaKey].ShowAssets == true) {
         $('.AssetShowHide').show();
      } else {
         $('.AssetShowHide').hide();
      };
      if (modulesInfo[MetaKey].ContentType == "none") {
         $('#MetaValueLabel').hide();
         $('#MetaValue').hide();
      } else if (modulesInfo[MetaKey].ContentType == "text") {
         $('#MetaValueLabel').show();
         $('#MetaValue').replaceWith('<input class="text" type="text" id="MetaValue" name="MetaValue" />');
      } else if (modulesInfo[MetaKey].ContentType == "textarea") {
         $('#MetaValueLabel').show();
         $('#MetaValue').replaceWith('<textarea id="MetaValue" style="width: 99%; overflow-x: hidden; overflow-y: hidden; display: block; " name="MetaValue" rows="4" cols="25" tabindex="8"></textarea>');
      } else {
          $('#MetaValueLabel').show();
          $('#MetaValue').show();
      };
      
      $('#MetaValueLabel').html(modulesInfo[MetaKey].HelpText);

   });
   
   $('textarea#MetaValue').livequery(function() {
      $(this).autogrow();
   });

   $('a#NewMetaSubmit').live('click', function() {//Submit new PageMeta
      $('#MetaAjaxResponse').empty();
      var postValues;
      var MetaKey = $('#MetaKeySelect option:selected').val();
      var MetaKeyName = $('#MetaKeySelect option:selected').html();
      var PageID = $('#Form_PageID').val();
      var MetaAsset = '';
      var MetaAssetName = '';
      if ($('#MetaKeySelect option:selected').hasClass('ShowAsset')) {
         MetaAsset = $('#MetaAssetSelect option:selected').val();
         MetaAssetName = $('#MetaAssetSelect option:selected').html();
      }
      var MetaValue = $('#MetaValue').val().trim();
      
      postValues = 'MetaKey=' + MetaKey + '&MetaKeyName=' + MetaKeyName + '&PageID=' + PageID + '&MetaAsset=' + MetaAsset + '&MetaAssetName=' + MetaAssetName + '&MetaValue=' + MetaValue;
      postValues += '&TransientKey=' + gdn.definition('TransientKey') + '&hpt=';
      
      $.ajax({
         type: "POST",
         url: gdn.definition('WebRoot') + '/edit/addpagemeta/',
         data: postValues,
         dataType: 'json',
         error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div.Popup').remove();
            $.popup({}, XMLHttpRequest.responseText);
         },
         success: function(json) {
            json = $.postParseJson(json);
                         
            if (json.FormSaved == false) {
               $(frm).prepend(json.ErrorMessages);
               json.ErrorMessages = null;
            } else {
               $('#MetaValue').empty();
               $('#MetaValue').val('');
               GetPageMeta(); //Fetch new PageMeta
               $('#MetaList').effect("highlight", {}, 2700);
               gdn.inform(json);
            }

            if (json.RedirectUrl)
              setTimeout("document.location='" + json.RedirectUrl + "';", 300);
         },
         complete: function(XMLHttpRequest, textStatus) {
            // Remove any spinners, and re-enable buttons.
            $('span.TinyProgress').remove();
         }
      });
      
         //$('<tr><td>'+keyname+'<a href="deletemeta" class="DeleteMeta">[Ta bort]</a><input type="hidden" id="Form_MetaKey[ ]" name="Page/MetaKey[ ]" value="'+key+'|'+keyname+'|'+value+'|'+asset+'|'+assetname+'" /></td><td>'+assetname+'</td><td>'+value+'</td></tr>').appendTo('#TheList');
         //$('#MetaList').show();
         //$('#MetaList').effect("highlight", {}, 1000);
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
         var PageMetaID = $(sender).parents('tr').attr('id');
         //console.log($(sender).prev('#MetaKey').val());
         
         var PageID = $('#Form_PageID').val();
         var postValues = 'PageID=' + PageID + '&PageMetaID=' + PageMetaID + '&TransientKey=' + gdn.definition('TransientKey');
         var action = gdn.definition('WebRoot') + '/edit/deletepagemeta/';
         
         $.ajax({
            type: "POST",
            url: action,
            data: postValues,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
               $('div.Popup').remove();
               $.popup({}, XMLHttpRequest.responseText);
            },   
            success: function(json) {
               json = $.postParseJson(json);

                  if (json.ErrorMessages) {
                     $('.PostMeta h2').after('<div class="Messages Errors"><ul><li>' + json.ErrorMessages + '</li></ul></div>');
                     json.ErrorMessages = null;
                  } else {
                     if (json.RedirectUrl) {
                        document.location = json.RedirectUrl;
                     } else {
                       $(sender).parents('tr').remove();
                       $('#MetaList').effect("highlight", {}, 2700);   
                     }
                  }
                  gdn.inform(json);
               },
               complete: function(XMLHttpRequest, textStatus) {
                  $('span.Progress').remove();
               }
            });
         
         
         
         console.log(postValues);
      }
   });
   
   // Hijack "publish" or "save as draft" clicks and handle via ajax...
   $.fn.handlePageForm = function() {
      this.click(function() {
         var button = this;
         $(button).attr('disabled', 'disabled');
         $('#Form_UrlCode').val($('#ParentUrlCode').html() + $('#UrlCode').html())
         var frm = $(button).parents('form').get(0);
         var textbox = $(frm).find('textarea');
         var postValues = $(frm).serialize();
         postValues += '&DeliveryType=VIEW&DeliveryMethod=JSON'; // DELIVERY_TYPE_VIEW
         postValues += '&Page%2FStatus='+button.name;

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
                
               // Remove any old errors from the form
               $(frm).find('div.Errors').remove();
               
               if (json.FormSaved == false) {
                  $(frm).prepend(json.ErrorMessages);
                  json.ErrorMessages = null;
               } else {
                  gdn.inform(json);
                  $('span.Publish.Time').html(json.InformMessages['0']['Message'].substr(-7, 7));
               }
               
               if ($('#Form_PageID').length == 0) {
                  $('.PostMeta').show(); //Enable the PageMeta selection
                  $('#Form_hpt').after('<input type=\"hidden\" id=\"Form_PageID\" name=\"Page/PageID\" value=\"'+json.PageID+'\">');
               };
                              
               if (json.RedirectUrl)
                 setTimeout("document.location='" + json.RedirectUrl + "';", 300);
            },
            complete: function(XMLHttpRequest, textStatus) {
               //Update the visit link with proper href
               $('a#VisitLink').attr("href", gdn.definition('WebRoot') + '/' + $('#Form_UrlCode').val());

               var statusText = button.name.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                   return letter.toUpperCase();
               });

               $('.Publish.Status').html(statusText);
               
               // Remove any spinners, and re-enable buttons.
               $('span.TinyProgress').remove();
               $(frm).find(':submit').removeAttr("disabled");
            }
         });
         return false;
      
      });
   };
   $('#Form_Page :submit').handlePageForm();
   
   //When creating a new page, auto-save after setting page title
   $('#Form_Name').blur(function() {
      if ($('#Form_PageID').length == 0) {
         $('#Form_SaveDraft').click();
      };
   });
   


   $('a.DeleteMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').remove();
      }
   });

   if ($.fn.alphanumeric) {
      $('#Form_UrlCode').alphanumeric({allow:"-"});
   }
   
   // Map plain text category to url code
   $("#Form_Name").keyup(function(event) {
      if ($('#Form_CodeIsDefined').val() == '0' && $('#IsCoreTemplate').val() == 'false') {
         $('#UrlCodeContainer').show();
         var val = $(this).val().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase();
         $("#Form_UrlCode").val(val);
         $("#UrlCode").text(val);
      }
   });
   // Make sure not to override any values set by the user.
   $('#UrlCode').text($('#Form_UrlCode').val());
   $("#Form_UrlCode").focus(function() {
      $('#Form_CodeIsDefined').val('1');
   });
   $('#Form_UrlCode, #UrlCodeContainer a.SaveUrlCode').hide();
   if ($('#Form_UrlCode').val() == '') {
      $('#UrlCodeContainer').hide();
   }
   
   // Reveal input when "change" button is clicked
   $('#UrlCodeContainer a.UrlToggle, #UrlCode').click(function() {
      $('#UrlCodeContainer').find('input,span,a').toggle();
      $('#UrlCode').text($('#Form_UrlCode').val());
      $('#Form_UrlCode').focus();
      return false;
   });
   
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
       
   $( 'textarea.Editor' ).ckeditor({
      customConfig : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/config.js',   
       filebrowserBrowseUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=files',
       filebrowserImageBrowseUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=images',
       filebrowserFlashBrowseUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=flash',
       filebrowserUploadUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=files',
       filebrowserImageUploadUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=images',
       filebrowserFlashUploadUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=flash',
       
        /*filebrowserUploadUrl  :gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=files',
        filebrowserImageUploadUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/upload.php?type=images',
       filebrowserBrowseUrl :gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=files',
        filebrowserImageBrowseUrl : gdn.definition('WebRoot') + '/applications/vanillacms/js/ckeditor/kcfinder/browse.php?type=images',*/
   });

   //When selecting Parent page, autoupdate UrlCode
   $('#Form_ParentPageID').change(function() {
      var Template = $('#Form_Template option:selected');
      
      if (Template.hasClass('CoreTemplate')) { //prevent from editing our fixed UrlCode
         return;
      }
      var ParentUrl = $('#Form_ParentPageID option:selected').data('url');
      $('#ParentUrlCode').html(ParentUrl);

      if ($('#Form_ParentPageID option:selected').val() > 0) {
         $('#ParentUrlCode').html($('#ParentUrlCode').html() + '/')
      }

      $('#UrlCodeContainer').effect("highlight", {}, 1000);
   });
   
   //When selecting Page Template, check to see if is All discussions or Conversations for just redirecting
   $('#Form_Template').change(function() {
      var Template = $('#Form_Template option:selected');
      if (Template.hasClass('CoreTemplate')) {
         $('#Form_UrlCode').val('');
         $('#UrlCode').empty();
         $('#UrlCode').hide();
         $('.EditUrlCode').hide();
         $('.ParentNotOptional').hide();
         $('#IsCoreTemplate').val('true');
         
         $('#ParentUrlCode').html(Template.val());
         
      } else {
         $('#IsCoreTemplate').val('false');
         $('#Form_UrlCode').val($('#Form_Name').val().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase());
         $('#UrlCode').html($('#Form_Name').val().replace(/[ ]+/g, '-').replace(/[^a-z0-9\-]+/gi,'').toLowerCase());
         $('#UrlCode').show();
         $('.EditUrlCode').show();
         $('.ParentNotOptional').show();
         var ParentUrl = $('#Form_ParentPageID option:selected').data('url');
         $('#ParentUrlCode').html(ParentUrl);

         if ($('#Form_ParentPageID option:selected').val() > 0) {
            $('#ParentUrlCode').html($('#ParentUrlCode').html() + '/')
         }
      };
         
      $('#UrlCodeContainer').effect("highlight", {}, 1000);

   });
   
   function GetPageMeta () {
      $('#TheList').empty();     
      var action = gdn.definition('WebRoot') + '/edit/getpagemeta/';
      var postValues;
      postValues = '&PageID='+$('#Form_PageID').val();
          
      $.ajax({
         type: "POST",
         url: action,
         data: postValues,
         dataType: 'json',
         error: function(XMLHttpRequest, textStatus, errorThrown) {
            $('div.Popup').remove();
            $.popup({}, XMLHttpRequest.responseText);
         },   
         success: function(json) {
            json = $.postParseJson(json);

               if (json.ErrorMessages) {
                  $('.PostMeta h2').after('<div class="Messages Errors"><ul><li>' + json.ErrorMessages + '</li></ul></div>');
                  json.ErrorMessages = null;
               } else {
                  if (json.RedirectUrl) {
                     document.location = json.RedirectUrl;
                  } else {
                     console.log(json.PageMeta);
                     $.each(json.PageMeta, function(key, val) {
                         console.log('<li id="' + key + '">' + val.MetaAsset + '</li>');
                         $('#TheList').prepend(
                            '<tr id=\"' + val.PageMetaID + '\"><td>' + val.MetaKeyName + '<input type=\"hidden\" id=\"MetaKey\" value=\"' + val.MetaKey + '\" \>' +
                            '<a href=\"' + gdn.definition('WebRoot') + '/edit/deletemeta\" class=\"DeleteMeta\">[X]</a></td><td>' + val.MetaAssetName +
                             '</td><td>' + val.MetaValue + '</td></tr>');
                     });
                     if (json.PageMeta.length === 0) {
                        $('#MetaList').hide();
                     };
                  }
               }
               gdn.inform(json);
            },
            complete: function(XMLHttpRequest, textStatus) {
               $('span.Progress').remove();
            }
         });
   }
   if ($('#Form_PageID')) {
      $('.PostMeta h2').append('<span class="Progress"></span>');
      GetPageMeta();
   };
   
});