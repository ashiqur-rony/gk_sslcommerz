/**
 * JS file to stylize the admin area of SSLCommerz plugin
 * @author: GoodKoding
 * @author_url: http://goodkoding.com
 **/
(function(){
    jQuery('.gk-sslcommerz-options table.form-table').hide();
    jQuery('.gk-sslcommerz-options h3').on('click', function(){
        jQuery(this).next('table.form-table').toggle();
        jQuery(this).toggleClass('active');
    });
    jQuery('.gk-sslcommerz-options h3').first().trigger('click');
    jQuery('.gk-sslcommerz-options h3').append('<span class="dashicons dashicons-plus"></span>');
})(jQuery);