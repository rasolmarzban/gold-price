jQuery(document).ready(function($) {
    $(".single_variation_wrap").on("show_variation", function(event, variation) {
        //console.log(event.target, 'variation methode 2');
        //console.log(variation.variation_id, 'variation methode 2');
        var SelectedID = variation.variation_id;

        if (SelectedID) {
            var actions = ['get_profit_variation', 'get_wages_variation', 'get_tax_variation', 'get_addons_variation'];
            var displays = ['#profit-display', '#wages-display', '#tax-display', '#addons-display'];

            actions.forEach(function(action, index) {
                $.ajax({
                    url: ajaxurl, 
                    method: 'POST',
                    data: {
                        action: action,
                        variation_id: SelectedID
                    },
                    success: function(response) {
                        $(displays[index]).html(response);
                    }
                });
            });
        } else {
            // Clear all displays if no variation selected
            $('#profit-display, #wages-display, #tax-display, #addons-display').html('');
        }
    });
});
// this is for woodmart themes only if you have a woodmart theme uncommanted the blow code
// jQuery(document).ready(function($) {
//     // Listen to change event on the select dropdown
//     $('#pa_model').on('change', function() {
//         var SelectedID = $(this).val(); // Store selected value (this should match variation_id)

//         // Trigger the show_variation event manually with mock variation object
//         $(document).trigger('show_variation', [{
//             variation_id: SelectedID
//         }]);
//     });

//     // Handle the show_variation event
//     $(document).on("show_variation", function(event, variation) {
//         var SelectedID = variation.variation_id;

//         if (SelectedID) {
//             var actions = ['get_profit_variation', 'get_wages_variation', 'get_tax_variation', 'get_addons_variation'];
//             var displays = ['#profit-display', '#wages-display', '#tax-display', '#addons-display'];

//             actions.forEach(function(action, index) {
//                 $.ajax({
//                     url: ajaxurl, 
//                     method: 'POST',
//                     data: {
//                         action: action,
//                         variation_id: SelectedID
//                     },
//                     success: function(response) {
//                         $(displays[index]).html(response);
//                     },
//                     error: function(xhr, status, error) {
//                         console.error("Ajax error: " + status + ", " + error);
//                     }
//                 });
//             });
//         } else {
//             // Clear all displays if no variation selected
//             $('#profit-display, #wages-display, #tax-display, #addons-display').html('');
//         }
//     });
// });