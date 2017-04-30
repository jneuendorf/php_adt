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
                function() {
                    return expect((new CharArr())->capitalize(), 'capitalize')->to_be('') &&
                    expect($this->str->capitalize(), 'capitalize')->to_be('Asdf');
                },
                function() {
                    return expect($this->str->center(10), 'center')->to_be('   asdf   ') &&
                    expect($this->str->center(10, '*'), 'center')->to_be('***asdf***') &&
                    expect($this->str->center(3, '*'), 'center')->to_be('asdf');
                },
                function() {
                    return expect($this->str->count_substr('asdf'), 'count_substr')->to_be(1) &&
                    expect($this->str->count_substr('a'), 'count_substr')->to_be(1) &&
                    expect($this->str->count_substr('a', 3), 'count_substr')->to_be(0) &&
                    expect($this->str->count_substr('f', 0, 2), 'count_substr')->to_be(0) &&
                    expect($this->str->count_substr('z'), 'count_substr')->to_be(0);
                },
                function() {
                    $str = new CharArr('abcdef');
                    return expect($str->endswith("ab"), 'endswith')->to_be(false) &&
                    expect($str->endswith("cd"), 'endswith')->to_be(false) &&
                    expect($str->endswith("ef"), 'endswith')->to_be(true) &&
                    expect($str->endswith(""), 'endswith')->to_be(true) &&
                    expect((new CharArr())->endswith("abcdef"), 'endswith')->to_be(false);
                },
                function() {
                    $str = new CharArr("col1\tcol2\tcol3");
                    return expect($str->expandtabs(), 'expandtabs')->to_be("col1    col2    col3") &&
                    expect($str->expandtabs(2), 'expandtabs')->to_be("col1  col2  col3");
                },
                function() {
                    return expect($this->str->find('sd'), 'find')->to_be(1) &&
                    expect($this->str->find('a'), 'find')->to_be(0) &&
                    expect($this->str->find('a', 1), 'find')->to_be(-1) &&
                    expect($this->str->find('f'), 'find')->to_be(3) &&
                    expect($this->str->find('f', 0, 2), 'find')->to_be(-1);
                },
                function() {
                    $str = new CharArr('{1} {A} {} {1337} {b} {my_key}');
                    $kwargs = ['A' => 2, 'b' => 'zz', 1337 => -1];
                    $dict = new Dict(null, $kwargs);
                    class Stringifyable {
                        public function __toString() {
                            return 'my_key';
                        }
                    }
                    $dict->put(
                        new Stringifyable(),
                        'awesome'
                    );
                    $args1 = ['val0', 'val1', $kwargs];
                    $args2 = ['val0', 'val1', $dict];
                    return expect($str->format(...$args1), 'format')->to_be('val1 2 val0 -1 zz {my_key}') &&
                    expect($str->format(...$args2), 'format')->to_be('val1 2 val0 -1 zz awesome');
                },
                function() {
                    $str = new CharArr('i am an untitlecased string :)');
                    return expect($str->istitle(), 'istitle')->to_be(false) &&
                    expect($str->title()->istitle(), 'istitle')->to_be(true);
                },
                function() {
                    return expect($this->str->isupper(), 'isupper')->to_be(false) &&
                    expect($this->str->upper()->isupper(), 'isupper')->to_be(true);
                },
                function() {
                    $arr = new Arr(1, 2, 3, 4);
                    return expect($this->str->join($arr), 'join')->to_be(implode($this->str->to_s(), $arr->to_a()));
                },
                function() {
                    $str = new CharArr('UPPER');
                    return expect($this->str->lower(), 'lower')->to_be($this->str) &&
                    expect($str->lower(), 'lower')->to_be(strtolower($str->to_s()));
                },
                function() {
                    $str = new CharArr('hello, world');
                    $partitioned1 = $str->partition(',');
                    $partitioned2 = $str->partition('%');
                    return expect($partitioned1->first(), 'partition')->to_be('hello') &&
                    expect($partitioned1->second(), 'partition')->to_be(',') &&
                    expect($partitioned1->third(), 'partition')->to_be(' world') &&
                    expect($partitioned2->first(), 'partition')->to_be('hello, world') &&
                    expect($partitioned2->second(), 'partition')->to_be('') &&
                    expect($partitioned2->third(), 'partition')->to_be('');
                },
                function() {
                    $str = new CharArr('word1 word2 word3 word4 word5');
                    return expect($this->str->replace('as', '__'), 'replace')->to_be('__df') &&
                    expect($this->str->replace('_X_', '<>'), 'replace')->to_be($this->str) &&
                    expect($str->replace('word', '+', 3), 'replace')->to_be('+1 +2 +3 word4 word5');
                },
                function() {
                    $str = new CharArr('word1 word2 word3 word4 word5');
                    return expect($this->str->split(''), 'split')->to_be(new Arr('', 'a', 's', 'd', 'f', '')) &&
                    expect($str->split(' ', 3), 'split')->to_be(new Arr('word1', 'word2', 'word3 word4 word5'));
                },
                function() {
                    $str = new CharArr("word1\nword2\rword3\r\nword4 word5");
                    return expect($str->splitlines(), 'splitlines')->to_be(new Arr('word1', 'word2', 'word3', 'word4 word5')) &&
                    expect($str->splitlines(true), 'splitlines')->to_be(new Arr("word1\n", "word2\r", "word3\r\n", 'word4 word5'));
                },
                function() {
                    $str = new CharArr('abcdef');
                    return expect($str->startswith("ab"), 'startswith')->to_be(true) &&
                    expect($str->startswith("cd"), 'startswith')->to_be(false) &&
                    expect($str->startswith("ef"), 'startswith')->to_be(false) &&
                    expect($str->startswith(""), 'startswith')->to_be(true) &&
                    expect((new CharArr())->startswith("abcdef"), 'startswith')->to_be(false);
                },
                function() {
                    $str1 = new CharArr("  \ttext word1\t");
                    $str2 = new CharArr("0000000this is string example...00...wow!!!0000000");
                    return expect($str1->strip(), 'strip')->to_be('text word1') &&
                    expect($str2->strip('0'), 'strip')->to_be('this is string example...00...wow!!!');
                },
                function() {
                    $str = new CharArr('word1 word2 word3 word4 word5');
                    return expect($str->title(), 'title')->to_be('Word1 Word2 Word3 Word4 Word5');
                },
                function() {
                    return expect($this->str->upper(), 'upper')->to_be(strtoupper($this->str->to_s()));
                },
            ],
            function () {
                $this->chars = ['a', 's', 'd', 'f'];
                $this->str = new CharArr('asdf');
            }
        )
    )
);
