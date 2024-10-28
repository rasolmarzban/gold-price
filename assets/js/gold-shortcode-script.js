jQuery(document).ready(function($) {
    $('#').change(function() {
        var productId = $(this).val();
        if (productId) { // Check if a product is selected
            $.ajax({
                type: 'POST',
                url: ajaxurl, // WordPress AJAX URL
                data: {
                    action: 'get_product_profit',
                    product_id: productId
                },
                success: function(response) {
                    // Display the returned profit
                    $('#profit-result').html('Profit: ' + response);
                }
            });
        } else {
            $('#profit-result').html(''); // Clear the result if no product is selected
        }
    });
});