<?php

require_once 'init.php';
require_once 'AbstractMap.php';
require_once 'Arr.php';

// NOTE: supports mutable objects as keys but if the key's hash changes Dict does NOT take care of it
// TODO: inherit from Set
class Dict extends AbstractMap implements ArrayAccess, Iterator {

    public $default_val;

    // maps hashes to lists of values
    protected $_dict;
    // protected $_size;
    // those 3 vars are needed for the iterator interface
    protected $_hash_idx;
    protected $_bucket_item_idx;
    protected $_hash_order;

    public function __construct($default_val=null, $iterable=null) {
        $this->clear();
        $this->default_val = $default_val;

        if ($iterable !== null) {
            foreach ($iterable as $key => $value) {
                $this->put($key, $value);
            }
        }
    }

    public function __toString() {
        $res = [];
        foreach ($this as $key => $value) {
            array_push($res, '  '.__toString($key).': '.__toString($value));
        }
        return "{\n".implode(", \n", $res)."\n}";
    }

    public function to_arr() {
        return $this->items();
    }

    public function to_dict() {
        return $this->copy();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED


    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    public function offsetExists($offset) {
        return $this->has_key($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        return $this->put($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->remove($offset);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    public function current() {
        return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx][1];
    }

    public function key() {
        return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx][0];
    }

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

    public function rewind() {
        $this->_hash_idx = 0;
        $this->_bucket_item_idx = 0;
    }

    public function valid() {
        $h_idx = $this->_hash_idx;
        return $h_idx >= 0 && $h_idx < count($this->_hash_order) && $this->_bucket_item_idx < count($this->_dict[$this->_hash_order[$h_idx]]);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING MAP (COLLECTION)

    // public function add($key, $value) {
    //     return $this->put($key, $value);
    // }

    public function clear() {
        $this->_dict = [];
        $this->_size = 0;
        $this->_hash_idx = 0;
        $this->_bucket_item_idx = 0;
        $this->_hash_order = [];
        return $this;
    }

    public function copy($deep = false) {
        return new static($this->default_val, $this);
    }

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
                $map_value = $map->get($key, $obj);
                if (!$map->has_key($key) || $map_value === $obj || !__equals($value, $map_value)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

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

    public function remove($key) {
        if ($this->has_key($key)) {
            $hash = $this->_get_hash($key);
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $tuple) {
                if (__equals($key, $tuple[0])) {
                    // remove entire hash-bucket entry because will be empty
                    if (count($bucket) === 1) {
                        unset($this->_dict[$hash]);
                        // remove hash from hash order
                        $this->_hash_order = array_values(array_diff($this->_hash_order, array($hash)));
                    }
                    // remove tuple from the bucket
                    else {
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

    public static function fromkeys($iterable=[], $value=null) {
        $res = new static();
        foreach ($iterable as $idx => $key) {
            $res->put($key, $value);
        }
        return $res;
    }

    public function get($key, $default_val=null) {
        if (func_num_args() === 1) {
            $default_val = $this->default_val;
        }
        $hash = $this->_get_hash($key);

        if (array_key_exists($hash, $this->_dict)) {
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $tuple) {
                if (__equals($key, $tuple[0])) {
                    return $tuple[1];
                }
            }
            return $default_val;
        }
        return $default_val;
    }

    public function has_key($key) {
        $default_val = new StdClass();
        $val = $this->get($key, $default_val);
        return $val !== $default_val;
    }

    public function has_value($value) {
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                if (__equals($value, $tuple[1])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function items() {
        $res = new Arr();
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->push(new Arr(__clone($tuple)));
            }
        }
        return $res;
    }

    public function keys() {
        $res = new Arr();
        foreach ($this->_dict as $key => $bucket) {
            foreach ($bucket as $idx => $tuple) {
                $res->push($tuple[0]);
            }
        }
        return $res;
    }

    public function pop($key, $default=null) {
        if ($this->has($key)) {
            $res = $this->get($key);
            $this->remove($key);
            return $res;
        }
        // else
        return $default;
    }

    public function popitem() {
        foreach ($this->dict as $hash => $bucket) {
            $tuple = $bucket[0];
            $res = new Arr(...$tuple);
            $this->remove($tuple[0]);
            return $res;
        }
    }

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
            if (__equals($key, $tuple[0])) {
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

    public function setdefault() {

    }

    public function update($iterable) {
        foreach ($iterable as $key => $value) {
            $this->put($key, $value);
        }
        return $this;
    }

    public function values() {
        $res = new Arr();
        foreach (array_values($this->_dict) as $key => $list) {
            $res->merge($list);
        }
        return $res->values();
    }

}

class_alias('Dict', 'HashMap');
class_alias('Dict', 'Dictionary');

?>
