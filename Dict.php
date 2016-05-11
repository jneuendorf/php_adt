<?php

require_once 'init.php';
require_once 'AbstractMap.php';
require_once 'Arr.php';
require_once 'Set.php';

/**
 * The Dict class implements a dictionary. The keys should implement a 'hash()' method.
 * If the key is not hashable an id generated by 'uniqid()' will be used instead.
 * The keys may be mutable but must manually be updated when modified (remove, modify, add). Otherwise the Dict will break.
 */
class Dict extends _php_adt\AbstractMap implements ArrayAccess, Iterator {
    /**
    * The default value is returned on access if there is no value for a key.
    * @var mixed
    */
    public $default_val;
    /**
    * A callable that determines when two keys are considered equal.
    * @var callable
    */
    public $key_equals;
    /**
    * A callable that determines when two values are considered equal.
    * @var callable
    */
    public $val_equals;

    /**
    * Maps hashes to lists of values (buckets).
    * @internal
    * @var array
    */
    protected $_dict;
    /**
    * Used for iteration. Keeps track of the current bucket.
    * @internal
    * @var array
    */
    protected $_hash_idx;
    /**
    * Used for iteration. Keeps track of the current value in the current bucket.
    * @internal
    * @var array
    */
    protected $_bucket_item_idx;
    /**
    * Used for iteration. Saves the order of the hash values.
    * @internal
    * @var array
    */
    protected $_hash_order;


    /**
    * Constructor.
    * @param mixed $default_val
    * @param Iterator $iterable
    * @param callable $key_equality
    * @param callable $value_equality
    */
    public function __construct($default_val=null, $iterable=null, $key_equality='__equals', $value_equality='__equals') {
        $this->clear();
        $this->default_val = $default_val;
        $this->key_equals = $key_equality;
        $this->val_equals = $value_equality;

        if ($iterable !== null && is_iterable($iterable)) {
            foreach ($iterable as $key => $value) {
                $this->put($key, $value);
            }
        }
    }

    /**
     * Stringifies the dict instance.
     * @return string
     */
    public function __toString() {
        $res = [];
        foreach ($this as $key => $value) {
            array_push($res, '  '.__toString($key).': '.__toString($value));
        }
        return "{\n".implode(", \n", $res)."\n}";
    }

    /**
     * Converts the Dict instance to a native array. The result has the form <code>[[$key, $value], ...]</code>
     * @return array
     */
    public function to_a() {
        return $this->items()->to_a();
    }

    /**
     * Converts the Dict instance to an instance of Arr. The result has the form <code>[[$key, $value], ...]</code>
     * @return Arr
     */
    public function to_arr() {
        return $this->items();
    }

    /**
     * Creates a copy of the Dict instance.
     * @return Dict
     */
    public function to_dict() {
        return $this->copy();
    }

    /**
     * Converts the Dict instance to an instance of Set. The result has the form <code>{[$key, $value], ...}</code>
     * @return Set
     */
    public function to_set() {
        return $this->items()->to_set();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    /**
     * @internal
     */
    protected function _get_hash($object) {
        try {
            $hash = __hash($object);
        }
        catch (Exception $e) {
            if (is_object($object) && property_exists($object, '__uniqid__')) {
                $hash = $object->__uniqid__;
            }
            else {
                $hash = uniqid('', true);
            }
        }
        return $hash;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    /**
     * @internal
     */
    public function offsetExists($offset) {
        return $this->has_key($offset);
    }

    /**
     * @internal
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * @internal
     */
    public function offsetSet($offset, $value) {
        return $this->put($offset, $value);
    }

    /**
     * @internal
     */
    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
     * Gets the value at the current position of the cursor.
     * @return mixed
     */
    public function current() {
        return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx][1];
    }

    /**
     * Gets the key at the current position of the cursor.
     * @return mixed
     */
    public function key() {
        return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx][0];
    }

    /**
     * Moves the cursor to the next key-value pair.
     */
    public function next() {
        // can proceed in current bucket
        if ($this->_bucket_item_idx < count($this->_dict[$this->_hash_order[$this->_hash_idx]]) - 1) {
            $this->_bucket_item_idx++;
        }
        // need to proceed to beginning of next bucket
        else {
            $this->_hash_idx++;
            $this->_bucket_item_idx = 0;
        }
    }

    /**
     * Moves the cursor to the first key-value pair.
     */
    public function rewind() {
        $this->_hash_idx = 0;
        $this->_bucket_item_idx = 0;
    }

    /**
    * @internal
    */
    public function valid() {
        $h_idx = $this->_hash_idx;
        return $h_idx >= 0 && $h_idx < count($this->_hash_order) && $this->_bucket_item_idx < count($this->_dict[$this->_hash_order[$h_idx]]);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (COLLECTION)

    /**
     * Adds one or more key-value pairs to the dictionary. Each pair must have the key at index zero and the value at index 1. <span class="label label-info">Chainable</span>
     * @param ArrayAccess... $pairs
     * @return Dict
     */
    public function add(...$pairs) {
        foreach ($pairs as $idx => $pair) {
            // $this->put($key, $this->default_val);
            $this->put($pair[0], $pair[1]);
        }
        return $this;
    }

    /**
     * Empties the dictionary. <span class="label label-info">Chainable</span>
     * @return Dict
     */
    public function clear() {
        $this->_dict = [];
        $this->_size = 0;
        $this->_hash_idx = 0;
        $this->_bucket_item_idx = 0;
        $this->_hash_order = [];
        return $this;
    }

    /**
     * Creates a new dictionary from this instance.
     * @return Dict
     */
    public function copy($deep=false) {
        if (!$deep) {
            return new static($this->default_val, $this);
        }

        $res = new static($this->default_val, null, $this->key_equals, $this->val_equals);
        foreach ($this as $key => $value) {
            if (is_object($key) && ($key instanceof Clonable)) {
                $key = $key->copy(true);
            }
            if (is_object($$value) && ($$value instanceof Clonable)) {
                $$value = $$value->copy(true);
            }
            $res->put($key, $value);
        }
        return $res;
    }

    /**
    * Indicates whether the Dict instance is equals to another object.
    * @param mixed $map
    * @return bool
    */
    // REVIEW
    public function equals($map) {
        if ($map instanceof self) {
            if ($this->size() !== $map->size()) {
                return false;
            }
            if (__hash($map) !== __hash($this)) {
                return false;
            }
            // hashes are equal => compare each entry
            $obj = new StdClass();
            foreach ($this as $key => $value) {
                if (!$map->has_key($key) || !call_user_func($this->val_equals, $value, $map->get($key))) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
    * Synonym for 'has_key()'.
    * @param mixed $key
    * @return bool
    */
    public function has($key) {
        return $this->has_key($key);
    }

    public function hash() {
        $res = 0;
        foreach ($this as $key => $value) {
            $res += __hash($key) + 3*__hash($value);
        }
        return $res;
    }

    /**
    * Maps each key-value pair to something new.
    * @param callable $callback The mapping function. Index zero of the return value will be the key, index one the value. <code>ArrayAccess $callback($key, $value)</code>
    * @return bool
    */
    public function map($callback) {
        $res = new Dict($this->default_val, null, $this->key_equals, $this->val_equals);
        foreach ($this as $key => $value) {
            $mapped_pair = $callback($key, $value);
            $res->put($mapped_pair[0], $mapped_pair[1]);
        }
        return $res;
    }

    /**
    * Removes a key-value pair. <span class="label label-info">Chainable</span>
    * @param mixed $key
    * @return Dict
    */
    public function remove($key) {
        if ($this->has_key($key)) {
            $hash = $this->_get_hash($key);
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $tuple) {
                if (call_user_func($this->key_equals, $key, $tuple[0])) {
                    // remove entire hash-bucket entry because will be empty
                    if (count($bucket) === 1) {
                        unset($this->_dict[$hash]);
                        // remove hash from hash order
                        $this->_hash_order = array_values(array_diff($this->_hash_order, array($hash)));
                    }
                    // remove tuple from the bucket
                    else {
                        // REVIEW reassign array keys?
                        unset($this->_dict[$hash][$idx]);
                    }
                    $this->_size--;
                    return $this;
                }
            }
        }
        return $this;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // custom methods (for better usability)

    // public function rehash_key($key) {
    //     // rehashing is not necessary for uniqid'ed keys
    //     if (is_object($key) && property_exists($key, '__uniqid__')) {
    //         return $this;
    //     }
    //     $new_hash = $this->_get_hash($key);
    //     // TODO:
    // }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (remaining function)

    /**
    * Returns a new dict with keys from $iterable and values equal to $value.
    * @param Iterator $iterable Collection of keys.
    * @param mixed $value Value for all pairs.
    * @return Dict
    */
    public static function fromkeys($iterable=[], $value=null) {
        $res = new static();
        foreach ($iterable as $idx => $key) {
            $res->put($key, $value);
        }
        return $res;
    }

    /**
    * Get the value for the given key.
    * @param mixed $key.
    * @param mixed $default_val This parameter can be used to return a special value of no value was found for the given key. This overrides <code>$this->default_val</code>
    * @return Dict
    */
    public function get($key, $default_val=null) {
        if (func_num_args() === 1) {
            $default_val = $this->default_val;
        }
        $hash = $this->_get_hash($key);

        if (array_key_exists($hash, $this->_dict)) {
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $tuple) {
                if (call_user_func($this->key_equals, $key, $tuple[0])) {
                    return $tuple[1];
                }
            }
            return $default_val;
        }
        return $default_val;
    }

    /**
    * Indicates if the dictionary contains the given key.
    * @param mixed $key.
    * @return bool
    */
    public function has_key($key) {
        $default_val = new StdClass();
        $val = $this->get($key, $default_val);
        return $val !== $default_val;
    }

    /**
    * Indicates if the dictionary contains the given value.
    * @param mixed $value.
    * @return bool
    */
    public function has_value($value) {
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                if (call_user_func($this->val_equals, $value, $tuple[1])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Returns an Arr instance containing all key-value pairs in form of Arr instances.
    * @return Arr
    */
    public function items() {
        $res = new Arr();
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->push(new Arr($tuple[0], $tuple[1]));
            }
        }
        return $res;
    }

    /**
    * Returns a Set instance containing all keys.
    * @return Arr
    */
    public function keys() {
        $res = new Set();
        foreach ($this->_dict as $hash => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->add($tuple[0]);
            }
        }
        return $res;
    }

    /**
    * Removes a key-value pair from the dictionary and returns the value of the removed item.
    * @param mixed $key
    * @param mixed $default_val
    * @return mixed
    */
    public function pop($key, $default_val=null) {
        if ($this->has($key)) {
            $res = $this->get($key);
            $this->remove($key);
            return $res;
        }
        // else
        if (func_num_args() === 1) {
            return $this->default_val;
        }
        // else
        return $default_val;
    }

    /**
    * Removes a key-value pair from the dictionary and returns an Arr instance containing the key and the value. An Exception is thrown if the dictionary is empty.
    * @param mixed $key
    * @param mixed $default_val
    * @throws Exception
    * @return mixed
    */
    public function popitem() {
        if (!$this->is_empty()) {
            foreach ($this->_dict as $hash => $bucket) {
                $tuple = $bucket[0];
                $res = new Arr($tuple[0], $tuple[1]);
                $this->remove($tuple[0]);
                return $res;
            }
        }
        throw new Exception('Dict::popitem: Cannot pop an item from an empty dictionary.');
    }

    /**
    * Adds a key-value pair to the dictionary. <span class="label label-info">Chainable</span>
    * @param mixed $key
    * @param mixed $value
    * @return Dict
    */
    public function put($key, $value) {
        $hash = $this->_get_hash($key);
        // cache pseudo hash on key
        if (is_object($key) && !property_exists($key, '__uniqid__')) {
            $key->__uniqid__ = $hash;
        }

        // add key with new hash (new bucket)
        if (!array_key_exists($hash, $this->_dict)) {
            $this->_dict[$hash] = [[$key, $value]];
            $this->_size++;
            array_push($this->_hash_order, $hash);
            return $this;
        }
        // else: add key with existing hash
        $list = $this->_dict[$hash];
        foreach ($list as $idx => $tuple) {
            if (call_user_func($this->key_equals, $key, $tuple[0])) {
                // also update key for potential reference equality
                $this->_dict[$hash][$idx][0] = $key;
                $this->_dict[$hash][$idx][1] = $value;
                return $this;
            }
        }
        array_push($this->_dict[$hash], [$key, $value]);
        $this->_size++;
        return $this;
    }

    /**
    * Sets the default value for the dictionary. <span class="label label-info">Chainable</span>
    * @param mixed $default_val
    * @return Dict
    */
    public function setdefault($default_val=null) {
        $this->default_val = $default_val;
        return $this;
    }

    /**
    * Updates the dictionary from the given iterable. All items for $iterable are put into this instance. <span class="label label-info">Chainable</span>
    * @param Iterator $iterable
    * @return Dict
    */
    public function update($iterable) {
        foreach ($iterable as $key => $value) {
            $this->put($key, $value);
        }
        return $this;
    }

    /**
    * Returns a Set instance containing all values.
    * @return Arr
    */
    public function values() {
        $res = new Set();
        foreach ($this->_dict as $hash => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->add($tuple[1]);
            }
        }
        return $res;
    }

}

class_alias('Dict', 'HashMap');
class_alias('Dict', 'Dictionary');
