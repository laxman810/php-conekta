<?PHP

include 'config.php';

class payment_module1 {

    public function function_call($functionName, $args) {
        include_once 'conekta/contModule.php';
        $conekta = new contModule(conektaKey_private);
        $args['method'] = $functionName;
        return call_user_func(array($conekta, 'apiConekta'), $args);
    }

}

?>