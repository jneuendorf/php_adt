<?php

require_once __DIR__.'/../Set.php';
require_once __DIR__.'/Test.php';

echo '<h1>Set class</h1>';

section('set creation', subsection('', new Test(
    'new, from_iterable',
    function() {
        $set = new Set(1, 2, 3, 1, 'asdf');
        $set2 = Set::from_iterable([1, 10 ,true, false, 0, new Arr(0,1), new Arr(0,1)]);

        return expect($set->size(), 'new -> size')->to_be(4) &&
        expect($set->has(1), 'new -> check elements')->to_be(true) &&
        expect($set->has(2))->to_be(true) &&
        expect($set->has(3))->to_be(true) &&
        expect($set->has('asdf'))->to_be(true) &&
        expect($set->has(1337))->to_be(false)
        &&
        expect($set2->size(), 'from_iterable -> size')->to_be(6) &&
        expect($set2->has(1), 'from_iterable -> check elements')->to_be(true) &&
        expect($set2->has(10))->to_be(true) &&
        expect($set2->has(true))->to_be(true) &&
        expect($set2->has(false))->to_be(true) &&
        expect($set2->has(0))->to_be(true) &&
        expect($set2->has(new Arr(0,1)))->to_be(true) &&
        expect($set2->has(new Arr(0,1,2)))->to_be(false);
    }
)));


section('set access', subsection('', new Test(
    'via [], has',
    function() {
        $set = new Set(1, 2, 3, 1, 'asdf');
        return expect($set[1], 'array-like')->not_to_be(null) &&
        expect($set[2], 'array-like')->not_to_be(null) &&
        expect($set[3], 'array-like')->not_to_be(null) &&
        expect($set['asdf'], 'array-like')->not_to_be(null) &&
        expect($set['not an element'], 'array-like')->to_be(null) &&
        expect($set->has(1), 'has')->to_be(true) &&
        expect($set->has(2), 'has')->to_be(true) &&
        expect($set->has(3), 'has')->to_be(true) &&
        expect($set->has('asdf'), 'has')->to_be(true) &&
        expect($set->has('not an element'), 'has')->to_be(false);
    }
)));


section('set instance methods',
    subsection(
        '',
        new Test(
            'collection "interface"',
            [
                function() {
                    $copy = $this->set->copy();
                    $copy->add('new element');
                    $copy[] = 'new element2';
                    return expect($copy->size(), 'add')->to_be($this->set->size() + 2) &&
                    expect($copy->has('new element'), 'add')->to_be(true) &&
                    expect($copy->has('new element2'), 'add')->to_be(true);
                },
                function() {
                    $copy = $this->set->copy()->clear();
                    return expect($copy->size(), 'clear')->to_be(0);
                },
                function() {
                    return expect($this->set->copy()->equals($this->set), 'copy')->to_be(true);
                },
                function() {
                    // $this->run_only_this();
                    $equal_set = new Set('asdf', 1, 2, 1, 3, 3);
                    return expect($this->set->equals($equal_set), 'equals')->to_be(true) &&
                    expect($this->set->equals($equal_set->add(1234)), 'equals')->to_be(false);
                },
                function() {
                    return expect($this->set->has(1), 'has')->to_be(true) &&
                    expect($this->set->has(2), 'has')->to_be(true) &&
                    expect($this->set->has(3), 'has')->to_be(true) &&
                    expect($this->set->has('asdf'), 'has')->to_be(true) &&
                    expect($this->set->has(11), 'has')->to_be(false);
                },
                function() {
                    return expect($this->set->hash(), 'hash')->to_be($this->set->copy()->hash()) &&
                    expect($this->set->hash(), 'hash')->not_to_be($this->set->copy()->clear()->hash());
                },
                function() {
                    $copy = $this->set->copy();
                    $copy->remove(1);
                    $copy->remove(11);
                    return expect($copy->size(), 'remove')->to_be($this->set->size() - 1) &&
                    expect($copy->has(1), 'remove')->to_be(false);
                },
            ],
            function() {
                $this->set = new Set(1, 2, 3, 1, 'asdf');
            }
        ),
        new Test(
            'remaining methods',
            [
                function() {
                    return expect($this->set->difference($this->set2), 'difference')->to_be(new Set(1,3));
                },
                function() {
                    $set = $this->set->copy();
                    $set->difference_update($this->set2);
                    return expect($set, 'difference_update')->to_be($this->set->difference($this->set2));
                },
                function() {
                    $set = $this->set->copy();
                    $set
                        ->discard(true)
                        ->discard(1);
                    return expect($set->size(), 'discard')->to_be($this->set->size() - 1) &&
                    expect($set, 'discard')->to_be($this->set->copy()->remove(true)->remove(1));
                },
                function() {
                    return expect($this->set->intersection($this->set2), 'intersection')->to_be(new Set('asdf', 2));
                },
                function() {
                    $set = $this->set->copy();
                    $set->intersection_update($this->set2);
                    return expect($set, 'intersection_update')->to_be($this->set->intersection($this->set2));
                },
                function() {
                    return expect($this->set->isdisjoint($this->set2), 'isdisjoint')->to_be(false) &&
                    expect($this->set->isdisjoint(new Set()), 'isdisjoint')->to_be(true);
                },
                function() {
                    $intersection = $this->set->intersection($this->set2);
                    return expect($intersection->issubset($this->set), 'issubset')->to_be(true) &&
                    expect($intersection->issubset($this->set2), 'issubset')->to_be(true) &&
                    expect($intersection->issubset(new Set()), 'issubset')->to_be(false) &&
                    expect($intersection->issubset(new Set('haha', [1, 2])), 'issubset')->to_be(false);
                },
                function() {
                    $intersection = $this->set->intersection($this->set2);
                    return expect($this->set->issuperset($intersection), 'issuperset')->to_be(true) &&
                    expect($this->set2->issuperset($intersection), 'issuperset')->to_be(true) &&
                    expect($intersection->issuperset($this->set), 'issuperset')->to_be(false) &&
                    expect($this->set->issuperset(new Set()), 'issuperset')->to_be(true);
                },
                function() {
                    $set = new Set(1, 2, 3, 1, 'asdf');
                    $set2 = new Set(2, 'asdf', 42, false);
                    $union = $set->union($set2);
                    $intersection = $set->intersection($set2);
                    return expect($set->symmetric_difference($set2), 'symmetric_difference')->to_be($union->difference($intersection));
                },
                function() {
                    $set = new Set(1, 2, 3, 1, 'asdf');
                    $set2 = new Set(2, 'asdf', 42, false);
                    $union = $set->union($set2);
                    $intersection = $set->intersection($set2);
                    $set->symmetric_difference_update($set2);
                    return expect($set, 'symmetric_difference_update')->to_be($union->difference($intersection));
                },
                function() {
                    return expect($this->set->union($this->set2), 'union')->to_be(new Set(1,2,3,'asdf',[true,false],false));
                },
                function() {
                    $copy = $this->set->copy();
                    $copy->union_update($this->set2);
                    return expect($copy, 'union_update')->to_be($this->set->union($this->set2));
                },
                function() {
                    $copy = $this->set->copy();
                    $copy->update($this->set2);
                    return expect($copy, 'update')->to_be($this->set->union($this->set2));
                },
            ],
            function() {
                $this->set = new Set(1, 2, 3, 1, 'asdf');
                $this->set2 = new Set(2, 'asdf', [true, false], false);
            }
        )
    )
);

section('iteration', subsection('', new Test(
    'foreach',
    function() {
        $set = new Set(1, 2, 3, 1, 'asdf');
        $res = true;

        $iterated = new Set();
        foreach ($set as $key => $value) {
            $res = $res && expect($key)->to_be($value);
            $iterated->add($value);
        }
        expect($iterated)->to_be($set);
        return $res;
    }
)));


?>
