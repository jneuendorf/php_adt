<?php

namespace php_adt;

use \StdClass as StdClass; use \Exception as Exception;
echo '<h1>CharrArr (aka Str) class</h1>';

section('array creation',
    subsection('',
        new Test(
            'new, range, from_iterable',
            [
                function() {
                    return expect($this->arr1 instanceof Arr, 'new Arr instanceof Arr')->to_be(true) &&
                    expect($this->arr2 instanceof Arr, 'Arr::range instanceof Arr')->to_be(true) &&
                    expect($this->arr2->to_a(), 'Arr::range vs range()')->to_be(range(-10,10,2));
                },
                function () {
                    $array = [0, ['a','b'], 2];
                    return expect(Arr::from_iterable($array), 'from_iterable recursive')->to_be(new Arr(0, new Arr('a','b'), 2)) &&
                    expect(Arr::from_iterable($array, false), 'from_iterable non-recursive')->to_be(new Arr(0, ['a','b'], 2));
                }
            ],
            function () {
                $this->arr1 = new Arr(1,2,3,'asdf',5,6,7);
                $this->arr2 = Arr::range(-10,10,2);
            }
        )
    )
);
