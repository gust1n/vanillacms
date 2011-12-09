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

//Handles the drag and drop page ordering
if ($.ui && $.ui.nestedSortable)
   $('ol.Sortable').nestedSortable({
      disableNesting: 'NoNesting',
      errorClass: 'SortableError',
      forcePlaceholderSize: true,
      handle: 'div',
      items: 'li',
      opacity: .6,
      placeholder: 'Placeholder',
      tabSize: 25,
      tolerance: 'pointer',
      toleranceElement: '> div',
      update: function(event, ui) {
         //alert($(ui.placeholder).attr("id"));
         //alert(ui);
         var id = $(ui.item).attr("id");
         id = id.substr(5);
         
         console.log('ThisID' + id);
         console.log('ThisID: ' + id.substr(5));
         
         /*Måste göra ngt slags getPrev id på den jag nu fått för att få ovanstående li*/
         //console.log('ParentID' + $('#' + id).parent.attr("id"));
        /* $("li").click(function(event) {
                 //alert(event.target.id);
             });*/
         
         $.post(
                     gdn.url('/edit/sortpages/' + id),
                     {
                        'TreeArray': $('ol.Sortable').nestedSortable('toArray', {startDepthCount: 0}),
                        'DeliveryType': 'VIEW',
                        'TransientKey': gdn.definition('TransientKey')
                     },
                     function(response) {
                        if (response != 'TRUE') {
                           alert("Oops - Didn't save order properly");
                        }
                     }
                  );
         
      }
   });
});