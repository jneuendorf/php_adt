<?php

require_once __DIR__.'/Test.php';
require_once __DIR__.'/../Genewrapor.php';
require_once __DIR__.'/../Arr.php';
require_once __DIR__.'/../Dict.php';
require_once __DIR__.'/../itertools/itertools.php';

echo '<h1>itertools</h1>';

section('zip', subsection('', new Test(
    '',
    [
      function() {
        $count = new Genewrapor(step(1));
        $gewr1 = new Genewrapor($count);
        $gewr2 = new Genewrapor($count);

        return expect(
            Arr::from_iterable(
              islice(
                zip($gewr1, $gewr2)
                ->map(
                  function($key, $tup) {
                    return $tup->map(function($key, $val) {return $val + 1;});
                  }
                ),
                2
              )
            ),
            "complex example"
          )
          ->to_be(new Arr(new Arr(2,3), new Arr(4,5)));
      },
      function() {
        return expect(
            Arr::from_iterable(zip(step(), iter(range(1,3)))), "basic example"
          )
          ->to_be(new Arr(new Arr(0, 1), new Arr(1, 2), new Arr(2, 3)));
      },
      function() {
        return expect(Arr::from_iterable(zip([1,2], [3,4])), "transpose")
          ->to_be(new Arr(new Arr(1, 3), new Arr(2, 4)));
      },
      function() {
        return expect(
            Arr::from_iterable(zip(...zip([1,2], [3,4]))), "zip(zip(...))"
          )
          ->to_be(new Arr(new Arr(1, 2), new Arr(3, 4)));
      }
    ]
)));

section('step', subsection('', new Test(
    '',
    [
      function() {
        return expect(
            Arr::from_iterable(zip(step(), range(0, 2))), "basic example"
          )
          ->to_be(new Arr(new Arr(0, 0), new Arr(1, 1), new Arr(2, 2)));
      },
      function() {
        return expect(
            Arr::from_iterable(zip(step(1, 2), range(0, 2))), "with different start and step"
          )
          ->to_be(new Arr(new Arr(1, 0), new Arr(3, 1), new Arr(5, 2)));
      },
    ]
)));

section('enumerate', subsection('', new Test(
    '',
    [
      function() {
        return expect(new Dict(null, enumerate([1,2,3])))
          ->to_be(new Dict(null, [1, 2, 3]));
      },
      function() {
        return expect(new Dict(null, enumerate([1,2,3])))
          ->to_be(new Dict(null, [1, 2, 3]));
      },
    ]
)));

section('islice', subsection('', new Test(
    '',
    [
      function() {
        return expect(Arr::from_iterable(islice(step(), 4)), "basic example")
          ->to_be(new Arr(0,1,2,3));
      },
      function() {
        return expect(Arr::from_iterable(islice(step(), 1, 5, 3)), "complex example")
          ->to_be(new Arr(1, 4));
      },
    ]
)));

section('cycle', subsection('', new Test(
    '',
    [
      function() {
        return expect(Arr::from_iterable(islice(cycle([1, 2]), 4)), "native array")
          ->to_be(new Arr(1,2,1,2));
      },
      function() {
        return expect(Arr::from_iterable(islice(cycle(iter([1, 2])), 4)), "Genewrapor")
          ->to_be(new Arr(1,2,1,2));
      },
      function() {
        return expect(Arr::from_iterable(
            islice(
              cycle(iter([1, 2]))
                ->map(function($idx, $e) {return $e-1;}),
              4
            )
            ->map(function($idx, $e) {return $e-1;})
          ), "can still map all the things")
          ->to_be(new Arr(-1,0,-1,0));
      },
    ]
)));

section('chain', subsection('', new Test(
    '',
    [
      function() {
        return expect(
            Arr::from_iterable(
              chain(...zip([1, 2], [3, 4]))
                ->map(function($idx, $e) {return $e+1;})
            ),
            "flatten zipped list"
          )
          ->to_be(new Arr(2,4,3,5));
      },
    ]
)));

section('something cool', subsection('', new Test(
    '',
    [
      function() {
        $input = '100100011001011101100110110011011110100000101011111011111110010110110011001000100001';

        function chunked($iterable, $chunksize) {
          // bam! magic!
          return zip(...islice(cycle([iter($iterable)]), $chunksize));
        }

        // generate chunks of 7 bits
        $text = chunked($input, 7)
          // join each chunk into a binary string
          ->map(function($idx, $chunk) {
            return iter($chunk)->reduce(
              function($acc, $item) {return $acc . $item;}
            );
          })
          ->map(function($idx, $bin_str) {return bindec($bin_str);})
          ->map(function($idx, $ord) {return chr($ord);})
          ->reduce(function($acc, $item) {return $acc . $item;});

        return expect($text, $input)
          ->to_be("Hello World!");
      },
    ]
)));

section('product', subsection('', new Test(
    '',
    [
      function() {
        return expect(
            Arr::from_iterable(product([1,2], [3]))
          )
          ->to_be(new Arr(new Arr(1,3), new Arr(2,3)));
        return true;
      },
      function() {
        return expect(
            Arr::from_iterable(
              product(...repeat([0,1], 3))->map(
                function($idx, $triple) {
                  return $triple->reduce(function($a, $b) {return $a.$b;});
                }
              )
            )
          )
          ->to_be(Arr::from_iterable(
            iter(range(0, 7))->map(function($ix, $item) {
              return str_pad(decbin($item), 3, "0", STR_PAD_LEFT);
            })
          ));
        return true;
      },
    ]
)));

section('permutations', subsection('', new Test(
    '',
    [
      function() {
        return expect(
            Arr::from_iterable(permutations([1,2,3]))
          )
          ->to_be(new Arr(new Arr(1,2,3), new Arr(1,3,2), new Arr(2,1,3), new Arr(2,3,1), new Arr(3,1,2), new Arr(3,2,1)));
        return true;
      },
    ]
)));
?>
