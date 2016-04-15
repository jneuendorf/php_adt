<?php

require_once __DIR__.'/../Dict.php';
require_once __DIR__.'/Test.php';




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
