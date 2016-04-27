<?php

require_once 'init.php';
require_once 'Arr.php';
require_once 'AbstractTree.php';

class Tree extends AbstractTree {

    const PRE_ORDER = 1;
    const POST_ORDER = 2;
    const LEVEL_ORDER = 3;

    public $data_source;
    protected $_children;
    protected $_parent;

    public function __construct($data_source=null, $children=null) {
        $this->_parent = null;
        $this->data_source = $data_source;
        $this->_children = new Arr();
        if ($children instanceof Arr) {
            $this->add_multiple($children);
        }
    }

    public function __toString() {
        $children = [];
        foreach ($this->_children as $idx => $child) {
            $children[] = $child.'';
        }
        return 'Tree(data_source: '.__toString($this->data_source).', children: ['.implode(', ', $children).'])';
    }

    protected static function _level_order($tree_node, $accumulator) {
        $arr = new Arr($tree_node);
        while (!$arr->is_empty()) {
            $el = $arr[0];
            $arr->remove($el);
            $accumulator->push($el);
            $arr->merge($el->children());
        }
    }

    // TODO: from_iterable (recursive)

    public static function traverse($tree_node, $order, $accumulator) {
        switch ($order) {
            case self::PRE_ORDER:
                $accumulator->push($tree_node);
                foreach ($tree_node->children() as $idx => $child) {
                    static::traverse($child, $order, $accumulator);
                }
                break;
            case self::POST_ORDER:
                foreach ($tree_node->children() as $idx => $child) {
                    static::traverse($child, $order, $accumulator);
                }
                $accumulator->push($tree_node);
                break;
            case self::LEVEL_ORDER:
                static::_level_order($tree_node, $accumulator);
                break;

            default:
                throw new Exception("Tree::traverse: Unsupported order given. Use a class constant! This error might be caused by having called the iterable method on a tree node.", 1);
        }
    }

    public function iterable($order=self::PRE_ORDER) {
        $res = new Arr();
        static::traverse($this, $order, $res);
        return $res;
    }

    // convenience methods for iterators
    public function pre_order() {
        return $this->iterable(self::PRE_ORDER);
    }

    public function post_order() {
        return $this->iterable(self::POST_ORDER);
    }

    public function level_order() {
        return $this->iterable(self::LEVEL_ORDER);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING HASHABLE
    public function hash() {
        $res = __hash($this->data_source);
        foreach ($this->_children as $child) {
            $res += __hash($child);
        }
        return $res;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING CLONABLE
    public function copy($deep=false) {
        $children = $this->_children->copy(true);
        $tree = new static($this->data_source);
        // detach children from their parent
        foreach ($children as $child) {
            $child->set_parent($tree);
        }
        $tree->set_children($children);
        return $tree;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ABSTRACT TREE

    ////////////////////////////////////////////////////////////////////////////////////
    // TREE DATA FETCHING

    public function equals($tree) {
        if ($this->size() !== $tree->size()) {
            return false;
        }
        if ($this->_children->size() !== $tree->children()->size()) {
            return false;
        }
        if ($this->hash() !== $tree->hash()) {
            return false;
        }
        $res = true;
        $children = $tree->children();
        foreach ($this->_children as $key => $child) {
            $res = $res && $child->equals($children[$idx]);
        }
        return $res;
    }

    // public function has($tree_node) {
    //     if ($this === $tree_node) {
    //         return true;
    //     }
    //     $found_nodes = $this->find(function($node) use ($tree_node) {
    //         return $node === $tree_node;
    //     });
    //     return !$found_nodes->is_empty();
    // }

    public function find($filter) {
        $res = new Set();
        foreach ($this->iterable() as $idx => $node) {
            if ($filter($node) === true) {
                $res->add($node);
            }
        }
        return $res;
    }

    public function depth() {
        $max_depth = 0;
        foreach ($this->_children as $idx => $child) {
            $depth = $child->depth() + 1;
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
        return $max_depth;
    }

    public function size() {
        $size = 1;
        foreach ($this->_children as $idx => $child) {
            $size += $child->size();
        }
        return $size;
    }

    public function level() {
        return $this->path_to_root()->size() - 1;
    }

    public function root() {
        $root = $this->_parent;
        if ($root === null) {
            return $this;
        }
        while ($root->parent()) {
            $root = $root->parent();
        }
        return $root;
    }

    public function parent() {
        return $this->_parent;
    }

    public function children() {
        return $this->_children;
    }

    public function leaves() {
        $leaves = new Arr();
        foreach ($this->_children as $idx => $child) {
            if (!$child->children()->is_empty()) {
                $leaves->merge($child->leaves());
            }
            else {
                $leaves->push($child);
            }
        }
        return $leaves;
    }

    public function is_leaf() {
        return $this->_children->is_empty();
    }

    public function siblings() {
        $res = new Arr();
        if ($this->_parent !== null && !$this->_parent->children()->is_empty()) {
            $parent_children = $this->_parent->children();
            foreach ($parent_children as $idx => $sibling) {
                if ($sibling !== $this) {
                    $res->push($sibling);
                }
            }
        }
        return $res;
    }

    public function level_siblings() {
        $level = $this->level();
        return $this->root()->find(function($node) use ($level) {
            return $node->level() === $level;
        });
    }

    public function descendants() {
        $descendants = new Arr();
        foreach ($this->_children as $idx => $child) {
            $descendants->push($child);

            if (!$child->children()->is_empty()) {
                $descendants->merge($child->descendants());
            }
        }
        return $descendants;
    }

    // includes both end nodes
    public function path_to_root() {
        $res = new Arr($this);
        $parent = $this->_parent;
        while ($parent !== null) {
            $res->push($parent);
            $parent = $parent->parent();
        }
        return $res;
    }

    public function path_from_root() {
        return $this->path_to_root()->reverse();
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // TREE MODIFICATION

    public function set_parent($new_parent) {
        return $this->_parent = $new_parent;
    }

    public function add($tree_node, $index=null) {
        // RecursionTracker::track(__FUNCTION__, func_get_args());
        if (!($tree_node instanceof static)) {
            $tree_node = new static($tree_node);
        }

        # node is attached somewhere else => correctly move between (sub)trees
        $parent = $tree_node->parent();
        if ($parent !== null && $parent !== $this) {
            $tree_node->move_to($this, $index);
            return $this;
        }
        if ($index === null) {
            $this->_children->push($tree_node);
        }
        else {
            $this->_children->insert($index, $tree_node);
        }
        $tree_node->set_parent($this);
        return $this;
    }

    public function add_multiple($tree_nodes, $index=null) {
        # inverse for correct indices
        if ($index !== null) {
            $tree_node->reverse();
        }
        foreach ($tree_nodes as $idx => $tree_node) {
            $this->add($tree_node, $index);
        }
        return $this;
    }

    public function set_children($tree_nodes) {
        $old_children = $this->_children;
        $this->_children = new Arr();
        foreach ($tree_nodes as $idx => $tree_node) {
            $this->add($tree_node);
        }
        return $old_children;
    }

    public function move_to($tree_node, $index=null) {
        $this->remove();
        $tree_node->add($this, $index);
        return $this;
    }

    public function remove() {
        if ($this->_parent !== null) {
            $this->_parent->children()->remove($this);
            $this->_parent = null;
        }
        return $this;
    }

    public function clear() {
        $this->_children->clear();
        return $this;
    }
}

?>
