<?php

require_once 'init.php';
require_once 'AbstractCollection.php';

class Set extends AbstractCollection implements ArrayAccess, Iterator {

    // maps hashes to lists of values
    protected $_dict;
    
    public function __construct(...$elements) {
        $this->_dict = new Dict();
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
            array_push($res, '  '.__toString($element));
        }
        return "{\n".implode(", \n", $res)."\n}";
    }

    public function dict() {
        return $this->_dict;
    }

    // PROTECTED

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
        return $this->_dict->key();
        // return $this->_dict[$this->_hash_order[$this->_hash_idx]][$this->_bucket_item_idx];
    }

    public function key() {
        // $idx = 0;
        // for ($i = 0; $i < $this->_hash_idx; $i++) {
        //     $idx += count($this->_dict[$this->_hash_order[$i]]);
        // }
        // return $idx + $this->_bucket_item_idx;
        return $this->_dict->key();
    }

    public function next() {
        // // can proceed in current bucket
        // if ($this->_bucket_item_idx < count($this->_dict[$this->_hash_order[$this->_hash_idx]]) - 1) {
        //     $this->_bucket_item_idx++;
        // }
        // // need to proceed to beginning of next bucket
        // else {
        //     $this->_hash_idx++;
        //     $this->_bucket_item_idx = 0;
        // }
        $this->_dict->next();
    }

    public function rewind() {
        // $this->_hash_idx = 0;
        // $this->_bucket_item_idx = 0;
        $this->_dict->rewind();
    }

    public function valid() {
        // $h_idx = $this->_hash_idx;
        // return $h_idx >= 0 && $h_idx < count($this->_hash_order) && $this->_bucket_item_idx < count($this->_dict[$this->_hash_order[$h_idx]]);
        return $this->_dict->valid();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING COLLECTION

    public function add(...$elements) {
        foreach ($elements as $key => $element) {
            $this->_dict->put($element, true);
        }
        return $this;
    }

    public function clear() {
        $this->_dict->clear();
        return $this;
    }

    public function copy($deep=false) {
        return static::from_iterable($this);
    }

    public function equals($set) {
        if ($set instanceof self) {
            return $this->_dict->equals($set->dict());
        }
        return false;
    }

    public function has($element) {
        return $this->_dict->has($element);
    }

    public function hash() {
        $res = 0;
        foreach ($this as $idx => $value) {
            $res += __hash($value);
        }
        return $res;
    }

    public function map($callback) {
        $res = new Set();
        foreach ($this as $idx => $element) {
            $res->add($callback($element));
        }
        return $res;
    }

    public function remove($element) {
        $this->_dict->remove($element);
        return $this;
    }

    public function size() {
        return $this->_dict->size();
    }

    public function to_a() {
        $res = [];
        foreach ($this->_dict->keys() as $idx => $key) {
            $res[] = $key;
        }
        return $res;
    }

    public function to_arr() {
        return Arr::from_iterable($this->_dict->keys());
    }

    public function to_dict() {
        // return $this->_dict->copy();
        $keys = $this->_dict->keys();
        $res = new Dict();
        foreach ($keys as $idx => $key) {
            $res->put($key, $key);
        }
        return $res;
    }

    public function to_set() {
        return $this->copy();
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

    public function difference_update($set) {
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
        foreach ($this as $idx => $element) {
            if (!$set->has($element)) {
                $this->remove($element);
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
        return $res;
    }

    public function symmetric_difference_update($set) {
        $sym_diff = $this->symmetric_difference($set);
        $this->clear();
        foreach ($sym_diff as $idx => $element) {
            $this->add($element);
        }
        // // union
        // $this->update($set);
        // // remove intersection
        // foreach ($this as $idx => $element) {
        //     var_dump('checking');
        //     var_dump($element);
        //     // element in intersection
        //     if ($set->has($element)) {
        //         var_dump("removed it!\n");
        //         $this->remove($element);
        //     }
        // }
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
