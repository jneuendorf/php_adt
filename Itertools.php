<?php
  require_once __DIR__.'/Genewrapor.php';
  require_once __DIR__.'/Arr.php';

  /**
   * most of the code is heavily inspired by
   * https://docs.python.org/3/library/itertools.html
   *
   * Here `Iterable` refers to anything iterable, including native arrays and strings,
   * while `Iterator` refers to an instance of Genewrapor.
  */

  /**
   * Return an iterator for a given iterable.
  */
  function iter($iterable) {
    if (is_object($iterable) && $iterable instanceof Genewrapor) {
      return $iterable;
    }
    if (is_string($iterable)) {
      $iterable = str_split($iterable);
    }
    return new Genewrapor($iterable);
  }

  /**
   * raised when trying to iterate over an empty iterator
  */
  class StopIteration extends Exception {
      /**
      * the return value of the iterator that caused the exception
      * @var mixed
      */
      public $result;

      public function __construct($result=null, Exception $previous = null) {
          $this->result = $result;
          parent::__construct("StopIteration: $result", 0, $previous);
      }

      public function __toString() {
          return __CLASS__ . ": [{$this->code}]: {$this->result}\n";
      }
  }

  /**
   * get an iterator's current value v, advance the iterator, and return v,
   * raises StopIteration if the given iterator is empty
  */
  function inext($iterator) {
    if (!$iterator->valid()) {
      // throw new StopIteration($iterator->getReturn());
      // TODO: think hard about implementing Genewrapor->getReturn
      throw new StopIteration();
    }
    $current = $iterator->current();
    $iterator->next();
    return $current;
  }

  /**
   * Make an iterator that aggregates elements from each of the iterables.
   *
   * Returns an iterator of Arr, where the i-th Arr contains the i-th element
   * from each of the argument iterables. The iterator stops when
   * the shortest input iterable is exhausted.
  */
  function zip(...$iterables) {
    return iter(function() use ($iterables) {

      $iterators = array_map(
        function($iterable) {return iter($iterable);},
        $iterables
      );

      while (True) {
        $tup = new Arr();
        foreach ($iterators as $iterator) {
          if ($iterator->valid()) {
            $tup->append(inext($iterator));
          } else {
            return;
          }
        }
        yield $tup;
      }
    });
  }

  /**
   * Make an iterator that returns evenly spaced values starting with number
   * start.
  */
  function step($start=0, $step=1) {
    return iter(function() use ($start, $step) {
      while (True) {
        yield $start;
        $start = $start + $step;
      }
    });
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

  /**
   * yields zip(step(), $iterable) as key => value pairs
  */
  function enumerate($iterable) {
    return iter(function() use ($iterable) {
      foreach(zip(step(), $iterable) as $tup) {
        yield $tup[0] => $tup[1];
      }
    });
  }

  /**
   * islice(iterable, [start, ]stop[, step])
   *
   * Make an iterator that returns selected elements from the iterable. If
   * start is non-zero, then elements from the iterable are skipped until start
   * is reached. Afterward, elements are returned consecutively unless step is
   * set higher than one which results in items being skipped. If stop is not
   * set, then iteration continues until the iterator is exhausted, if at all;
   * otherwise, it stops at the specified position. Unlike regular slicing,
   * islice() does not support negative values for start, stop, or step.
  */
  function islice($iterable, ...$args) {
    return iter(function() use ($iterable, $args) {
      $s = new Slice(...$args);
      $indices = iter(range($s->start, $s->stop-1, $s->step));

      try {
        $next_index = inext($indices);
      } catch (StopIteration $e) {
        return;
      }

      foreach (enumerate($iterable) as $index => $element) {
        if ($index === $next_index) {
          yield $element;
          try {
            $next_index = inext($indices);
          } catch (StopIteration $e) {
            return;
          }
        }
      }
    });
  }

  /**
   * Make an iterator returning elements from the iterable and saving a copy of
   * each. When the iterable is exhausted, return elements from the saved copy.
   * Repeats indefinitely.
  */
  function cycle($iterable) {
    return iter(function() use ($iterable) {
      $saved = new Arr();
      foreach ($iterable as $element) {
        yield $element;
        $saved->append($element);
      }
      while (count($saved) > 0) {
        foreach ($saved as $element) {
          yield $element;
        }
      }
    });
  }

  // TODO: accumulate -> maybe a method on Genewrapor?

  /**
   * Make an iterator that returns elements from the first iterable until it is
   * exhausted, then proceeds to the next iterable, until all of the iterables
   * are exhausted. Used for treating consecutive sequences as a single
   * sequence.
  */
  function chain(...$iterables) {
    return iter(function() use ($iterables) {
      foreach ($iterables as $it) {
        foreach ($it as $element) {
          yield $element;
        }
      }
    });
  }

  // TODO: groupby is cool, but should maybe be a method of dict
  // TODO: i think I want some `join` function. implode doesn't work on Genewrapors

  /**
   * Return successive r length permutations of elements in the iterable.
   *
   * If r is not specified or is None, then r defaults to the length of the
   * iterable and all possible full-length permutations are generated.
   *
   * Permutations are emitted in lexicographic sort order. So, if the input
   * iterable is sorted, the permutation tuples will be produced in sorted
   * order.
   *
   * Elements are treated as unique based on their position, not on their
   * value. So if the input elements are unique, there will be no repeat values
   * in each permutation.
  */
  function permutations($iterable, $r=null) {
    // $pool = Arr::from_iterable($iterable);
    // $n = $pool->length;
    // $r = $r === null ? $n : $r;
    // if ($r > $n) {
    //   return;
    // }
    //
    // $indices = Arr::from_iterable(range(0, $n));
    // $cycles = Arr::from_iterable(range($n, $n-$r, -1));
    // yield (
    //   $indices[[null, $r]]->map(
    //     function($idx, $i) use ($pool) {return $pool[$i];})
    // );
    // while ($n) {
    //   $nobreak = true;
    //   foreach (range($r-1, 0, -1) as $i) {
    //     $cycles[$i] -= 1;
    //     if ($cycles[$i] == 0) {
    //       $indices[[$i]] = $indices[[$i+1]] + $indices[[$i, $i+1]];
    //       $cycles[$i] = $n - $i;
    //     } else {
    //       $j = $cycles[$i];
    //       list($indices[$i], $indices[-$j]) = [$indices[-$j], $indices[$i]];
    //       yield (
    //         $indices[[null, $r]]->map(
    //           function($idx, $i) use ($pool) {return $pool[$i];})
    //       );
    //       $nobreak = false;
    //       break;
    //     }
    //   }
    //   if ($nobreak) {
    //     return;
    //   }
    // }
  }

  /**
  * Cartesian product of input iterables.
  */
  // function product(...$iterables, $r=1) {
    // $pools = array_map(
    //   function($pools) {return Arr::from_iterable($pool)},
    //   $iterables
    // )
    // $pools = Arr::from_iterable(islice(cycle($pools), $repeat))
    // $result = [[]];
    //
    // foreach ($pools as $pool) {
    //   // $result = [x+[y] for x in result for y in pool]
    //   $r = [];
    //   foreach ($result as $x) {
    //     foreach ($pool as $y) {
    //
    //     }
    //   }
    //   $result = $r;
    // }
    // foreach ($results as $prod) {
    //   yield Arr::from_iterable($prod);
    // }
  // }


?>
