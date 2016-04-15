<?php

require_once __DIR__.'/../Set.php';
require_once __DIR__.'/Test.php';

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

?>
