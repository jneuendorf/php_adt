<?php
  require_once __DIR__.'/Genewrapor.php';
  require_once __DIR__.'/Arr.php';

  function zip(...$iterables) {
    $generator = function() use ($iterables) {
      while (True) {
        $tup = new Arr();
        foreach ($iterables as $iterable) {
          if ($iterable->valid()) {
            $tup->append($iterable->current());
            $iterable->next();
          } else {
            break 2;
          }
        }
        yield $tup;
      }
    };
    return new Genewrapor($generator);
  }

  function step($start=0, $step=1) {
    $generator = function() use ($start, $step) {
      while (True) {
        yield $start;
        $start = $start + $step;
      }
    };
    return new Genewrapor($generator);
  }

  class Slice {
    // [start,] stop [, step])
    public function __construct(...$args) {
      if (count($args) == 1) {
        $this->start = 0;
        $this->stop = $args[0];
        $this->step = 1;
      } else {
        $this->start = $args[0];
        $this->stop = $args[1];
        if (count($args) >= 3) {
          $this->step = $args[2];
        } else {
          $this->step = 1;
        }
      }
    }
  }

  function slice(...$args) {
      return new Slice(...$args);
  }

  function iter($iterable) {
    if (is_object($iterable) && $iterable instanceof Genewrapor) {
      return $iterable;
    }
    return new Genewrapor($iterable);
  }

  function islice($iterable, ...$args) {
    $s = slice(...$args);
    $it = iter(range($s->start, $s->stop, $s->step));

    if ($it->valid() > 0) {
      $nexti = $it->current();
      $it->next();
    } else {
      return;
    }

    $max = count($it);
    $i = 0;
    foreach ($iterable as $element) {
      if ($i === $nexti) {
        yield $element;
        $nexti = $it->current();
        $it->next();
        if (!$it->valid()) {
          return;
        }
      }
      $i = $i + 1;
    }
  }


?>
