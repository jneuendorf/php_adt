<?php

require_once __DIR__.'/../Arr.php';
require_once __DIR__.'/Test.php';



$arr = new Arr(1,2,3,4);


section('array creation',
    subsection('',
        new Test(
            'new, range',
            new Callback(
                function() {
                    return
                    expect($this->arr1 instanceof Arr, 'new Arr instanceof Arr')->to_be(true) &&
                    expect($this->arr2 instanceof Arr, 'Arr::range instanceof Arr')->to_be(true);
                }
            ),
            function () {
                $this->arr1 = new Arr(1,2,3,'asdf',5,6,7);
                $this->arr2 = Arr::range(-10,10);
            }
        )
    )
);

section('array access',
    subsection('',
        new Test(
            'using brackets (get)',
            new Callback(
                function() {
                    $test_res = true;
                    foreach ($this->native as $idx => $value) {
                        $test_res = $test_res && expect($this->arr[$idx])->to_be($value);
                    }
                    return $test_res &&
                    expect($this->arr[-1])->to_be($this->native[count($this->native) - 1]);
                }
            ),
            function () {
                $this->native = [1,2,3,'asdf',5,true,7];
                $this->arr = new Arr(...$this->native);
            }
        ),
        new Test(
            'using brackets (set)',
            new Callback(
                function() {
                    $test_res = true;
                    $new_vals = [1 => '2nd', -1 => 'last'];
                    foreach ($new_vals as $idx => $value) {
                        $this->arr[$idx] = $value;
                        $test_res = $test_res && expect($this->arr[$idx])->to_be($new_vals[$idx]);
                    }
                    return $test_res;
                }
            ),
            function () {
                $this->native = [1,2,3,'asdf',5,true,7];
                $this->arr = new Arr(...$this->native);
            }
        ),
        new Test(
            'access convenience methods',
            new Callback(
                function() {
                    return
                    expect($this->arr->first(), '$arr->first()')->to_be($this->native[0]) &&
                    expect($this->arr->second())->to_be($this->native[1]) &&
                    expect($this->arr->third())->to_be($this->native[2]) &&
                    expect($this->arr->penultimate())->to_be($this->native[1]) &&
                    expect($this->arr->last(), '$arr->last()')->to_be($this->native[2]);
                }
            ),
            function () {
                $this->native = [1,'asdf',true];
                $this->arr = new Arr(...$this->native);
            }
        )
    )
);

section('array instance methods automatically delegating to native methods',
    subsection('',
        new Test(
            'chunk, column, count_values, diff_assoc, diff_key, diff_uassoc, diff_ukey, diff, filter, intersect, keys, merge_recursive, pad, product, rand, reduce, replace_recursive, replace, search, slice, sum, udiff_assoc, udiff_uassoc, udiff, uintersect_assoc, uintersect_uassoc, uintersect, unique, values',
            new Callback(
                function() {
                    return expect($this->arr->chunk(2)->to_a())->to_be(array_chunk($this->native, 2)) &&
                    true;
                }
            ),
            function () {
                $this->native = [1,'asdf',true];
                $this->arr = new Arr(...$this->native);
            }
        )
    )
);



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
echo '<br>'.count($arr);


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

echo '<br>';

section('slicing',
    subsection('slicing w/ array',
        new Test(
            'positive valid indices',
            new Callback(
                function($args) use ($arr) {
                    return
                    expect($this->arr->size())->to_be(7) &&
                    expect($arr[[1,3]])->to_be($arr->slice(1, 2)) &&
                    expect($arr[[1,4]])->to_be($arr->slice(1, 3));
                },
                ['arr' => $arr]
            ),
            function () {
                $this->arr = new Arr(1,2,3,4,5,6,7);
            }
        )
    )
);

test(
    'slicing w/ array',
    function() use ($arr) {
        echo $arr[[1,3]];
    }
);
test(
    'slicing w/ array (neg. ints)',
    function() use ($arr) {
        echo $arr[[-3,-1]];
    }
);
test(
    'slicing w/ string',
    function() use ($arr) {
        echo $arr['1:3'];
    }
);
test(
    'slicing w/ string (neg. ints)',
    function() use ($arr) {
        echo $arr[' -3: -1'];
    }
);
test(
    'slicing w/ invalid string indices',
    function() use ($arr) {
        try {
            echo $arr[' a: -1'];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
);
test(
    'slicing w/ invalid array indices',
    function() use ($arr) {
        try {
            echo $arr[['a', -1]];
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
);

echo '<br>';
echo '<br>';

?>
