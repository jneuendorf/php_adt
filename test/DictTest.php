<?php

namespace php_adt;

use \StdClass as StdClass; use \Exception as Exception;
// require_once __DIR__.'/../Dict.php';
// require_once __DIR__.'/Test.php';


echo '<h1>Dict class</h1>';


section('iteration', subsection('', new Test(
    'foreach',
    function() {
        $dict = new Dict(null, [1, 2, 3, 1, 'asdf']);
        $res = expect($dict->size(), 'size')->to_be(5);

        $iterated = new Dict();
        foreach ($dict as $key => $value) {
            $res = $res && expect($dict[$key])->to_be($value);
            $iterated->put($key, $value);
        }
        expect($iterated, 'iterated == created?')->to_be($dict);
        return $res;
    }
)));


section('dict access', subsection('', new Test(
    'via [], get',
    function() {
        $dict = new Dict(null, [
            'a' => 1, 'c' => 2, 4 => 3, 1, 'asdf'
        ]);
        $dict->put(true, false);
        return expect($dict['a'], 'array-like')->to_be(1) &&
        expect($dict['c'], 'array-like')->to_be(2) &&
        expect($dict[4], 'array-like')->to_be(3) &&
        expect($dict[5], 'array-like')->to_be(1) &&
        expect($dict[6], 'array-like')->to_be('asdf') &&
        expect($dict[true], 'array-like')->to_be(false) &&
        expect($dict['not an element'], 'array-like')->to_be(null) &&
        expect($dict->get('a'), 'get')->to_be($dict['a']) &&
        expect($dict->get('c'), 'get')->to_be($dict['c']) &&
        expect($dict->get(4), 'get')->to_be($dict[4]) &&
        expect($dict->get(5), 'get')->to_be($dict[5]) &&
        expect($dict->get(6), 'get')->to_be($dict[6]) &&
        expect($dict->get(true), 'get')->to_be($dict[true]) &&
        expect($dict->get('not an element'), 'get')->to_be($dict['not an element']);
    }
)));




section('dict instance methods',
    subsection(
        '',
        new Test(
            'collection "interface"',
            [
                function() {
                    $dict = $this->dict->copy();
                    $dict->clear();
                    return expect($dict->size(), 'clear')->to_be(0);
                },
                function() {
                    $dict = $this->dict->copy();
                    return expect($dict, 'copy')->to_be($this->dict);
                },
                function() {
                    $dict = new Dict();
                    $dict
                        ->put('a', 1)
                        ->put('c', 2)
                        ->put(4, 3)
                        ->put(5.1, 3.1)
                        ->put(['string', false], 1)
                        ->put(true, false);
                    return expect($this->dict, 'equals')->to_be($dict);
                },
                function() {
                    return expect(true, 'get() was tested above')->to_be(true);
                },
                function() {
                    return expect($this->dict->hash(), 'hash')->to_be($this->dict->copy()->hash()) &&
                    expect($this->dict->hash(), 'hash')->not_to_be($this->dict->copy()->clear()->hash());
                },
            ],
            function() {
                $dict = new Dict();
                $dict
                    ->put('a', 1)
                    ->put('c', 2)
                    ->put(4, 3)
                    ->put(5.1, 3.1)
                    ->put(['string', false], 1)
                    ->put(true, false);
                $this->dict = $dict;
            }
        ),
        new Test(
            'map "interface" (partly overriding collection)',
            [
                function() {
                    $dict = $this->dict->copy();
                    $dict->add('mykey');
                    return expect($dict->size(), 'add')->to_be($this->dict->size() + 1) &&
                    expect($dict['mykey'], 'add')->to_be($dict->default_val);
                },
                function() {
                    return expect(true, 'get() was tested above')->to_be(true);
                },
                function() {
                    $res = true;
                    foreach ($this->dict->keys() as $idx => $key) {
                        $res = $res && expect($this->dict->has_key($key), 'has_key')->to_be(true) && expect($this->dict->has($key), 'has')->to_be(true);
                    }
                    $res = $res && expect($this->dict->has_key('not actually a key'), 'has_value')->to_be(false);
                    foreach ($this->dict as $key => $val) {
                        $res = $res && expect($this->dict->has_value($val), 'has_value')->to_be(true);
                    }
                    $res = $res && expect($this->dict->has_value([1,2,3]), 'has_value')->to_be(false);
                    return $res;
                },
                function() {
                    $expected = new Arr();
                    foreach ($this->dict as $key => $value) {
                        $expected->push(new Arr($key, $value));
                    }
                    return expect($this->dict->items(), 'items')->to_be($expected);
                },
                function() {
                    return expect($this->dict->keys(), 'keys')->to_be(new Set('a', 'c', 4, 5.1, ['string', false], true));
                },
                function() {
                    $dict = $this->dict->copy();
                    $popped1 = $dict->pop('a');
                    $popped2 = $dict->pop('somethin weird', 'my default');
                    return expect($popped1, 'pop')->to_be($this->dict['a']) &&
                    expect($dict->size(), 'pop (size)')->to_be($this->dict->size() - 1) &&
                    expect($popped2, 'pop')->to_be('my default');
                },
                function() {
                    $dict = $this->dict->copy();
                    $popped = $dict->popitem();
                    $res = expect($popped, 'popitem')->not_to_be(null) &&
                    expect($dict->size(), 'popitem (size)')->to_be($this->dict->size() - 1);
                    $dict->clear();
                    return $res && expect(function() use ($dict) {$dict->popitem();}, 'popitem')->to_throw();
                },
                function() {
                    $dict = $this->dict->copy();
                    $o = new StdClass();
                    $dict->put(['asdf', 42], $o);
                    return expect($dict->size(), 'put')->to_be($this->dict->size() + 1) &&
                    expect($dict->get(['asdf', 42]), 'put')->to_be($o);
                },
                function() {
                    $dict = $this->dict->copy();
                    $dict->remove('a');
                    return expect($dict['a'], 'remove')->to_be(null) &&
                    expect($dict->size(), 'remove')->to_be($this->dict->size() - 1);
                },
                function() {
                    $dict = $this->dict->copy();
                    $dict->setdefault(1337);
                    return expect($dict['no key'], 'setdefault')->to_be(1337);
                },
                function() {
                    $dict = $this->dict->copy();
                    $data = new Dict(null, ['newkey' => 'newval', 'c' => 12, 4 => 'a']);
                    $dict->update($data);
                    return expect($dict['newkey'], 'update (new pair)')->to_be('newval') &&
                    expect($dict['c'], 'update (updated pair)')->to_be(12) &&
                    expect($dict[4], 'update (updated pair)')->to_be('a');
                },
                function() {
                    return expect($this->dict->values(), 'values')->to_be(new Set(1, 2, 3, 3.1, false));
                },
            ],
            function() {
                $dict = new Dict();
                $dict
                    ->put('a', 1)
                    ->put('c', 2)
                    ->put(4, 3)
                    ->put(5.1, 3.1)
                    ->put(['string', false], 1)
                    ->put(true, false);
                $this->dict = $dict;
            }
        )
    )
);

?>
