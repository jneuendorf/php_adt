<?php

/**
 * This function is similar to language construct 'echo' but calls to __toString on non-string arguments before echoing.
 * @param mixed $args Arguments to echo stringified.
*/
function p(...$args) {
    $res = '';
    foreach ($args as $arg) {
        if (!is_string($arg) || $arg === undefined) {
            $res .= __toString($arg);
        }
        else {
            $res .= $arg;
        }
    }
    echo $res;
    return $res;
}

function is_iterable($x) {
    return is_array($x) || (is_object($x) && $x instanceof Traversable);
}

function __equals($x, $y) {
    if ($x === $y) {
        return true;
    }
    if (method_exists($x, "equals")) {
        return $x->equals($y);
    }
    if (method_exists($y, "equals")) {
        return $y->equals($x);
    }
    if (__hash($x) !== __hash($y)) {
        return false;
    }
    return false;
}

function __get_type($x) {
    if (is_bool($x)) {
        return 'bool';
    }
    if (is_int($x) || is_float($x)) {
        return 'number';
    }
    if (is_string($x)) {
        return 'string';
    }
    if (is_array($x)) {
        return 'array';
    }
    if (is_object($x)) {
        return 'object';
    }
    throw new Exception("Could not determine type of given argument.", 1);
}

// the behavior of __less_than is essential for sorting
function __less_than($x, $y) {
    if (method_exists($x, "less_than")) {
        return $x->less_than($y);
    }

    $type_x = __get_type($x);
    $type_y = __get_type($y);

    // normal compare
    if ($type_x === $type_y) {
        return $x < $y;
    }

    $ranking = [
        'bool' => 0,
        'number' => 1,
        'string' => 2,
        'array' => 3,
        'object' => 4,
        // resource
    ];
    // cross type compare => compare by type rank
    return $ranking[$type_x] < $ranking[$type_y];
}

function __compare($x, $y) {
    if (method_exists($x, "compare")) {
        return $x->compare($y);
    }
    if (method_exists($y, "compare")) {
        return -($y->compare($x));
    }
    if (__equals($x, $y)) {
        return 0;
    }
    if (__less_than($x, $y)) {
        return -1;
    }
    return 1;
}

function __hash($x) {
    if (method_exists($x, "hash")) {
        return $x->hash();
    }
    if (is_int($x)) {
        return $x;
    }
    if (is_numeric($x)) {
        while ($x < PHP_INT_MAX && floor($x) != $x) {
            $x = $x * 100;
        }
        return (int) $x;
    }
    if (is_string($x)) {
        $hash = 0;
        for ($i = 0; $i < strlen($x); $i++) {
            $hash += ord($x[$i]) * ($i + 1);
        }
        return $hash;
    }
    if (is_array($x)) {
        $hash = 0;
        $i = 1;
        foreach ($x as $key => $value) {
            $hash += $i * (__hash($key) + 3*__hash($value));
            $i++;
        }
        return $hash;
    }
    if (is_bool($x)) {
        return (int) $x;
    }
    if (is_object($x)) {
        if ([property_exists($x, '__uniqid__')]) {
            $id = $x->__uniqid__;
        }
        else {
            $id = uniqid('', true);
            $x->__uniqid__ = $id;
        }
        return __hash($id);
    }
    if ($x === null) {
        return 0;
    }
    throw new Exception("Given parameter does not support hashing! ".var_export($x, true), 1);
}

function __clone($x) {
    if (method_exists($x, "copy")) {
        return $x->copy();
    }
    if (is_object($x)) {
        return clone $x;
    }
    if (is_array($x)) {
        return array_merge($x);
    }
    return $x;
}

function __toString($x, $default_val=null) {
    if ($x === undefined) {
        return 'undefined';
    }
    if ($x === null) {
        return 'null';
    }
    if (is_string($x)) {
        return "'".$x."'";
    }
    if (is_bool($x)) {
        return $x ? 'true' : 'false';
    }
    if (is_array($x)) {
        $assoc = false;
        foreach (array_keys($x) as $idx => $key) {
            if ($idx !== $key) {
                $assoc = true;
                break;
            }
        }
        $elements = [];
        if (!$assoc) {
            foreach ($x as $idx => $value) {
                $elements[] = __toString($value);
            }
        }
        else {
            foreach ($x as $key => $value) {
                $elements[] = __toString($key).' => '.__toString($value);
            }
        }
        return '['.implode(', ', $elements).']';
    }
    if (method_exists($x, '__toString')) {
        return $x.'';
    }
    if ($default_val !== null) {
        return $default_val;
    }
    return var_export($x, true);
}




function __mergesort_compare($a, $b) {
    return __compare($a, $b);
    // return __compare($a, $b, false);
}

function __mergesort(&$array, $cmp_function='__mergesort_compare') {
    $length = count($array);
    // Arrays of size < 2 require no action.
    if ($length < 2) {
        return;
    }

    // Split the array in half
    $halfway = $length / 2;
    $array1 = array_slice($array, 0, $halfway);
    $array2 = array_slice($array, $halfway);
    // Recurse to sort the two halves
    __mergesort($array1, $cmp_function);
    __mergesort($array2, $cmp_function);
    // If all of $array1 is <= all of $array2, just append them.
    if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {
        $array = array_merge($array1, $array2);
        return;
    }
    // Merge the two sorted arrays into a single sorted array
    $array = array();
    $ptr1 = $ptr2 = 0;
    $len1 = count($array1);
    $len2 = count($array2);
    while ($ptr1 < $len1 && $ptr2 < $len2) {
        if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
            $array[] = $array1[$ptr1++];
        }
        else {
            $array[] = $array2[$ptr2++];
        }
    }
    // Merge the remainder
    while ($ptr1 < $len1) {
        $array[] = $array1[$ptr1++];
    }
    while ($ptr2 < $len2) {
        $array[] = $array2[$ptr2++];
    }
}

?>
