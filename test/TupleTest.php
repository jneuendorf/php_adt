<?php

namespace php_adt;


echo '<h1>Tuple class</h1>';

section('tuple creation',
    subsection('',
        new Test(
            'new, tuple function',
            [
                function() {
                    return expect($this->tuple1 instanceof \php_adt\Tuple, 'new Tuple instanceof Tuple')->to_be(true) &&
                    expect($this->tuple2 instanceof \php_adt\Tuple, 'new tuple instanceof Tuple')->to_be(true);
                }
            ],
            function () {
                $this->tuple1 = new \php_adt\Tuple([1, 2, 3, 'asdf', 5, 6, 7]);
                $this->tuple2 = new \php_adt\tuple([1, 2, 3, 'asdf', 5, 6, 7]);
            }
        )
    )
);


section('tuple item access',
    subsection('',
        new Test(
            'using brackets (get)',
            function() {
                $tuple = $this->tuple;
                return expect($tuple[0])->to_be(1) &&
                expect($tuple[1])->to_be(2) &&
                expect($tuple[2])->to_be('a') &&
                expect(function() use ($tuple) {
                    return $tuple[3];
                })->to_throw();
            },
            function () {
                $this->tuple = new \php_adt\Tuple([1, 2, 'a']);
            }
        ),
        new Test(
            'using brackets (set)',
            function() {
                $tuple = $this->tuple;
                return expect(function() use ($tuple) {
                    $tuple[0] = 4;
                })->to_throw();
            }
            ,
            function () {
                $this->tuple = new \php_adt\Tuple([1, 2, 'a']);
            }
        )
    )
    // ,
    // subsection('slicing w/ array',
    //     new Test(
    //         'positive valid indices',
    //         function() {
    //             return expect($this->arr[[1,3]])->to_be($this->arr->slice(1, 2)) &&
    //             expect($this->arr[[1,4]])->to_be($this->arr->slice(1, 3));
    //         },
    //         function () {
    //             $this->arr = new Arr(1,2,3,4,5,6,7);
    //         }
    //     ),
    //     new Test(
    //         'slicing w/ positive ints (array, string)',
    //         function() {
    //             $arr = new Arr(1,2,3,4,5);
    //             return expect($arr[[1,3]]->to_a())->to_be([2,3]) &&
    //             expect($arr['1:3']->to_a())->to_be([2,3]);
    //         }
    //     ),
    //     new Test(
    //         'slicing w/ negative ints (array, string)',
    //         function() {
    //             $arr = new Arr(1,2,3,4,5);
    //             return expect($arr[[-3,-1]]->to_a())->to_be([3,4]) &&
    //             expect($arr[' -3: -1']->to_a())->to_be([3,4]);
    //         }
    //     ),
    //     new Test(
    //         'slicing w/ invalid indices (array, string)',
    //         function() {
    //             $arr = new Arr();
    //             return expect(function() use ($arr) {return $arr[' a: -1'];})->to_throw() &&
    //             expect(function() use ($arr) {return $arr[['a', -1]];})->to_throw();
    //         }
    //     )
    // )
);
