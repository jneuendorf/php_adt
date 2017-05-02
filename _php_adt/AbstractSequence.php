<?php

/**
* @package _php_adt
*/
namespace _php_adt;


require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'Super.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'funcs.php']);


abstract class AbstractSequence extends Super implements \ArrayAccess, \Iterator {
    // abstract public function clear();
    // $index can potentially be used for slicing, too
    public function get($index) {
        return $this->offsetGet($index);
    }
    // $index must be numeric
    abstract protected function _get_at($index);
    abstract public function slice($start=0, $length=null);

    abstract public function to_a();
    abstract public function to_arr();
    abstract public function to_set();
    abstract public function to_str();
    abstract public function to_dict();

    ////////////////////////////////////////////////////////////////////////////////////
    // PARTLY IMPLEMENTING ARRAYACCESS (those methods are suggestions only!)
    // TODO: somehow share code with Arr class (sequence should NOT inherit from collection though)
    // TODO: => use traits!
    /**
     * @internal
    */
    protected function _adjust_offset($offset) {
        if ($this->offsetExists($offset)) {
            if ($offset < 0) {
                $offset += $this->size();
            }
            return $offset;
        }
        throw new \Exception("Undefined offset $offset!", 1);
    }

    /**
     * @internal
    */
    protected function _get_start_end_from_offset($offset) {
        if (is_array($offset)) {
            if (count($offset) === 1) {
                $offset[] = null;
            }
            if (is_int($offset[0]) && is_int($offset[1])) {
                $use_slicing = true;
                $start = $offset[0];
                $end = $offset[1];
            }
            else {
                throw new \Exception('Invalid array offset '.\php_adt\__toString($offset).'. Array offsets must have the form \'[int1(,int2)]\'.');
            }
        }
        else if (is_string($offset)) {
            $offset = preg_replace('/\s+/', '', $offset);
            if (preg_match('/^\-?\d+\:(\-?\d+)?$/', $offset) === 1) {
                $use_slicing = true;
                $parts = explode(':', $offset);
                $start = (int) $parts[0];
                if (strlen($parts[1]) > 0) {
                    $end = (int) $parts[1];
                }
                else {
                    $end = null;
                }
            }
            else {
                throw new \Exception('Invalid string offset \''.$offset.'\'. String offsets must have the form \'int1:(int2)\'.');
            }
        }
        else if (is_int($offset)) {
            $use_slicing = false;
            $start = $offset;
            $end = $offset;
        }
        else {
            throw new \Exception('Invalid offset. Use null ($a[]=4) to push, int for index access or for slicing use [start, end] or \'start:end\'!');
        }
        try {
            $end = $this->_adjust_offset($end);
        } catch (\Exception $e) {
            $end = $this->size();
        }

        return [
            'start' => $this->_adjust_offset($start),
            'end' => $end,
            'slicing' => $use_slicing
        ];
    }

    /**
     * @internal
    */
    public function offsetExists($offset) {
        if (is_int($offset)) {
            if ($offset >= 0) {
                return $offset < $this->size();
            }
            // else: negative
            return abs($offset) <= $this->size();
        }
        return false;
    }

    /**
     * @internal
    */
    public function offsetGet($offset) {
        $bounds = $this->_get_start_end_from_offset($offset);
        if (!$bounds['slicing']) {
            return $this->_get_at($bounds['start']);
        }
        return $this->slice($bounds['start'], $bounds['end'] - $bounds['start']);
    }

}
