<?php
function my_child_theme_enqueue_styles()
{
    $theme_version = wp_get_theme()->get('Version');
    wp_enqueue_style('divi-child-style', get_stylesheet_directory_uri() . '/style.css', array('divi-style'), $theme_version);
}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');

/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function my_hide_shipping_when_free_is_available($rates)
{
    $free = array();
    foreach ($rates as $rate_id => $rate) {
        if ('free_shipping' === $rate->method_id) {
            $free[$rate_id] = $rate;
            break;
        }
    }
    return !empty($free) ? $free : $rates;
}
add_filter('woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100);


/**OLD WIDGETS */
function phi_theme_support()
{
    remove_theme_support('widgets-block-editor');
}
add_action('after_setup_theme', 'phi_theme_support');


//polje ispod varijacije proizvoda

function add_domain_field_before_variation()
{
    $label = (get_locale() == 'hr') ? 'Domena' : 'Domain';
    $placeholder = (get_locale() == 'hr') ? 'Unesite domenu' : 'Enter the domain';

    echo '<div class="domain-field-wrapper">
        <label for="customer_domain">' . esc_html($label) . ' </label>
        <input type="text" name="customer_domain" id="customer_domain" placeholder="' . esc_attr($placeholder) . '" required>
    </div>';
    // JavaScript za osiguranje da je polje obavezno
    echo '<script>
        jQuery(document).on("checkout_place_order", function(){
            if(jQuery("#customer_domain").val() === "") {
                alert("' . esc_js((get_locale() == 'hr') ? 'Molimo unesite domenu.' : 'Please enter the domain.') . '");
                return false;
            }
        });
    </script>';
}
add_action('woocommerce_before_variations_form', 'add_domain_field_before_variation');


//spremanje domene prilikom dodavanja proizvoda u košaricu
function save_domain_field_in_cart_item($cart_item_data, $product_id)
{
    if (isset($_POST['customer_domain'])) {
        $cart_item_data['customer_domain'] = sanitize_text_field($_POST['customer_domain']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'save_domain_field_in_cart_item', 10, 2);

//prikaz domene u košarici i pregledu narudžbe
function display_domain_in_cart_and_checkout($item_data, $cart_item)
{
    if (isset($cart_item['customer_domain'])) {
        $key_display = (get_locale() == 'hr') ? 'Domena' : 'Domain';
        $item_data[] = array(
            'key'     => $key_display,
            'value'   => $cart_item['customer_domain'],
            'display' => '',
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_domain_in_cart_and_checkout', 10, 2);

//spremanje domene u detalje narudžbe
function save_domain_in_order_items($item, $cart_item_key, $values, $order)
{
    if (isset($values['customer_domain'])) {
        $meta_key = (get_locale() == 'hr') ? 'Domena' : 'Domain';
        $item->add_meta_data($meta_key, $values['customer_domain']);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'save_domain_in_order_items', 10, 4);

//prikaz domene unutar SUMO
function display_domain_in_subscriptions($item_id, $item, $order)
{
    $meta_key = (get_locale() == 'hr') ? 'Domena' : 'Domain';
    $domain = wc_get_order_item_meta($item_id, $meta_key, true);
    if ($domain) {
        echo '<br><strong>' . esc_html($meta_key) . ':</strong> ' . esc_html($domain);
    }
}
add_action('woocommerce_my_subscriptions_after_subscription_id', 'display_domain_in_subscriptions', 10, 3);

//odmah checkout
function redirect_to_checkout_on_add_to_cart()
{
    return wc_get_checkout_url();
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout_on_add_to_cart');

function elm_user_registered($user_id)
{
    $user_data = get_userdata($user_id);
    $email = $user_data->user_email;

    $api_url = 'https://api.expresslabelmaker.com/user/create';
    $api_data = array(
        'wp_user_id' => $user_id,
        'email' => $email,
    );

    $response = wp_remote_post($api_url, array(
        'body' => json_encode($api_data),
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        error_log('API request to api.expresslabelmaker.com/user/create failed: ' . $response->get_error_message());
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        error_log('API Response Code: ' . $response_code);
        error_log('API Response Body: ' . $response_body);
    }
}

add_action('woocommerce_created_customer', 'elm_user_registered');
