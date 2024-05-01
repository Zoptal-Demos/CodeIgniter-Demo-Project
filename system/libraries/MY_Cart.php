<?php
class MY_Cart extends CI_Cart {
    function __construct() {
        parent::CI_Cart();
        $this->product_name_rules = '\,\(\)\"\'\.\:\-_ a-z0-9';
    }
}