/**
 * JS file to stylize the admin area of SSLCommerz plugin
 * @author: GoodKoding
 * @author_url: http://goodkoding.com
 **/
(function(){
    jQuery('.gk-sslcommerz-options .gk-sslcommerz-admin-form table.form-table').hide();
    jQuery('.gk-sslcommerz-options .gk-sslcommerz-admin-form :header').on('click', function(){
        jQuery(this).next('table.form-table').toggle();
        jQuery(this).toggleClass('active');
    });
    jQuery('.gk-sslcommerz-options .gk-sslcommerz-admin-form :header').first().trigger('click');
    jQuery('.gk-sslcommerz-options .gk-sslcommerz-admin-form :header').append('<span class="dashicons dashicons-plus"></span>');
})(jQuery);