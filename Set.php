<?php

require_once 'init.php';
require_once 'funcs.php';
require_once 'AbstractCollection.php';

class Set extends AbstractCollection implements ArrayAccess, Iterator {

    // maps hashes to lists of values
    protected $_dict;
    // protected $_size;
    // those 3 vars are needed for the iterator interface
    protected $_hash_idx;
    protected $_bucket_item_idx;
    protected $_hash_order;

    public function __construct(...$elements) {
        $this->clear();
        foreach ($elements as $idx => $element) {
            $this->add($element);
        }
    }

    public static function from_iterable($iterable) {
        $res = new static();
        foreach ($iterable as $idx => $element) {
            $res->add($element);
        }
        return $res;
    }

    public function __toString() {
        $res = [];
        foreach ($this as $idx => $element) {
            if (is_string($element)) {
                $e = "'$element'";
            }
            elseif (is_bool($element)) {
                $e = $element ? 'true' : 'false';
            }
            else {
                try {
                    $e = "$element";
                } catch (Exception $e) {
                    $e = var_export($element, true);
                }
            }
            array_push($res, '  '.$e);
        }
        return "{\n".implode(", \n", $res)."\n}";
    }

    // PROTECTED

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
    // IMPLEMENTING ARRAYACCESS

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetGet($offset) {
        if ($this->has($offset)) {
            return $offset;
        }
        return null;
    }

    public function offsetSet($offset, $value) {
        $this->add($value);
    }

    public function offsetUnset($offset) {
        if ($this->has($offset)) {
            $this->remove($offset);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    public function current() {
        return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx];
    }

    public function key() {
        $idx = 0;
        for ($i = 0; $i < $this->_hash_idx; $i++) {
            $idx += count($this->_dict[$this->_hash_order[$i]]);
        }
        return $idx + $this->_bucket_item_idx;
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
    // IMPLEMENTING COLLECTION

    public function add($element) {
        $hash = $this->_get_hash($element);
        // cache pseudo hash on key
        if (is_object($element) && !property_exists($element, '__uniqid__')) {
            $element->__uniqid__ = $hash;
        }

        // add key with new hash (new bucket)
        if (!array_key_exists($hash, $this->_dict)) {
            $this->_dict[$hash] = [$element];
            $this->_size++;
            array_push($this->_hash_order, $hash);
            return $this;
        }
        // else: add key with existing hash
        $bucket = $this->_dict[$hash];
        foreach ($bucket as $idx => $bucket_element) {
            if (__equals($element, $bucket_element)) {
                // update for potential reference equality
                $this->_dict[$hash][$idx] = $element;
                return $this;
            }
        }
        array_push($this->_dict[$hash], $element);
        $this->_size++;
        return $this;
    }

    public function clear() {
        $this->_dict = [];
        $this->_size = 0;
        $this->_hash_idx = 0;
        $this->_bucket_item_idx = 0;
        $this->_hash_order = [];
        return $this;
    }

    public function copy($deep=false) {
        return static::from_iterable($this);
    }

    public function equals($set) {
        if ($set instanceof Set) {
            if ($this->size() !== $set->size()) {
                return false;
            }
            if (__hash($set) !== __hash($this)) {
                return false;
            }
            // hashes are equal => compare each element
            foreach ($this as $idx => $element) {
                if (!$set->has($element)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function has($element) {
        $hash = $this->_get_hash($element);
        if (array_key_exists($hash, $this->_dict)) {
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $bucket_element) {
                if (__equals($element, $bucket_element)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function hash() {
        $res = 0;
        foreach ($this as $idx => $value) {
            $res += __hash($value);
        }
        return $res;
    }

    public function remove($element) {
        if ($this->has($element)) {
            $hash = $this->_get_hash($element);
            $bucket = $this->_dict[$hash];
            foreach ($bucket as $idx => $bucket_element) {
                if (__equals($element, $bucket_element)) {
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

    // PYTHON API

    public function difference($set) {
        $res = new Set();
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                $res->add($element);
            }
        }
        return $res;
    }

    public function difference_udpate($set) {
        foreach ($set as $idx => $element) {
            $this->remove($element);
        }
        return $this;
    }

    public function discard($element) {
        return $this->remove($element);
    }

    public function intersection($set) {
        $res = new Set();
        foreach ($set as $idx => $element) {
            if ($this->has($element)) {
                $res->add($element);
            }
        }
        return $res;
    }

    public function intersection_update($set) {
        foreach ($set as $idx => $element) {
            if (!$this->has($element)) {
                $res->remove($element);
            }
        }
        return $this;
    }

    public function isdisjoint($set) {
        return $this->intersection($set)->is_empty();
    }

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

    public function issuperset($set) {
        return $set->issubset($this);
    }

    // == union without intersection
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
        return $set;
    }

    public function symmetric_difference_udpate($set) {
        // union
        $this->update($set);
        // remove intersection
        foreach ($set as $idx => $element) {
            // element in intersection
            if ($this->has($element)) {
                $this->remove($element);
            }
        }
        return $this;
    }

    public function union($set) {
        $res = $this->copy();
        foreach ($set as $idx => $element) {
            $res->add($element);
        }
        return $res;
    }

    // API-CHANGE: added for naming consistency
    public function union_update($set) {
        return $this->update($set);
    }

    // union in place
    public function update($set) {
        foreach ($set as $idx => $element) {
            $this->add($element);
        }
        return $this;
    }

}

?>
