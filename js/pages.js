jQuery(document).ready(function($) {
   
   $('a.StatusMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('table').toggleClass('draft');       
      }
   });
   $('a.DeleteMessage').popup({
      confirm: true,
      followConfirm: false,
      afterConfirm: function(json, sender) {
         $(sender).parents('tr').remove();
      }
   });
   
   var ProgressSpinner = $('.Progress');
   ProgressSpinner.hide();
   
   var SortableContainer = $('.Sortable');

   //Handles the drag and drop page ordering
   if ($.ui && $.ui.nestedSortable) {
      $('ol.Sortable').nestedSortable({
         disableNesting: 'NoNesting',
         errorClass: 'SortableError',
         forcePlaceholderSize: true,
         handle: 'div',
         items: 'li',
         opacity: 0.6,
         placeholder: 'Placeholder',
         tabSize: 25,
         tolerance: 'pointer',
         toleranceElement: '> div',
         update: function(event, ui) {
            ProgressSpinner.show();
            SortableContainer.addClass('disabled');
            var id = $(ui.item).attr("id");
            id = id.substr(5);

            $.post(
               gdn.url('/edit/sortpages/' + id),
               {
                  'TreeArray': $('ol.Sortable').nestedSortable('toArray', {startDepthCount: 0}),
                  'DeliveryType': 'VIEW',
                  'TransientKey': gdn.definition('TransientKey')
               },
               function(response) {
                  if (response !== 'TRUE') {
                     alert("Oops - Didn't save order properly");
                  }
                  ProgressSpinner.hide();
                  SortableContainer.removeClass('disabled');
               }
            );

         }
      });
   }
});