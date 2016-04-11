<?php

function __equals($x, $y) {
    if (method_exists($x, "equals")) {
        return $x->equals($y);
    }
    if (method_exists($y, "equals")) {
        return $y->equals($x);
    }
    return $x == $y;
}

function __compare($x, $y) {
    if (method_exists($x, "compare")) {
        return $x->compare($y);
    }
    if (method_exists($y, "compare")) {
        return $y->compare($x);
    }
    if (__equals($x, $y)) {
        return 0;
    }
    if ($x < $y) {
        return -1;
    }
    return 1;
}

function __hash($x) {
    // use cached value
    if (is_object($x) && property_exists($x, '__hash__')) {
        return $x->__hash__;
    }
    // calc new hash
    if (method_exists($x, "hash")) {
        return $x->hash();
    }
    if (is_numeric($x)) {
        return (float) $x;
    }
    if (is_string($x)) {
        // TODO: calc string hash
    }
    if (is_bool($x)) {
        return (int) $x;
    }
    throw new Exception("Given parameter does not support hashing!", 1);
}

function __mergesort_compare($a, $b) {
    return __compare($a, $b);
}

function __mergesort(&$array, $cmp_function='__mergesort_compare') {
    $length = count($array);
    // Arrays of size < 2 require no action.
    if ($length < 2)
        return;

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
