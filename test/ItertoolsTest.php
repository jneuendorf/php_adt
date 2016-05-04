<?php

require_once __DIR__.'/Test.php';
require_once __DIR__.'/../Genewrapor.php';
require_once __DIR__.'/../Arr.php';
require_once __DIR__.'/../itertools.php';

echo '<h1>playing with stuff class</h1>';

section('zip', subsection('', new Test(
    '',
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
          )
        )
        ->to_be(new Arr(new Arr(2,3), new Arr(4,5)));
    }
)));


?>
