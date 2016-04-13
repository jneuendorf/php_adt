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
    // $dict->put(new Arr(), 'array as key!!');
    // echo 'get new Arr(): ';
    // var_dump($dict->get(new Arr()));
    // echo '<br>';
    // $dict->put(new Arr(), 'equal key hash for this value!!');
    // echo 'get new Arr(): ';
    // var_dump($dict->get(new Arr()));
    // echo '<br>';

    // !!!!!
    $dict[new Arr()] = 'new!!!';
    echo 'get new Arr(): ';
    var_dump($dict[new Arr()]);
    echo '<br>';

    echo '$dict->values(): ';
    echo $dict->values().'<br>';
    echo '$dict->keys(): ';
    echo $dict->keys().'<br>';

    $obj = new StdClass();
    $dict[$obj] = 42;
    echo 'get obj: ';
    var_dump($dict[$obj]);
    echo '<br>';
    $dict[$obj] = 43;
    echo 'get obj after update: ';
    var_dump($dict[$obj]);
    echo '<br>';
    // echo 'get new obj: ';
    // var_dump($dict[new StdClass()]);
    // echo '<br>';

    echo 'dict size = '.$dict->size().'<br>';
    // echo 'get obj after removal: ';
    // $dict->remove($obj);
    // var_dump($dict[$obj]);
    // echo '<br>';
    // echo 'dict size = '.$dict->size().'<br>';

    echo $dict.'<br>';
    echo 'hash value = '.__hash([1,0]).'<br>';
    echo 'hash value = '.__hash([0,1]).'<br>';
    // echo 'hash value = '.$dict->hash().'<br>';

    echo 'EACH.....<br>';
    foreach ($dict as $key => $value) {
        echo 'key: ';
        var_dump($key);
        echo '<br>';
        echo 'value: ';
        var_dump($value);
        echo '<br>';
        echo '-------<br>';
    }

?>
</body>
</html>
