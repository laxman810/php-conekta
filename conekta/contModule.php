<?php

require_once('lib/Conekta.php');

class contModule {

    public function __construct($privateKey) {
        \Conekta\Conekta::setApiKey($privateKey);
        \Conekta\Conekta::setApiVersion("2.0.0");
        \Conekta\Conekta::setLocale('es');
    }

    function createCustomer($args) {
        $customer = \Conekta\Customer::create(
                        array(
                            'name' => $args['name'],
                            'email' => $args['email'],
                            'corporate' => true,
                        )
        );

        return array('flag' => 0, 'message' => $customer);
    }

    function createOrder($args) {
        $shippingline[] = array(
            'amount' => 0,
            'has_more' => false,
            'tracking_number' => "TRACK123",
            'carrier' => "Fedex",
            'method' => "Airplane",
        );
        $order = \Conekta\Order::create(array(
                    'line_items' => array(
                        array(
                            'name' => 'order',
                            'description' => 'charged for job',
                            'unit_price' => $args['amount'],
                            'quantity' => 1
                        )
                    ),
                    'currency' => 'USD',
                    'customer_info' => array(
                        'customer_id' => $args['custId']
                    ),
                    'shipping_lines' => $shippingline,
                    'shipping_contact' => array(
                            'phone' => "+5215555555555",
                            'receiver' => "Laxman T",
                            'between_streets' => "RT NAGAR",
                            'address' => array(
                                'street1' => "250 Alexis St",
                                'street2' => "Ganga Nagar",
                                'city' => "Banglore",
                                'state' => "Karnataka",
                                'country' => "CA",
                                'postal_code' => "T4N 0B8",
                                'residential' => true
                            )
                        )
        ));
        return array('flag' => 0, 'message' => $order);
    }

    function createCharge($args) {

        $order = \Conekta\Order::find($args['orderid']);
        $charge = $order->createCharge(
                array(
                    'payment_method' => array(
                        'type' => 'default'
                    ),
                )
        );
        return array('flag' => 0, 'message' => $charge);
    }

    function createPaymentSource($args) {
        $customer = \Conekta\Customer::find($args['custId']);
        $source = $customer->createPaymentSource(array(
            'token_id' => $args['token'],
            'type' => 'card'
        ));
        return array('flag' => 0, 'message' => $source);
    }

    function deletePaymentSource($args) {
        $customer = \Conekta\Customer::find($args['custId']);
        $source = $customer->payment_sources[$args['card']]->delete();
        return array('flag' => 0, 'message' => $source);
    }

    function makeCardDefault($args) {
        $customer = \Conekta\Customer::find($args['custId']);
        $customer->update(array('default_payment_source_id' => $args['card']));
        return array('flag' => 0, 'message' => 'success');
    }

    function getCustomer($args) {
        $customer = \Conekta\Customer::find($args['custId']);
        return array('flag' => 0, 'message' => $customer);
    }

    public function apiConekta($args) {
        try {
            return $this->{$args['method']}($args);
        } catch (Exception $e) {
            return array('flag' => 1, 'message' => $e->getMessage());
        }
    }

}
