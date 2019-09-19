<?php

require_once 'payment_module.php';

class MyConektaAPI
{
    public function __construct() {
        $this->payment_gateway = new payment_module1();
    }

    /*
     * Method name: addCard
     * Desc: Add a card to the customer profile
     * Input: Request data
     * Output:  success array if got it else error according to the result
     * ent_token:  card token
     * conekta_id:  if already added card then else empty
     */

    protected function addCard($args) {

        if ($args['ent_token'] == '')
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => 'ent_token missing');

        $custid = '';
        if ($args['conekta_id'] == '') {
            $arguments = array('name' => 'Laxman Tukadiya', 'email' => 'laxman810@gmail.com');
            $cretCust = $this->payment_gateway->function_call('createCustomer', $arguments);
            if ($cretCust['flag'] == '1') {
                return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
            }
            // conekta_id update in customer database

            $arguments = array('custId' => $cretCust['message']['id'], 'token' => $args['ent_token']);
            $result = $this->payment_gateway->function_call('createPaymentSource', $arguments);
            if ($result['flag'] == '1') {
                return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $result['message']);
            }
            $custid = $cretCust['message']['id'];
        } else {
            $arguments = array('custId' => $args['conekta_id'], 'token' => $args['ent_token']);
            $result = $this->payment_gateway->function_call('createPaymentSource', $arguments);
            if ($result['flag'] == '1') {
                return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $result['message']);
            }
            $custid = $args['conekta_id'];
        }

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' => $custid));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
        }
        $cardRes = array();
        foreach ($cretCust['message']['payment_sources'] as $data) {
            foreach ($data as $d) {
                $cardRes[] = $d;
            }
        }
        return array('errNum' => 50, 'errFlag' => 0, 'errMsg' => "success", 'cards' => $cardRes);
    }

    /*
     * Method name: getCards
     * Desc: get a card for customer profile
     * Input: Request data
     * Output:  success array if got it else error according to the result
     * conekta_id:  if already added card then else empty
     */

    protected function getCards($args) {

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' => $args['conekta_id']));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message'], 'test' => 2);
        }
        $cardRes = array();
        $def = '';
        foreach ($cretCust['message']['payment_sources'] as $data) {
            foreach ($data as $d) {
                $cardRes[] = $d;
                if ($d['default'] == true) {
                    $def = $d['id'];
                }
            }
        }
        return array('errNum' => 50, 'errFlag' => 0, 'errMsg' => 'success','cards' => $cardRes,'def' => $def);
    }

    /*
     * Method name: removeCard
     * Desc: remove a card for customer profile
     * Input: Request data
     * Output:  success array if got it else error according to the result
     * conekta_id:  if already added card then else empty
     * cc_id:  card id which to remove
     */

    protected function removeCard($args) {

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' => $args['conekta_id']));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
        }
        $i = 0;
        $flag = 0;
        foreach ($cretCust['message']['payment_sources'] as $data) {
            foreach ($data as $d) {
                if ($args['cc_id'] == $d['id']) {
                    $flag = 1;
                    break;
                } else {
                    $i++;
                }
            }
        }
        if ($flag == 0) {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => 'Oops, card not found.');
        }

        $delCard = $this->payment_gateway->function_call('deletePaymentSource', array('custId' => $args['conekta_id'],'card' => $i));
        if ($delCard['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $delCard['message']);
        }

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' => $args['conekta_id']));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
        }
        $cardRes = array();
        $def = '';
        foreach ($cretCust['message']['payment_sources'] as $data) {
            foreach ($data as $d) {
                $cardRes[] = $d;
                if ($d['default'] == true) {
                    $def = $d['id'];
                }
            }
        }
        return array('errNum' => 50, 'errFlag' => 0, 'errMsg' => 'success', 'cards' => $cardRes, 'def' => $def);
    }

    /*
      /*
     * Method name: makeCardDefault
     * Desc: Make a card default in the passenger profile
     * Input: Request data
     * Output:  success array if got it else error according to the result
     * conekta_id:  if already added card then else empty
     * cc_id:  card id which to remove
     */

    protected function makeCardDefault($args) {

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' => $args['conekta_id']));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
        }

        $delCard = $this->payment_gateway->function_call('makeCardDefault', array('custId' =>  $args['conekta_id'],'card' => $args['cc_id']));
        if ($delCard['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $delCard['message']);
        }

        $cretCust = $this->payment_gateway->function_call('getCustomer', array('custId' =>  $args['conekta_id']));
        if ($cretCust['flag'] == '1') {
            return array('errNum' => 16, 'errFlag' => 1, 'errMsg' => $cretCust['message']);
        }
        $cardRes = array();
        $def = '';
        foreach ($cretCust['message']['payment_sources'] as $data) {
            foreach ($data as $d) {
                $cardRes[] = $d;
                if ($d['default'] == true) {
                    $def = $d['id'];
                }
            }
        }
        return array('errNum' => 50, 'errFlag' => 0, 'errMsg' => 'success', 'cards' => $cardRes, 'def' => $def);
    }
    
    
}
?>
