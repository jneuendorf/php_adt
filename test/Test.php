<?php

require_once 'funcs.php';

class Callback {
    public function __construct($func, $args=[]) {
        $this->func = $func;
        $this->args = $args;
    }

    public function call($context=null) {
        // return $this->func->__invoke($this->args);
        return $this->func->bindTo($context)->__invoke($this->args);
    }
}

class Test {
    public static $total_tests = 0;
    public static $invalid_tests = 0;
    public static $total_expectations = 0;
    public static $invalid_expectations = 0;

    public function __construct($name, $callback, $setup=null, $teardown=null) {
        $this->name = $name;
        $this->callback = $callback;
        $this->setup = $setup;
        $this->teardown = $teardown;

        if (is_callable($this->setup)) {
            $this->setup = $this->setup->bindTo($this);
        }
        if (is_callable($this->teardown)) {
            $this->teardown = $this->teardown->bindTo($this);
        }

        Test::$total_tests++;
    }

    public static function print_stats() {
        if (static::$invalid_tests === 0) {
            echo '<h2 style=\'color:green\'>ALL TESTS HAVE BEEN PASSED! ('.static::$total_tests.' tests, '.static::$total_expectations.' expectations)</h2>';
        }
        else {
            echo '<h2 style=\'color:red\'>invalid tests! ('.static::$invalid_tests.' of '.static::$total_tests.' tests, '.static::$invalid_expectations.' of '.static::$total_expectations.' expectations)</h2>';
        }
    }

    public function run() {
        echo "running '$this->name'...<br>";
        try {
            if (is_callable($this->setup)) {
                $this->setup->__invoke();
            }
            $res = $this->callback->call($this);
            // if ($this->callback->call()) {
            //     // echo '<span style=\'color:green\'>&nbsp;&#10003;</span>';
            //     return true;
            // }
            // echo '<span style=\'color:red\'>&nbsp;&times;</span>';
            // return false;
        } catch (Exception $e) {
            echo '<span style=\'color:red\'>&nbsp;&times;</span> '.$e->getMessage();
            $res = false;
        } finally {
            if (is_callable($this->teardown)) {
                $this->teardown->__invoke();
            }
            if (!$res) {
                Test::$invalid_tests++;
            }
            return $res;
        }
    }
}

class Expectation {
    public function __construct($value, $label='') {
        $this->value = $value;
        $this->label = $label;
    }

    public function to_be($value) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp; expect '.__toString($this->value).' == '.__toString($value);
        if (strlen($this->label) > 0) {
            echo ' ('.$this->label.')';
        }
        Test::$total_expectations++;
        $res = __equals($this->value, $value);
        if ($res === true) {
            echo '<span style=\'color:green\'>&nbsp;&#10003;</span><br>';
        }
        else {
            Test::$invalid_expectations++;
            echo '<span style=\'color:red\'>&nbsp;&times;</span><br>';
        }
        return $res;
    }
}


function test($begin, $callback, $end='') {
    echo $begin.'<br>';
    echo $callback();
    echo $end.'<br>';
}

function section($name, ...$subsections) {
    echo "<h3>$name</h3>";
    foreach ($subsections as $idx => $subsection) {
        $subsection();
    }
    echo '<hr>';
}

function subsection($name, ...$tests) {
    return function() use ($name, $tests) {
        echo "<h4>$name</h4>";
        foreach ($tests as $idx => $test) {
            $test->run();
        }
        echo '<br><br>';
    };
}

function expect($value, $label='') {
    return new Expectation($value, $label);
}

// function and(...$terms) {
//     $res = true;
//     foreach ($terms as $idx => $term) {
//         $res = $res && $term;
//     }
//     return $res;
// }

?>
