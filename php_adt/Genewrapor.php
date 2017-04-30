<?php

namespace php_adt;

use \StdClass as StdClass;
use \Exception as Exception;

use _php_adt\Clonable as Clonable;

/**
* The class is intended for wrapping generators but it can handle all types of iterables (implementing the Iterator interface).
* That way generator functionaliy can be chained from different code parts (just like functions).
* Also optionally this class makes it possible to reiterate over a generator.
*/
class Genewrapor extends Clonable implements \Iterator {
    /**
    * Keep track of iteration progress of native arrays. The array itself does not know that...
    * @internal
    * @var int
    */
    protected $array_pos;
    /**
    * Caches iterated items if $reiterable was true in the constructor.
    * @internal
    * @var Arr
    */
    protected $cache;
    /**
    * The actual iterable object.
    * @internal
    * @var Iterator|array
    */
    protected $iterable;
    /**
    * Indicates whether to remember the iterated items.
    * @internal
    * @var bool
    */
    protected $reiterable;
    /**
    * Used to prevent using the cache on initial call of rewind (which happens at the beginning of foreach).
    * @internal
    * @var bool
    */
    protected $started_iteration;
    /**
    * Used to add items to the cache only if not currently using the cache.
    * @internal
    * @var bool
    */
    protected $using_cache;

    /**
    * Constructor.
    * @param Iterator $iterable An iterable object or array (i.e. a generator) that will be wrapped into the instance.
    * @param bool $reiterable Indicates whether to remember the iterated items. If true the instance can be iterated more than once.
    * @return Genewrapor
    */
    public function __construct($iterable, $reiterable=false) {
        // init generator if not initialized
        if (is_callable($iterable)) {
            $iterable = $iterable();
        }
        if (!is_iterable($iterable)) {
            throw new Exception("Genewrapor::__construct: Expected \$iterable to be iterable. Got ".var_export($iterable, true), 1);
        }
        $this->iterable = $iterable;
        $this->reiterable = $reiterable;
        $this->cache = new Arr();
        $this->started_iteration = false;
        $this->using_cache = false;
        $this->array_pos = 0;
    }

    /**
    * Creates a copy of the instance.
    * @param bool $deep This parameter has no effect. It is present only for interface consistency (clonable).
    * @return Genewrapor
    */
    public function copy($deep=false) {
        // TODO: clone the generator somehow
        return new static($this->iterable, $this->reiterable);
    }

    /**
    * Returns the internal iterable object.
    * @return Iterator
    */
    public function iterable() {
        return $this->iterable;
    }

    /**
    * Indicates if the instance can be reiterated.
    * @return bool
    */
    public function is_reiterable() {
        return $this->reiterable || is_array($this->iterable) || !($this->iterable instanceof \Generator);
    }

    /**
    * Generates a list of key-value pairs from the iterable. If the iterable is a generator and the reiterable flag has not been set to true.
    * @param int $size
    * @return Arr
    */
    public function items($size=PHP_INT_MAX) {
        $res = new Arr();
        $i = 0;
        foreach ($this->iterable as $key => $value) {
            if ($i > $size) {
                break;
            }
            $res->push(new Arr($key, $value));
            $i++;
        }
        return $res;
    }

    /**
    * Synonym for 'items()'.
    * @param int $size
    * @return Arr
    */
    public function to_arr($size=PHP_INT_MAX) {
        return $this->items($size);
    }

    /**
    * Generates a list of keys from the iterable. If the iterable is a generator and the reiterable flag has not been set to true.
    * @param int $size
    * @return Arr
    */
    public function keys($size=PHP_INT_MAX) {
        $res = new Arr();
        $i = 0;
        foreach ($this->iterable as $key => $value) {
            if ($i > $size) {
                break;
            }
            $res->push($key);
            $i++;
        }
        return $res;
    }

    /**
    * Generates a list of values from the iterable. If the iterable is a generator and the reiterable flag has not been set to true.
    * @param int $size
    * @return Arr
    */
    public function values($size=PHP_INT_MAX) {
        $res = new Arr();
        $i = 0;
        foreach ($this->iterable as $key => $value) {
            if ($i > $size) {
                break;
            }
            $res->push($value);
            $i++;
        }
        return $res;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
    * Returns the current element.
    * @return mixed
    */
    public function current() {
        if (!is_array($this->iterable)) {
            return $this->iterable->current();
        }
        return current($this->iterable);
    }

    /**
    * Returns the key of the current element.
    * @return mixed
    */
    public function key() {
        if (!is_array($this->iterable)) {
            return $this->iterable->key();
        }
        return key($this->iterable);
    }

    /**
    * Moves the cursor to the next element (the one after the current element).
    */
    public function next() {
        $this->started_iteration = true;
        if (!is_array($this->iterable)) {
            if (!$this->using_cache) {
                $this->cache[] = $this->iterable->current();
            }
            $this->iterable->next();
        }
        else {
            next($this->iterable);
            $this->array_pos++;
        }
    }

    /**
    * Moves the cursor to the first element.
    */
    public function rewind() {
        if ($this->using_cache === false && $this->started_iteration === true && $this->reiterable === true) {
            $this->using_cache = true;
            // var_dump('using the cache');
            $this->iterable = $this->cache;
        }
        if (!is_array($this->iterable)) {
            $this->iterable->rewind();
        }
        else {
            reset($this->iterable);
            $this->array_pos = 0;
        }
    }

    /**
    * @internal
    */
    public function valid() {
        if (!is_array($this->iterable)) {
            return $this->iterable->valid();
        }
        return $this->array_pos < count($this->iterable);
    }

    /**
    * Filters the items of the iterable using the given callback.
    * @param callable $callback The callback determines what items to keep. If true is returned the item is kept. <code>boolean $callback($key, $value)</code>
    * @return Genewrapor
    */
    public function filter($callback) {
        $iterable = $this->iterable;
        return new static(function() use ($iterable, $callback) {
            foreach ($this->iterable as $key => $value) {
                if ($callback($key, $value) === true) {
                    yield $key => $value;
                }
            }
        }, $this->reiterable);
    }

    /**
    * Maps the items of the iterable using the given callback.
    * @param callable $callback The callback determines the new value of the item. If true is returned the item is kept. <code>mixed $callback($key, $value)</code>
    * @return Genewrapor
    */
    public function map($callback) {
        $iterable = $this->iterable;
        return new static(function() use ($iterable, $callback) {
            foreach ($this->iterable as $key => $value) {
                yield $callback($key, $value);
            }
        }, $this->reiterable);
    }

    /**
    * reduce($callback[, initial])
    * Apply function of two arguments cumulatively to the items of this
    * Genewrapor, from left to right, so as to reduce the Genewrapor to a
    * single value.
    * @return mixed
    */
    public function reduce($callback, ...$args) {
        if (count($args) == 1) {
          $last = $args[0];
        } else {
          $last = $this->current();
          $this->next();
        }
        while ($this->valid()) {
          $last = $callback($last, $this->current());
          $this->next();
        }
        return $last;
    }
}
