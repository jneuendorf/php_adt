<?php

namespace php_adt;

use \StdClass as StdClass; use \Exception as Exception;
echo '<h1>CharrArr (aka Str) class</h1>';

section('creation',
    subsection('',
        new Test(
            'new, range, from_iterable',
            [
                function() {
                    return expect($this->str1 instanceof Arr, 'new Arr instanceof Arr')->to_be(true) &&
                    expect($this->str1 instanceof CharArr, 'new Arr instanceof CharArr')->to_be(true) &&
                    expect($this->str1 instanceof Str, 'new Arr instanceof Str')->to_be(true) &&
                    expect($this->str2 instanceof CharArr, 'CharArr::range instanceof CharArr')->to_be(true) &&
                    expect($this->str2.'', 'CharArr::range vs range()')->to_be(implode('', range('a', 'z')));
                },
                function () {
                    $array = range('a','z');
                    return expect(CharArr::from_iterable($array)->to_s(), 'from_iterable')->to_be(implode('', range('a', 'z')));
                }
            ],
            function () {
                $this->str1 = new CharArr('asdf');
                $this->str2 = CharArr::range('a', 'z');
            }
        )
    )
);

section('abstract sequence (partly) (for the rest see array access and conversion)',
    subsection('',
        new Test(
            'clear, slice',
            [
                function() {
                    return expect($this->str->slice(1,2), 'slice')->to_be('sd');
                },
                function() {
                    $this->str->clear();
                    return expect($this->str->size(), 'clear (size)')->to_be(0) &&
                    expect($this->str, 'clear (\'\')')->to_be('');
                }
            ],
            function () {
                $this->str = new CharArr('asdf');
            }
        )
    )
);
