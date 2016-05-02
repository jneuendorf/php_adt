<?php

require_once 'Clonable.php';

class Lazy extends Clonable implements Iterator {

    protected $iterable;
    // protected $current_val;

    public function __construct($iterable) {
        $this->iterable = $iterable;
        // $this->current_val = null;
    }

    public function copy($deep=false) {
        return new static($this->iterable);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
    * Returns the current element.
    * @return mixed
    */
    public function current() {
        return $this->iterable->current();
    }

    /**
    * Returns the index of the current element.
    * @return int
    */
    public function key() {
        return $this->iterable->key();
    }

    /**
    * Moves the cursor to the next element (the one after the current element).
    */
    public function next() {
        return $this->iterable->next();
    }

    /**
    * Moves the cursor to the first element.
    */
    public function rewind() {
        // $this->iterable->rewind();
        // TODO: magic
    }

    /**
    * @internal
    */
    public function valid() {
        return $this->iterable->valid();
    }



    public function filter() {
        $iterable = $this->iterable;
        return new static(function() use ($iterable, $callback) {
            foreach ($this->iterable as $key => $value) {
                if ($callback($key, $value) === true) {
                    yield $key => $value;
                }
            }
        });
    }

    public function map($callback) {
        $iterable = $this->iterable;
        return new static(function() use ($iterable, $callback) {
            foreach ($this->iterable as $key => $value) {
                yield $callback($key, $value);
            }
        });
    }
}

$count = function($i=1) {
    while ($i < 20) {
        yield $i++;
    }
    yield $i;
};


// var_dump((new ReflectionFunction($count))->isGenerator());

echo '<pre>';
$o = new Lazy($count());
// var_dump($o->current());
// var_dump($o->map(function($k, $v) {return $v*$v;}));
// for ($i=0; $i < 20; $i++) {
//     // var_dump(current($o));
//     var_dump($o->current());
//     $o->next();
// }
foreach ($o as $key => $value) {
    var_dump($key);
    var_dump($value);
}


?>
