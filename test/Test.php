<?php

class Test {

    public function __construct() {
        # code...
    }

    // public function
}

function describe($description, $callback) {

}

function test($begin, $callback, $end='') {
    echo $begin.'<br>';
    echo $callback();
    echo $end.'<br>';
}

?>
