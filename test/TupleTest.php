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
                    expect($this->tuple2 instanceof \php_adt\Tuple, 'new tuple instanceof Tuple')->to_be(true) &&
                    expect($this->tuple3 instanceof \php_adt\Tuple, 'new tuple(Arr)')->to_be(true) &&
                    expect($this->tuple4 instanceof \php_adt\Tuple, 'new tuple (empty)')->to_be(true) &&
                    expect($this->tuple5 instanceof \php_adt\Tuple, 'new tuple (multiple arguments)')->to_be(true);
                }
            ],
            function () {
                $this->tuple1 = new \php_adt\Tuple([1, 2, 3, 'asdf', 5, 6, 7]);
                $this->tuple2 = new \php_adt\tuple([1, 2, 3, 'asdf', 5, 6, 7]);
                $this->tuple3 = new \php_adt\tuple(new Arr(1, 2, 3));
                $this->tuple4 = new \php_adt\tuple();
                $this->tuple5 = new \php_adt\Tuple(1, 2, 3, 4);
            }
        )
    )
);

section('countability',
    subsection('',
        new Test(
            'count, len, size',
            function() {
                $tuple = new \php_adt\Tuple([1, 2, 'a']);
                $tuple2 = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple->size(), 'size')->to_be(3) &&
                expect(count($tuple), 'count')->to_be(3) &&
                expect(len($tuple), 'len')->to_be(3) &&
                expect(len($tuple2), 'len')->to_be(3) &&
                expect(len(new \php_adt\Tuple()), 'empty tuple')->to_be(0);
            }
        )
    )
);

section('tuple item access',
    subsection('bracket access',
        new Test(
            'get (brackets, method)',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple[0])->to_be(1) &&
                expect($tuple[1])->to_be(2) &&
                expect($tuple[2])->to_be('a') &&
                expect(function() use ($tuple) {
                    return $tuple[3];
                })->to_throw() &&
                expect($tuple[0], 't[0] vs t->get(0)')->to_be($tuple->get(0));
            }
        ),
        new Test(
            'set (brackets only)',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect(function() use ($tuple) {
                    $tuple[0] = 4;
                })->to_throw();
            }
        )
    ),
    subsection('slicing',
        new Test(
            'slice method',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple->slice(1, 3) instanceof \php_adt\Tuple)->to_be(true) &&
                expect($tuple->slice(10, 100)->equals(new \php_adt\Tuple()))->to_be(true);
            }
        ),
        new Test(
            'positive valid indices',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple[[1,3]])->to_be($tuple->slice(1, 2)) &&
                expect($tuple[[1,4]])->to_be($tuple->slice(1, 3));
            }
        ),
        new Test(
            'slicing w/ positive ints (array, string)',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple[[1,3]])->to_be(new \php_adt\Tuple(2,'a')) &&
                expect($tuple['1:3'])->to_be(new \php_adt\Tuple(2,'a'));
            }
        ),
        new Test(
            'slicing w/ negative ints (array, string)',
            function() {
                $tuple = new \php_adt\Tuple([1, 2, 'a']);
                return expect($tuple[[-3,-1]])->to_be(new \php_adt\Tuple(1,2)) &&
                expect($tuple[' -3: -1'])->to_be(new \php_adt\Tuple(1,2));
            }
        ),
        new Test(
            'slicing w/ invalid indices (array, string)',
            function() {
                $tuple = new \php_adt\Tuple();
                return expect(function() use ($tuple) {return $tuple[' a: -1'];})->to_throw() &&
                expect(function() use ($tuple) {return $tuple[['a', -1]];})->to_throw();
            }
        )
    )
);

section('comparison',
    subsection('',
        new Test(
            'equality',
            function() {
                $tuple1 = new \php_adt\Tuple(1, 2, 'a');
                $tuple2 = new \php_adt\Tuple(1, 2, 'a');
                $tuple3 = new \php_adt\Tuple();
                return expect($tuple1->equals($tuple2))->to_be(true) &&
                expect($tuple1->equals($tuple3))->to_be(false) &&
                expect($tuple3->equals($tuple3))->to_be(true);
            }
        )
    )
);

section('cloning',
    subsection('',
        new Test(
            'clone, copy',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                $clone1 = clone $tuple;
                $clone2 = $tuple->copy();
                return expect($tuple->equals($clone1))->to_be(true) &&
                expect($tuple->equals($clone2))->to_be(true);
            }
        )
    )
);

section('index method',
    subsection('',
        new Test(
            'index()',
            function() {
                $tuple = new \php_adt\Tuple(1, 2, 'a');
                return expect($tuple->index(1))->to_be(0) &&
                expect($tuple->index(2))->to_be(1) &&
                expect($tuple->index('a'))->to_be(2) &&
                expect(function() use ($tuple) {
                    return $tuple->index(1337);
                })->to_throw();
            }
        )
    )
);
