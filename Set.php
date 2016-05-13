<?php

require_once '_php_adt/AbstractCollection.php';

use \_php_adt\AbstractCollection as AbstractCollection;
use \ArrayAccess as ArrayAccess;
use \Iterator as Iterator;

/**
 * Set is collection class with no duplicate elements.
 */
class Set extends AbstractCollection implements ArrayAccess, Iterator {

    /**
     * The set elements' hashes are stored as keys for uniqueness. All values are true.
     * @internal
     * @var array $_dict
    */
    protected $_dict;

    /**
     * Constructor.
     * @param mixed... $elements
    */
    public function __construct(...$elements) {
        $this->_dict = new Dict();
        $this->clear();
        foreach ($elements as $idx => $element) {
            $this->add($element);
        }
    }

    /**
     * Creates a new set from the given iterable (keys are ignored).
     * @param Iterator $iterable
    */
    public static function from_iterable($iterable) {
        $res = new static();
        foreach ($iterable as $idx => $element) {
            $res->add($element);
        }
        return $res;
    }

    /**
     * Stringifies the Set instance.
     * @return string
     */
    public function __toString() {
        $res = [];
        foreach ($this as $idx => $element) {
            array_push($res, '  '.__toString($element));
        }
        return "{\n".implode(", \n", $res)."\n}";
    }

    /**
     * Exposes the interal array.
     * @return array
     */
    public function dict() {
        return $this->_dict;
    }

    public function get($index) {
        return $this->offsetGet($index);
    }

    protected function _get_at($index) {
        return $this->offsetGet($index);
    }

    public function slice($start=0, $length=null) {
        throw new \Exception(get_called_class()." does not support slicing", 1);
    }

    // PROTECTED

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    /**
     * @internal
    */
    public function offsetExists($offset) {
        return $this->has($offset);
    }

    /**
    * @internal
    */
    public function offsetGet($offset) {
        if ($this->has($offset)) {
            return $offset;
        }
        return null;
    }

    /**
    * @internal
    */
    public function offsetSet($offset, $value) {
        $this->add($value);
    }

    /**
    * @internal
    */
    public function offsetUnset($offset) {
        if ($this->has($offset)) {
            $this->remove($offset);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
    * Returns the current element.
    * @return mixed
    */
    public function current() {
        return $this->_dict->key();
    }

    /**
    * Returns the current element (since there are no keys).
    * @return mixed
    */
    public function key() {
        return $this->_dict->key();
    }

    /**
    * Moves the cursor to the next element (the one after the current element).
    */
    public function next() {
        $this->_dict->next();
    }

    /**
    * Moves the cursor to the first element.
    */
    public function rewind() {
        $this->_dict->rewind();
    }

    /**
    * @internal
    */
    public function valid() {
        return $this->_dict->valid();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COLLECTION

    /**
    * Adds one or more elements to the set. <span class="label label-info">Chainable</span>
    * @param mixed... $elements The elements to be added.
    * @return Set
    */
    public function add(...$elements) {
        foreach ($elements as $key => $element) {
            $this->_dict->put($element, true);
        }
        return $this;
    }

    /**
    * Removes all elements from the set. <span class="label label-info">Chainable</span>
    * @return Set
    */
    public function clear() {
        $this->_dict->clear();
        return $this;
    }

    /**
    * Creates a (potentially deep) copy of the set.
    * @param bool $deep Whether to copy recursively.
    * @return Set
    */
    public function copy($deep=false) {
        return static::from_iterable($this);
    }

    /**
    * Indicates whether the set is equal to another object.
    * @param mixed $set
    * @return bool
    */
    public function equals($set) {
        if ($set instanceof self) {
            return $this->_dict->equals($set->dict());
        }
        return false;
    }

    /**
    * Indicates whether the given element is contained in this set.
    * @param mixed $element
    * @return bool
    */
    public function has($element) {
        return $this->_dict->has($element);
    }

    /**
    * Calculates a hash value of the set.
    * @return int
    */
    public function hash() {
        $res = 0;
        foreach ($this as $idx => $value) {
            $res += __hash($value);
        }
        return $res;
    }

    /**
    * Returns a new Set instance with each element mapped to (potentially) another value.
    * @param callable $callback The parameter is called like this: <code>$callback($element)</code>
    * @return Set
    */
    public function map($callback) {
        $res = new Set();
        foreach ($this as $idx => $element) {
            $res->add($callback($element));
        }
        return $res;
    }

    /**
    * Remove an element from a set if it is a member. If the element is not a member, do nothing. <span class="label label-info">Chainable</span>
    * @param mixed $element
    * @return Set
    */
    public function remove($element) {
        $this->_dict->remove($element);
        return $this;
    }

    /**
    * Returns the current size of the set.
    * @return int
    */
    public function size() {
        return $this->_dict->size();
    }

    /**
     * Converts the set to a native array.
     * @return array
     */
    public function to_a() {
        $res = [];
        foreach ($this->_dict->keys() as $idx => $key) {
            $res[] = $key;
        }
        return $res;
    }

    /**
     * Converts the set to an instance of Arr.
     * @return Arr
     */
    public function to_arr() {
        return Arr::from_iterable($this->_dict->keys());
    }

    /**
     * Converts the set to an instance of Dict.
     * @return Dict
     */
    public function to_dict() {
        $keys = $this->_dict->keys();
        $res = new Dict();
        foreach ($keys as $idx => $key) {
            $res->put($key, $key);
        }
        return $res;
    }

    /**
     * Creates a copy of the set.
     * @return Set
     */
    public function to_set() {
        return $this->copy();
    }

    /**
     * Creates a copy of the set.
     * @return Set
     */
    public function to_str() {
        return $this->to_arr()->to_str();
    }

    // PYTHON API

    /**
     * Returns the difference of two sets as a new set (all elements that are in this set but not the other).
     * @param Set $set
     * @return Set
     */
    public function difference($set) {
        $res = new Set();
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                $res->add($element);
            }
        }
        return $res;
    }

    /**
     * Removes all elements of another set from this set. <span class="label label-info">Chainable</span>
     * @param Set $set
     * @return Set
     */
    public function difference_update($set) {
        foreach ($set as $idx => $element) {
            $this->remove($element);
        }
        return $this;
    }

    /**
     * Synonym for 'remove()'.
     * @param mixed $element
     * @return Set
     */
    public function discard($element) {
        return $this->remove($element);
    }

    /**
     * Returns the intersection of two sets as a new set (all elements that are in both sets).
     * @param Set $set
     * @return Set
     */
    public function intersection($set) {
        $res = new Set();
        foreach ($set as $idx => $element) {
            if ($this->has($element)) {
                $res->add($element);
            }
        }
        return $res;
    }

    /**
     * Updates a set with the intersection of itself and another. <span class="label label-info">Chainable</span>
     * @param Set $set
     * @return Set
     */
    public function intersection_update($set) {
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                $this->remove($element);
            }
        }
        return $this;
    }

    /**
     * Returns true if the intersection of two sets is empty.
     * @param Set $set
     * @return bool
     */
    public function isdisjoint($set) {
        return $this->intersection($set)->is_empty();
    }

    /**
     * Reports whether another set contains this set.
     * @param Set $set
     * @return bool
     */
    public function issubset($set) {
        if ($set->size() < $this->size()) {
            return false;
        }
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Reports whether this set contains another set.
     * @param Set $set
     * @return bool
     */
    public function issuperset($set) {
        return $set->issubset($this);
    }

    /**
     * Returns the symmetric difference of two sets as a new set (all elements that are in exactly one of the sets, union without intersection).
     * @param Set $set
     * @return Set
     */
    public function symmetric_difference($set) {
        $res = new Set();
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                $res->add($element);
            }
        }
        foreach ($set as $idx => $element) {
            if (!$this->has($element)) {
                $res->add($element);
            }
        }
        return $res;
    }

    /**
     * Updates a set with the symmetric difference of itself and another. <span class="label label-info">Chainable</span>
     * @param Set $set
     * @return Set
     */
    public function symmetric_difference_update($set) {
        $sym_diff = $this->symmetric_difference($set);
        $this->clear();
        foreach ($sym_diff as $idx => $element) {
            $this->add($element);
        }
        return $this;
    }

    /**
     * Return the union of sets as a new set (all elements that are in either set).
     * @param Set $set
     * @return Set
     */
    public function union($set) {
        $res = $this->copy();
        foreach ($set as $idx => $element) {
            $res->add($element);
        }
        return $res;
    }

    // API-CHANGE: added for naming consistency
    /**
     * Synonym for 'update()'. <span class="label label-info">Chainable</span>
     * @param Set $set
     * @return Set
     */
    public function union_update($set) {
        return $this->update($set);
    }

    // union in place
    /**
     * Update a set with the union of itself and others. <span class="label label-info">Chainable</span>
     * @param Set $set
     * @return Set
     */
    public function update($set) {
        foreach ($set as $idx => $element) {
            $this->add($element);
        }
        return $this;
    }
}
