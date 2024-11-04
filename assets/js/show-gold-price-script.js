jQuery(document).ready(function($) {
    $(".single_variation_wrap").on("show_variation", function(event, variation) {
        var SelectedID = variation.variation_id;
        //console.log(SelectedID, 'variation methode 2');
        console.log(typeof SelectedID); 
        if (SelectedID) {
            actions.forEach(function(action, index) {
                $.ajax({
                    url: ajaxurl, 
                    method: 'POST',
                    data: {
                        action: 'get_variation_price', // Specify your action name
                        selected_id: SelectedID
                        
                    },
                    success: function(response) {
                        if (response.success) {
                            $(displays[index]).html(response.data); // Displaying the fetched data
                        } else {
                            $(displays[index]).html('Error loading price');
                        }
                    },
                    error: function() {
                        $(displays[index]).html('AJAX request failed');
                    }
                });
            });
        } 
    });
});