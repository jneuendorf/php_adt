<?php

echo '<h1>CharrArr (aka Str) class</h1>';

section('creation',
    subsection('',
        new Test(
            'new, range, from_iterable',
            [
                function() {
                    return expect($this->str1 instanceof Arr, 'new CharArr instanceof Arr')->to_be(true) &&
                    expect($this->str1 instanceof CharArr, 'new CharArr instanceof CharArr')->to_be(true) &&
                    expect($this->str1 instanceof Str, 'new CharArr instanceof Str')->to_be(true) &&
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


section('array access',
    subsection('',
        new Test(
            '[], get (implicit slicing)',
            [
                function() {
                    $str = $this->str;
                    return expect($str[0], 'positive index')->to_be('a') &&
                    expect($str[3], 'positive index')->to_be('f') &&
                    expect(function() use ($str) {return $str[4];}, 'positive index')->to_throw();
                },
                function() {
                    $str = $this->str;
                    return expect($str->get(0), 'positive index')->to_be('a') &&
                    expect($str->get(3), 'positive index')->to_be('f') &&
                    expect(function() use ($str) {return $str->get(4);}, 'positive index')->to_throw();
                },
                function() {
                    $str = $this->str;
                    return expect($str[-1], 'negative index')->to_be('f') &&
                    expect($str[-3], 'negative index')->to_be('s') &&
                    expect(function() use ($str) {return $str[-5];}, 'negative index')->to_throw();
                },
                function() {
                    $str = $this->str;
                    return expect($str['1:'], 'string slicing')->to_be('sdf') &&
                    expect($str['1:3'], 'string slicing')->to_be('sd') &&
                    expect($str['-4:-1'], 'string slicing')->to_be('asd') &&
                    expect(function() use ($str) {return $str['-5:-2'];}, 'string slicing')->to_throw();
                },
                function() {
                    $str = $this->str;
                    return expect($str[[1,]], 'array slicing')->to_be('sdf') &&
                    expect($str[[1]], 'array slicing')->to_be('sdf') &&
                    expect($str[[1,null]], 'array slicing')->to_be('sdf') &&
                    expect($str[[1,3]], 'array slicing')->to_be('sd') &&
                    expect($str[[-4,-1]], 'array slicing')->to_be('asd') &&
                    expect(function() use ($str) {return $str[[-5,-2]];}, 'array slicing')->to_throw();
                },
            ],
            function () {
                $this->str = new CharArr('asdf');
            }
        )
    )
);


section('conversion',
    subsection('',
        new Test(
            'to_a, to_s, to_dict, to_set, to_str',
            [
                function() {
                    return expect(implode('', $this->str->to_a()), 'to_a')->to_be(implode('', $this->chars));
                },
                function() {
                    return expect($this->str->to_str(), 'to_str')->to_be($this->str) &&
                    expect($this->str->to_str() instanceof Str, 'to_str')->to_be(true);
                },
                function() {
                    return expect($this->str->to_s(), 'to_s')->to_be('asdf') &&
                    expect(is_string($this->str->to_s()), 'to_s')->to_be(true);
                },
                function() {
                    return expect($this->str->to_dict(), 'to_dict')->to_be(new Dict(null, $this->chars));
                },
                function() {
                    return expect($this->str->to_set(), 'to_set')->to_be(new Set(...$this->chars));
                },
            ],
            function () {
                $this->chars = ['a', 's', 'd', 'f'];
                $this->str = new CharArr('asdf');
            }
        )
    )
);


section('instance methods (java-like)',
    subsection('',
        new Test(
            'char_at',
            [
                function() {
                    return expect($this->str->char_at(1), 'char_at')->to_be($this->chars[1]) &&
                    expect($this->str->char_at(-1), 'char_at')->to_be($this->chars[count($this->chars) -1]);
                },
                function() {
                    return expect($this->str->concat('xyz'), 'concat')->to_be(implode('', $this->chars).'xyz') &&
                    expect($this->str->concat(new CharArr('xyz')), 'concat')->to_be(implode('', $this->chars).'xyz') &&
                    expect($this->str->concat(new CharArr('xyz'), '123'), 'concat')->to_be(implode('', $this->chars).'xyz123');
                },
                function() {
                    return expect($this->str->contains('xyz'), 'contains')->to_be(false) &&
                    expect($this->str->contains(new CharArr('xyz')), 'contains')->to_be(false) &&
                    expect($this->str->contains(new CharArr('as')), 'contains')->to_be(true) &&
                    expect($this->str->contains('as'), 'contains')->to_be(true);
                },
                function() {
                    $str = $this->str;
                    return expect($this->str->matches('/asdF/gi'), 'matches')->to_be(true) &&
                    expect($this->str->matches('/^asdf$/'), 'matches')->to_be(true) &&
                    expect($this->str->matches('/.*/'), 'matches')->to_be(true) &&
                    expect($this->str->matches('/ASDF/g'), 'matches')->to_be(false) &&
                    expect(function() use ($str) {return $str->matches('/////');}, 'matches')->to_throw() &&
                    expect(function() use ($str) {return $str->matches('a/b');}, 'matches')->to_throw();
                },
                function() {
                    return expect($this->str->substring(1), 'substring')->to_be($this->str->slice(1)) &&
                    expect($this->str->substring(1, 3), 'substring')->to_be($this->str->slice(1, 2));
                },
                function() {
                    $str = new CharArr("\t haha \n");
                    return expect($this->str->trim(), 'trim')->to_be($this->str) &&
                    expect($str->trim(), 'trim')->to_be('haha');
                },
            ],
            function () {
                $this->chars = ['a', 's', 'd', 'f'];
                $this->str = new CharArr('asdf');
            }
        )
    )
);
