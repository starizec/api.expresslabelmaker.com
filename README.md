Controllers
    //maknuti
    $order = wc_get_order($order_id);
    $order_data = $order->get_data();

    $weight_total = 0;
    $quantity_total = 0;

    foreach( $order->get_items() as $item_id => $product_item ){
        $quantity = $product_item->get_quantity(); // get quantity
        $product = $product_item->get_product(); // get the WC_Product object
        $product_weight = $product->get_weight(); // get the product weight
        // Add the line item weight to the total weight calculation
        $weight_total += floatval( $product_weight * $quantity );
        $quantity_total += $quantity;
    }

    $parcel = array([
        'shipping' => $order_data['shipping'],
        'billing' => $order_data['billing'],
        'id' => $order_id,
        'order_total' => $order->get_total(),
        'payment_method' => $order->get_payment_method(),
        'weight_total' => $weight_total,
        'quantity_total' => $quantity_total,
        'shipping_total' => $order_data['shipping_total'],
        'note' => $order_data['customer_note']
    ]);

    $user = array([
        'email' => 'iz opcije dohvatiti email',
        'domain' => $_SERVER['SERVER_NAME'],
        'licence' => 'iz opcije dohvatiti licencu',
        'dpd' => [
            'username' => 'iz opcije dohvatiti username',
            'password' => 'iz opcije dohvatiti password',
            'options'  => 'opcije sve iz admina'
        ]
    ]);

    $body = array([
        'parcel' => $parcel,
        'user' => $user,
        'parcel_options' => [
            'dpd' => [
                'parcel_type' => 'classic'
            ]
        ]
    ]);
    ////////////////////////