<?php

// require_once __DIR__.'/../Genewrapor.php';
// require_once __DIR__.'/Test.php';

echo '<h1>Genewrapor class</h1>';

section('creation', subsection('', new Test(
    'new',
    function() {
        $count = function($i=1) {
            while ($i < 20) {
                yield $i++;
            }
            yield $i;
        };
        $count = $count();
        $gewr1 = new Genewrapor($count);
        $gewr2 = new Genewrapor($count, true);
        $gewr3 = new Genewrapor([1, 2, 3], true);

        return expect($gewr1->iterable() === $count, 'new')->to_be(true) &&
        expect($gewr1->is_reiterable(), 'new (reiterable?)')->to_be(false) &&
        expect($gewr2->is_reiterable(), 'new (reiterable?)')->to_be(true) &&
        expect($gewr3->is_reiterable(), 'new (reiterable?)')->to_be(true) &&
        expect(function() {return new Genewrapor('not iterable');}, 'new invalid')->to_throw();
    }
)));


section('Genewrapor instance methods',
    subsection(
        '',
        new Test(
            '',
            [
                function() {
                    $copy = $this->genwr->copy();
                    return expect($copy->iterable() === $this->genwr->iterable(), 'copy')->to_be(true) &&
                    expect($copy->is_reiterable(), 'copy')->to_be($this->genwr->is_reiterable());
                },
                function() {
                    $filtered = $this->genwr->filter(function($key, $val) {
                        return $val % 2 === 0;
                    });
                    return expect($filtered->values(), 'filter')
                        ->to_be(new Arr(2, 4, 6, 8, 10));
                },
                function() {
                    $mapped = $this->genwr->map(function($key, $val) {
                        return $val * $val;
                    });
                    return expect($mapped->values(), 'map')
                        ->to_be(new Arr(1, 4, 9, 16, 25, 36, 49, 64, 81, 100));
                },
            ],
            function() {
                $this->count = function($i=1) {
                    while ($i < 10) {
                        yield $i++;
                    }
                    yield $i;
                };
                $this->genwr = new Genewrapor($this->count);
            }
        )
    )
);


section('iteration', subsection('', new Test(
    'foreach',
    function() {
        $prev_val = 0;
        $res = true;
        foreach ($this->genwr as $key => $value) {
            $res = $res && expect($value, '1st iteration')->to_be($prev_val + 1);
            $prev_val = $value;
        }
        $prev_val = 0;
        foreach ($this->genwr as $key => $value) {
            $res = $res && expect($value, '2nd iteration')->to_be($prev_val + 1);
            $prev_val = $value;
        }
        return $res;
    },
    function() {
        $this->count = function($i=1) {
            while ($i < 10) {
                yield $i++;
            }
            yield $i;
        };
        $this->genwr = new Genewrapor($this->count, true);
    }
)));


?>
