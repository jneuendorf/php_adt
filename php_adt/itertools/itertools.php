<?php

namespace php_adt\itertools;

use php_adt\itertools\StopIteration as StopIteration;
use php_adt\itertools\Slice as Slice;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Genewrapor.php']);
use php_adt\Genewrapor as Genewrapor;
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Arr.php']);
use php_adt\Arr as Arr;
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Set.php']);
use php_adt\Set as Set;

/**
 * most of the code is heavily inspired by
 * https://docs.python.org/3/library/itertools.html.
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
 * get an iterator's current value v, advance the iterator, and return v,
 * raises StopIteration if the given iterator is empty.
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
    return iter(function () use ($iterables) {

        $iterators = array_map(
            function ($iterable) {return iter($iterable);},
            $iterables
        );

        while (true) {
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
function step($start = 0, $step = 1) {
    return iter(function () use ($start, $step) {
        while (true) {
            yield $start;
            $start = $start + $step;
        }
    });
}

/**
 * yields zip(step(), $iterable) as key => value pairs.
 */
function enumerate($iterable) {
    return iter(function () use ($iterable) {
        foreach (zip(step(), $iterable) as $tup) {
            yield $tup[0] => $tup[1];
        }
    });
}

/**
 * islice(iterable, [start, ]stop[, step]).
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
    return iter(function () use ($iterable, $args) {
        $s = new Slice(...$args);
        $indices = iter(range($s->start, $s->stop - 1, $s->step));

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
    return iter(function () use ($iterable) {
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

/**
 * Make an iterator that returns elements from the first iterable until it is
 * exhausted, then proceeds to the next iterable, until all of the iterables
 * are exhausted. Used for treating consecutive sequences as a single
 * sequence.
 */
function chain(...$iterables) {
    return iter(function () use ($iterables) {
        foreach ($iterables as $it) {
            foreach ($it as $element) {
                yield $element;
            }
        }
    });
}

/**
 * Return successive r length permutations of elements in the iterable.
 *
 * If r is not specified or is null, then r defaults to the length of the
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
function permutations($iterable, $r = null) {
    // TODO: this is not very efficient
    return iter(function () use ($iterable, $r) {
        $pool = Arr::from_iterable($iterable);
        $n = $pool->length;
        $r = $r === null ? $n : $r;

        foreach (product(...repeat(range(0, $n - 1), $r)) as $indices) {
            if (Set::from_iterable($indices)->size() === $r) {
                $perm = new Arr();
                foreach ($indices as $i) {
                    $perm->append($pool[$i]);
                }
                yield $perm;
            }
        }
    });
}

/**
 * Cartesian product of input iterables.
 */
function product(...$iterables) {
    return iter(function () use ($iterables) {
        $pools = array_map(
            function ($pool) {return Arr::from_iterable($pool);},
            $iterables
        );
        $result = [[]];
        foreach ($pools as $pool) {
            // $result = [x+[y] for x in result for y in pool]
            $r = [];
            foreach ($result as $x) {
                foreach ($pool as $y) {
                    $r[] = Arr::from_iterable($x)->concat([$y]);
                }
            }
            $result = $r;
        }
        foreach ($result as $prod) {
            yield Arr::from_iterable($prod);
        }
    });
}

/**
 * create an Iterator that yields a given $thing $times times.
 */
function repeat($thing, $times) {
    return islice(cycle([$thing]), $times);
}
