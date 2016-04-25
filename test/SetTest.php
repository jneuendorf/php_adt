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
                    return expect($copy->size(), 'add')->to_be($this->set->size() + 1) &&
                    expect($copy->has('new element'), 'add')->to_be(true);
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
                // function() {
                //     return expect(, 'difference')->to_be() &&
                //     expect(, 'difference')->to_be();
                // },
                // function() {
                //     return expect(, 'difference_update')->to_be() &&
                //     expect(, 'difference_update')->to_be();
                // },
                // function() {
                //     return expect(, 'discard')->to_be() &&
                //     expect(, 'discard')->to_be();
                // },
                // function() {
                //     return expect(, 'intersection')->to_be() &&
                //     expect(, 'intersection')->to_be();
                // },
                // function() {
                //     return expect(, 'intersection_update')->to_be() &&
                //     expect(, 'intersection_update')->to_be();
                // },
                // function() {
                //     return expect(, 'isdisjoint')->to_be() &&
                //     expect(, 'isdisjoint')->to_be();
                // },
                // function() {
                //     return expect(, 'issubset')->to_be() &&
                //     expect(, 'issubset')->to_be();
                // },
                // function() {
                //     return expect(, 'issuperset')->to_be() &&
                //     expect(, 'issuperset')->to_be();
                // },
                // function() {
                //     return expect(, 'symmetric_difference')->to_be() &&
                //     expect(, 'symmetric_difference')->to_be();
                // },
                // function() {
                //     return expect(, 'symmetric_difference_udpate')->to_be() &&
                //     expect(, 'symmetric_difference_udpate')->to_be();
                // },
                // function() {
                //     return expect(, 'union')->to_be() &&
                //     expect(, 'union')->to_be();
                // },
                // function() {
                //     return expect(, 'union_update')->to_be() &&
                //     expect(, 'union_update')->to_be();
                // },
                // function() {
                //     return expect(, 'update')->to_be() &&
                //     expect(, 'update')->to_be();
                // },
            ],
            function() {
                $this->set = new Set(1, 2, 3, 1, 'asdf');
            }
        )
    )
);

$set = new Set(1, 2, 3, 1, 'asdf');
$set->add('asdf2');
$set->remove(2);

foreach ($set as $idx => $elem) {
    var_dump($idx);
    echo ': ';
    var_dump($elem);
    echo '-------<br>';
}

$set2 = $set->copy();
var_dump($set->equals($set2));

// array access
echo '<hr>array access..........<br>';
echo '<br>';
echo 'set any offset. ';
$set[] = 'new element';
var_dump($set['new element']);
echo '<br>';
echo 'unset undefined offset: old size = '.count($set).'....';
unset($set['nope']);
echo 'new size'.count($set).' (no change)<br>';
echo 'unset defined offset: old size = '.count($set).'....';
unset($set['new element']);
var_dump($set['new element']);
echo 'new size'.count($set).' (1 less)<br>';

echo 'intersection:<br>';
$set->add(1337)->add(42);
$set2->add(false)->add(new Set());
echo $set, ' ^ ', $set2, ' = ', $set->intersection($set2);
echo 'union:<br>';
echo $set, ' u ', $set2, ' = ', $set->union($set2);

?>
