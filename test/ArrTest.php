<?php

require_once __DIR__.'/../Arr.php';
require_once __DIR__.'/Test.php';



$arr = new Arr(1,2,3,4);


section('array creation',
    subsection('',
        new Test(
            'new, range',
            function() {
                return
                expect($this->arr1 instanceof Arr, 'new Arr instanceof Arr')->to_be(true) &&
                expect($this->arr2 instanceof Arr, 'Arr::range instanceof Arr')->to_be(true);
            },
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
            function() {
                $test_res = true;
                foreach ($this->native as $idx => $value) {
                    $test_res = $test_res && expect($this->arr[$idx])->to_be($value);
                }
                return $test_res &&
                expect($this->arr[-1])->to_be($this->native[count($this->native) - 1]);
            },
            function () {
                $this->native = [1,2,3,'asdf',5,true,7];
                $this->arr = new Arr(...$this->native);
            }
        ),
        new Test(
            'using brackets (set)',
            function() {
                $test_res = true;
                $new_vals = [1 => '2nd', -1 => 'last'];
                foreach ($new_vals as $idx => $value) {
                    $this->arr[$idx] = $value;
                    $test_res = $test_res && expect($this->arr[$idx])->to_be($new_vals[$idx]);
                }
                return $test_res;
            },
            function () {
                $this->native = [1,2,3,'asdf',5,true,7];
                $this->arr = new Arr(...$this->native);
            }
        ),
        new Test(
            'access convenience methods',
            function() {
                return
                expect($this->arr->first(), '$arr->first()')->to_be($this->native[0]) &&
                expect($this->arr->second())->to_be($this->native[1]) &&
                expect($this->arr->third())->to_be($this->native[2]) &&
                expect($this->arr->penultimate())->to_be($this->native[1]) &&
                expect($this->arr->last(), '$arr->last()')->to_be($this->native[2]);
            },
            function () {
                $this->native = [1,'asdf',true];
                $this->arr = new Arr(...$this->native);
            }
        )
    )
);

section('Arr instance methods',
    subsection('automatically delegating to native methods (subset of array_* functions)',
        new Test(
            'chunk, column, count_values, diff, filter, intersect, keys, merge_recursive, pad, product, rand, reduce, replace_recursive, replace, slice, sum, udiff, uintersect, unique, values',
            [
                function() {
                    return expect($this->arr->chunk(2)->to_a(), 'chunk')->to_be(array_chunk($this->native, 2));
                },
                function() {
                    return expect($this->arr_2d->column(1)->to_a(), 'column')->to_be(array_column($this->native_2d, 1));
                },
                function() {
                    return expect($this->arr->count_values()->to_a(), 'count_values')->to_be(array_count_values($this->native));
                },
                function() {
                    return expect($this->arr->diff($this->arr_2d)->to_a(), 'diff')->to_be(array_diff($this->native, $this->native_2d));
                },
                function() {
                    $filter = function($e) {return $e;};
                    return expect($this->arr->filter($filter)->to_a(), 'filter')->to_be(array_filter($this->native, $filter));
                },
                function() {
                    return expect($this->arr->intersect($this->arr_2d)->to_a(), 'intersect')->to_be(array_intersect($this->native, $this->native_2d));
                },
                function() {
                    return expect($this->arr->keys()->to_a(), 'keys')->to_be(array_keys($this->native));
                },
                function() {
                    return expect($this->arr->merge_recursive($this->arr_2d)->to_a(), 'merge_recursive')->to_be(array_merge_recursive($this->native, $this->native_2d));
                },
                function() {
                    $size = 10;
                    $value = 'x';
                    return expect($this->arr->pad($size, $value)->to_a(), 'pad')->to_be(array_pad($this->native, $size, $value));
                },
                function() {
                    $n = range(1, 10);
                    $a = new Arr(...$n);
                    return expect($a->product(), 'product')->to_be(array_product($n));
                },
                function() {
                    $arr = Arr::range(1,50);
                    $first = $arr->rand();
                    $second = $arr->rand();
                    return expect($arr->has($first), 'rand')->to_be(true) &&
                    expect($arr->has($second), 'rand')->to_be(true) &&
                    expect($first == $second, 'rand (compare 2 rand values. run test again before checking code)')->to_be(false);
                },
                function() {
                    $initial = '>>';
                    $callback = function($prev, $cur) {
                        return $prev.'>'.$cur;
                    };
                    return expect($this->arr->reduce($callback, $initial), 'reduce')->to_be(array_reduce($this->native, $callback, $initial));
                },
                function() {
                    return expect($this->arr->replace_recursive($this->arr_2d)->to_a(), 'replace_recursive')->to_be(array_replace_recursive($this->native, $this->native_2d));
                },
                function() {
                    return expect($this->arr->replace($this->arr_2d)->to_a(), 'replace')->to_be(array_replace($this->native, $this->native_2d));
                },
                function() {
                    $start = 0;
                    $length = 2;
                    return expect($this->arr->slice($start, $length)->to_a(), 'slice')->to_be(array_slice($this->native, $start, $length));
                },
                function() {
                    $n = range(1, 100);
                    $a = new Arr(...$n);
                    return expect($a->sum(), 'sum')->to_be(array_sum($n));
                },
                function() {
                    $cb = function($a, $b) {
                        return __equals($a, $b);
                    };
                    return expect($this->arr->udiff($this->arr_2d, $cb), 'udiff')->to_be(array_udiff($this->native, $this->native_2d, $cb));
                },
                function() {
                    $cb = function($a, $b) {
                        return __equals($a, $b);
                    };
                    return expect($this->arr->uintersect($this->arr_2d, $cb), 'uintersect')->to_be(array_uintersect($this->native, $this->native_2d, $cb));
                },
                function() {
                    $res = true;
                    if (!expect($this->arr->unique(SORT_REGULAR)->to_a(), 'unique')->to_be(array_unique($this->native, SORT_REGULAR))) {
                        echo '...SORT_REGULAR failed';
                        $res = false;
                    }
                    if (!expect($this->arr->unique(SORT_NUMERIC)->to_a(), 'unique')->to_be(array_unique($this->native, SORT_NUMERIC))) {
                        echo '...SORT_NUMERIC failed';
                        $res = false;
                    }
                    if (!expect($this->arr->unique(SORT_STRING)->to_a(), 'unique')->to_be(array_unique($this->native, SORT_STRING))) {
                        echo '...SORT_STRING failed';
                        $res = false;
                    }
                    if (!expect($this->arr->unique(SORT_LOCALE_STRING)->to_a(), 'unique')->to_be(array_unique($this->native, SORT_LOCALE_STRING))) {
                        echo '...SORT_LOCALE_STRING failed';
                        $res = false;
                    }
                    return $res;
                },
                function() {
                    return expect($this->arr->values()->to_a(), 'values')->to_be(array_values($this->native));
                },
            ],
            function () {
                $this->native = [1,'asdf',true];
                $this->arr = new Arr(...$this->native);
                $this->native_2d = [1,[2, 'asdf'],true];
                $this->arr_2d = new Arr(...$this->native_2d);
            }
        )
    ),
    subsection('actually implemented methods (subset of array_* functions + more)',
        new Test('collection methods', [
            function() {
                $a = new Arr(1,2,3);
                $b = new Arr(1,2,3);
                $new_val = 'new_val';
                $a->add($new_val);
                $b->push($new_val);
                return expect($a, 'add')->to_be($b);
            },
            function() {
                return expect($this->arr, 'copy')->to_be($this->arr->copy());
            },
            function() {
                return expect($this->arr->copy()->clear(), 'clear')->to_be(new Arr());
            },
            function() {
                return expect($this->arr->copy()->equals($this->arr), 'equals')->to_be(true);
            },
            function() {
                return expect($this->arr->has('asdf'), 'has should')->to_be(true) &&
                expect($this->arr->has('asdf22'), 'has shouldnt')->to_be(false);
            },
            function() {
                return expect($this->arr->hash(), 'hash')->to_be($this->arr->copy()->hash()) &&
                expect($this->arr->hash(), 'hash')->not_to_be($this->arr_2d->hash());
            },
            function() {
                $a = $this->arr->copy();
                $a->remove('asdf');
                return expect($a->length, 'remove')->to_be($this->arr->length - 1);
            },
            function() {
                return expect($this->arr->size(), 'size (absolute)')->to_be(count($this->native)) &&
                expect($this->arr->size(), 'size (relative)')->to_be($this->arr->length);
            },
        ], function () {
            $this->native = [1,'asdf',true];
            $this->arr = new Arr(...$this->native);
            $this->native_2d = [1,[2, 'asdf'],true];
            $this->arr_2d = new Arr(...$this->native_2d);
        })
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

section('Arr access',
    subsection('slicing w/ array',
        new Test(
            'positive valid indices',
            function($args) use ($arr) {
                return
                expect($this->arr->size())->to_be(7) &&
                expect($arr[[1,3]])->to_be($arr->slice(1, 2)) &&
                expect($arr[[1,4]])->to_be($arr->slice(1, 3));
            },
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
