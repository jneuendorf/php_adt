<?php
// phpinfo();
require_once 'Arr.php';
require_once 'Dict.php';


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

    echo $arr." (length = $arr->length)<br>\n";
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
        return __compare($a[0], $b[0]);
    });
    echo '<br>'.$unsorted;

    echo '<hr><br>iterating "'.$arr.'" using foreach:<br>';
    foreach ($arr as $idx => $elem) {
        echo "$idx->$elem<br>";
    }



    echo '<hr><hr>DICT:<br>';
    $dict = new Dict();
    // echo 'get new Arr(): ';
    // var_dump($dict->get(new Arr()));
    // echo '<br>';
    // $dict->put(true, 'asdf');
    $dict->put(false, 'bsdf');
    // echo 'get true: ';
    // var_dump($dict->get(true));
    // echo '<br>';
    // echo 'get false: ';
    // var_dump($dict->get(false));
    // echo '<br>';
    $dict->put(new Arr(), 'array as key!!');
    echo 'get new Arr(): ';
    var_dump($dict->get(new Arr()));
    echo '<br>';
    $dict->put(new Arr(), 'new ARRAY2 as key!!');
    echo 'get new Arr(): ';
    var_dump($dict->get(new Arr()));
    echo '<br>';

?>
</body>
</html>
