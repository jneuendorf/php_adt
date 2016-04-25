<?php

require_once __DIR__.'/../Set.php';
require_once __DIR__.'/Test.php';

echo '<h1>Set class</h1>';

section('set creation', subsection('', new Test(
    'new, from_iterable',
    function() {
        $set = new Set(1, 2, 3, 1, 'asdf');
        $set2 = Set::from_iterable([1, 10 ,true, false, 0, new Arr(0,1), new Arr(0,1)]);

        return expect($set->size(), 'new -> size')->to_be(4) &&
        expect($set->has(1), 'new -> check elements')->to_be(true) &&
        expect($set->has(2))->to_be(true) &&
        expect($set->has(3))->to_be(true) &&
        expect($set->has('asdf'))->to_be(true) &&
        expect($set->has(1337))->to_be(false)
        &&
        expect($set2->size(), 'from_iterable -> size')->to_be(6) &&
        expect($set2->has(1), 'from_iterable -> check elements')->to_be(true) &&
        expect($set2->has(10))->to_be(true) &&
        expect($set2->has(true))->to_be(true) &&
        expect($set2->has(false))->to_be(true) &&
        expect($set2->has(0))->to_be(true) &&
        expect($set2->has(new Arr(0,1)))->to_be(true) &&
        expect($set2->has(new Arr(0,1,2)))->to_be(false);
    }
)));


section('set access', subsection('', new Test(
    'via [], has',
    function() {
        $set = new Set(1, 2, 3, 1, 'asdf');
        // $this->run_only_this();

        return expect($set[1], 'array-like')->not_to_be(null) &&
        expect($set[2], 'array-like')->not_to_be(null) &&
        expect($set[3], 'array-like')->not_to_be(null) &&
        expect($set['asdf'], 'array-like')->not_to_be(null) &&
        expect($set['not an element'], 'array-like')->to_be(null);
    }
)));

$set = new Set(1, 2, 3, 1, 'asdf');
$set->add('asdf2');
$set->remove(2);

foreach ($set as $idx => $elem) {
    var_dump($idx);
    echo ': ';
    var_dump($elem);
    echo '-------<br>';
}

$set2 = $set->copy();
var_dump($set->equals($set2));

// array access
echo '<hr>array access..........<br>';
echo 'get undefined offset: ';
var_dump($set[15]);
echo '<br>';
echo 'get defined offset: ';
var_dump($set[1]);
echo '<br>';
echo 'set any offset. ';
$set[] = 'new element';
var_dump($set['new element']);
echo '<br>';
echo 'unset undefined offset: old size = '.count($set).'....';
unset($set['nope']);
echo 'new size'.count($set).' (no change)<br>';
echo 'unset defined offset: old size = '.count($set).'....';
unset($set['new element']);
var_dump($set['new element']);
echo 'new size'.count($set).' (1 less)<br>';

echo 'intersection:<br>';
$set->add(1337)->add(42);
$set2->add(false)->add(new Set());
echo $set, ' ^ ', $set2, ' = ', $set->intersection($set2);
echo 'union:<br>';
echo $set, ' u ', $set2, ' = ', $set->union($set2);

?>
