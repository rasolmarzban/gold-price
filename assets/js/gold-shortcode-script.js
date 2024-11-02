jQuery(document).ready(function($) {
    $(".single_variation_wrap").on("show_variation", function(event, variation) {
        console.log(event.target, 'variation methode 2');
        console.log(variation.variation_id, 'variation methode 2');
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