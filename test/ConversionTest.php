<?php

// require_once __DIR__.'/../Arr.php';
// require_once __DIR__.'/Test.php';


echo '<h1>Conversions between classes</h1>';

section('Arr -> X',
    subsection('',
        new Test(
            '',
            [
                function() {
                    return expect($this->arr->to_set(), 'Arr -> Set')->to_be(new Set(...$this->values));
                },
                function() {
                    return expect($this->arr->to_dict(), 'Arr -> Dict')->to_be(new Dict(null, $this->values));
                },
                function() {
                    return expect($this->arr->to_arr(), 'Arr -> Arr')->to_be($this->arr);
                },
                function() {
                    return expect($this->arr->to_a(), 'Arr -> array')->to_be($this->values);
                },
            ],
            function () {
                $this->values = [1, 2, 3, 'asdf',false, 0, 2, 7, true];
                $this->arr = new Arr(...$this->values);
            }
        )
    )
);


section('Set -> X',
    subsection('',
        new Test(
            '',
            [
                function() {
                    return expect($this->set->to_set(), 'Set -> Set')->to_be($this->set);
                },
                function() {
                    return expect($this->set->to_arr()->sort(), 'Set -> Arr')->to_be((new Arr(...$this->values))->unique()->sort());
                },
                function() {
                    $expected = new Dict();
                    foreach ($this->set as $element) {
                        $expected->put($element, $element);
                    }
                    return expect($this->set->to_dict(), 'Set -> Dict')->to_be($expected);
                },
                function() {
                    $a = $this->set->to_a();
                    $res = true;
                    foreach ($a as $idx => $elem) {
                        if ($idx === 0) {
                            $res = $res && expect(in_array($elem, $this->values), 'Set -> array (order ignored)')->to_be(true);
                        }
                        else {
                            $res = $res && expect(in_array($elem, $this->values))->to_be(true);
                        }
                    }

                    return $res;
                },
            ],
            function () {
                $this->values = [1, 2, 3, 'asdf',false, 0, 2, 7, true];
                $this->set = new Set(...$this->values);
            }
        )
    )
);

section('Dict -> X',
    subsection('',
        new Test(
            '',
            [
                function() {
                    // list of tuples
                    $tuples = new Arr();
                    foreach ($this->dict as $key => $value) {
                        $tuples->push(new Arr($key, $value));
                    }
                    return expect($this->dict->to_set(), 'Dict -> Set')->to_be(Set::from_iterable($tuples));
                },
                function() {
                    return expect($this->dict->to_dict(), 'Dict -> Dict')->to_be($this->dict);
                },
                function() {
                    $tuples = new Arr();
                    foreach ($this->dict as $key => $value) {
                        $tuples->push(new Arr($key, $value));
                    }
                    return expect($this->dict->to_arr(), 'Dict -> Arr')->to_be($tuples);
                },
                function() {
                    $tuples = [];
                    foreach ($this->dict as $key => $value) {
                        $tuples[] = [$key, $value];
                    }
                    return expect($this->dict->to_a(), 'Dict -> array')->to_be($tuples);
                },
            ],
            function () {
                $this->values = [1, 2, 3, 'asdf',false, 0, 2, 7, true];
                $this->dict = new Dict(null, $this->values);
            }
        )
    )
);



?>
