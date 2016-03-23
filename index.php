<?php
// phpinfo();
require_once 'Arr.php';
$arr = new Arr(1,2,3,4);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
</head>
<body>
<?php

    // var_dump($arr->length);

    echo $arr."\n";
    // echo $arr->asdf()."\n";
    echo $arr->chunk(2)."\n";
    // echo $arr->filter(function($x) {return $x > 2;})."\n";
    // echo $arr->keys()."\n";
    echo $arr->map(function($x) {return $x*$x;})."\n";
    echo '<hr>';
    // echo Arr::combine([1, 3],[3, 4]);
    echo $arr[1].' -> ';
    $arr[1] = 5;
    echo $arr[1];

    // push 2 elements
    $arr[] = 12;
    $arr->push('asdf');

    echo '<br><br> >> '.$arr[-1].'<br><br>';

    echo '<br>'.$arr;
    echo '<br>'.$arr->reverse();

    echo '<br>'.Arr::range(0,10,2);
    echo '<br>'.$arr->size();


    $unsorted = new Arr(2,5,4,6,3,1);
    $unsorted->sort();
    echo '<br>'.$unsorted;
    $unsorted->reversed();
    echo '<br>'.$unsorted;
    $unsorted->reverse();
    echo '<br>'.$unsorted;

    // check stable
    $unsorted = new Arr(
        new Arr(1, 'b'),
        new Arr(2, 'c'),
        new Arr(1, 'a')
    );
    $unsorted->sort(function($a, $b) {
        return compare($a[0], $b[0]);
    });
    echo '<br>'.$unsorted;

?>
</body>
</html>
