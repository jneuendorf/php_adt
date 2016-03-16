<?php

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

    // echo $arr->rand();

?>
</body>
</html>
