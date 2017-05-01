<?php

namespace php_adt;

require_once 'funcs.php';

ob_start();

class Test {
    public static $total_tests = 0;
    public static $invalid_tests = 0;
    public static $total_expectations = 0;
    public static $invalid_expectations = 0;
    protected static $_abort_flag = false;

    public function __construct($name, $callbacks=[], $setup=null, $teardown=null) {
        $this->name = $name;
        if (is_array($callbacks)) {
            $this->callbacks = $callbacks;
        }
        else {
            $this->callbacks = [$callbacks];
        }
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
            echo '<script type="text/javascript">document.body.style.backgroundColor = "#d4fbb6";</script>';
        }
        else {
            echo '<h2 style=\'color:red\'>invalid tests! ('.static::$invalid_tests.' of '.static::$total_tests.' tests, '.static::$invalid_expectations.' of '.static::$total_expectations.' expectations)</h2>';
            echo '<script type="text/javascript">document.body.style.backgroundColor = "#fcc6c6";</script>';
        }
    }

    // can be used inside test callsback to print only the 1st callback using this function
    public function run_only_this() {
        ob_end_clean();
        static::$_abort_flag = true;
        return $this;
    }

    public function run() {
        echo "running '$this->name'...<br>";
        $result = true;
        foreach ($this->callbacks as $idx => $callback) {
            Test::$total_tests++;
            try {
                if (is_callable($this->setup)) {
                    $this->setup->__invoke();
                }
                $res = $callback->bindTo($this)->__invoke();
            } catch (\Exception $e) {
                $trace = htmlspecialchars(str_replace("\n", ' ', $e->getTraceAsString()));
                echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style=\'color:red; cursor:pointer\' onClick="$(this).next().fadeToggle(100);">&nbsp;&times; '.$e->getMessage().'</span><span style="display:none"><br>'.$trace.'</span><br>';
                $res = false;
            } finally {
                if (static::$_abort_flag === true) {
                    exit(0);
                }
                if (is_callable($this->teardown)) {
                    $this->teardown->__invoke();
                }
                if (!$res) {
                    Test::$invalid_tests++;
                }
                $result = $result && $res;
            }
        }
        return $result;
    }
}

class Expectation {
    public function __construct($value=null, $label='') {
        $this->value = $value;
        $this->label = $label;
    }

    protected function pre_process($value) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;';
        if (strlen($this->label) > 0) {
            $label = ' <b>'.$this->label.':</b>';
        }
        else {
            $label = '';
        }

        Test::$total_expectations++;
        $exception = null;

        if (is_callable($this->value)) {
            try {
                $this->value = $this->value->__invoke();
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        if ($exception === null) {
            $result = __equals($this->value, $value);
        }
        else {
            $result = null;
        }

        return [
            'result' => $result,
            'label' => $label,
            'exception' => $exception
        ];
    }

    public function to_be($value=null, $negated=false) {
        $pre_processed = $this->pre_process($value);
        $res = $pre_processed['result'];
        $label = $pre_processed['label'];
        if (!$negated) {
            if ($res === true) {
                echo '<span style=\'color:green\'>&nbsp;&#10003;</span>';
                echo '&nbsp;'.$label.' expect '.__toString($this->value).' == '.__toString($value).'<br>';
            }
            else {
                Test::$invalid_expectations++;
                echo '<span style=\'color:red\'>&nbsp;&times;';
                echo '&nbsp;'.$label.' expect '.__toString($this->value).' == '.__toString($value).'</span><br>';
            }
        }
        else {
            if ($res === false) {
                echo '<span style=\'color:green\'>&nbsp;&#10003;</span>';
                echo '&nbsp;'.$label.' expect '.__toString($this->value).' != '.__toString($value).'<br>';
                $res = true;
            }
            else {
                Test::$invalid_expectations++;
                echo '<span style=\'color:red\'>&nbsp;&times;';
                echo '&nbsp;'.$label.' expect '.__toString($this->value).' != '.__toString($value).'</span><br>';
                $res = false;
            }
        }

        return $res;
    }

    public function not_to_be($value=null) {
        return $this->to_be($value, true);
    }

    public function to_throw($value=null) {
        $pre_processed = $this->pre_process($value);
        $res = $pre_processed['result'];
        $label = $pre_processed['label'];
        $exception = $pre_processed['exception'];
        if ($exception !== null) {
            echo '<span style=\'color:green\'>&nbsp;&#10003;</span>';
            echo '&nbsp;'.$label.' expect exception \''.$exception->getMessage().'\'<br>';
            return true;
        }
        // else
        Test::$invalid_expectations++;
        echo '<span style=\'color:red\'>&nbsp;&times;';
        echo '&nbsp;'.$label.' expected exception but got '.__toString($value).'</span><br>';
        return false;
    }
}

class RecursionTracker {
    const MAX_STACK_DEPTH = 40;
    protected static $counters = [];


    public static function track($func_name, $args) {
        if (!isset(static::$counters[$func_name])) {
            static::$counters[$func_name] = 0;
        }
        static::$counters[$func_name]++;
        if (static::$counters[$func_name] > static::MAX_STACK_DEPTH) {
            echo "<h2 style='color:red'>max call stack exceeded by '$func_name'</h2>";
            echo (new \Exception())->getTraceAsString();
            exit;
        }
        $arg_strs = [];
        foreach ($args as $idx => $arg) {
            $str = '#'.$idx.' ';
            try {
                $str .= var_export($arg, true);
            } catch (\Exception $e) {
                $str .= '&lt;something circular&gt;';
            }
            $arg_strs[] = $str;
        }
        echo "<br>\nrecursion: f = ".$func_name.'('.implode(',', $arg_strs).")<br>\n";
    }
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

function expect($value=null, $label='') {
    return new Expectation($value, $label);
}
