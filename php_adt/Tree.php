<?php

namespace php_adt;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '_php_adt', 'AbstractTree.php']);
use \_php_adt\AbstractTree as AbstractTree;

/**
 * Tree is class implementing a tree structure with nodes knowing both their children and their parents.
 * In this documentation, 'tree' and 'node' will often describe the same thing from different perspectives.
 */
class Tree extends AbstractTree {
    /**
    * This constant can be passed to the 'iterable()' method to traverse the tree in pre order (parent before children).
    * @var PRE_ORDER
    */
    const PRE_ORDER = 1;
    /**
    * This constant can be passed to the 'iterable()' method to traverse the tree in post order (children before parent).
    * @var POST_ORDER
    */
    const POST_ORDER = 2;
    /**
    * This constant can be passed to the 'iterable()' method to traverse the tree in level order (top to bottom).
    * @var LEVEL_ORDER
    */
    const LEVEL_ORDER = 3;

    /**
    * This variables can hold anything associated with the tree node.
    * @var mixed $data_source
    */
    public $data_source;
    /**
    * @internal
    * @var Arr $_children
    */
    protected $_children;
    /**
    * @internal
    * @var Tree $_parent
    */
    protected $_parent;

    /**
    * Constructor.
    * @param mixed $data_source Data associated with the node.
    * @param Arr $children
    * @return Tree
    */
    public function __construct($data_source=null, $children=null) {
        $this->_parent = null;
        $this->data_source = $data_source;
        $this->_children = new Arr();
        if ($children instanceof Arr) {
            $this->add_multiple($children);
        }
    }

    /**
     * Stringifies the Tree instance.
     * @return string
     */
    public function __toString() {
        $children = [];
        foreach ($this->_children as $idx => $child) {
            $children[] = $child.'';
        }
        return 'Tree(data_source: '.__toString($this->data_source).', children: ['.implode(', ', $children).'])';
    }

    /**
     * Helper method for iterating in level order. Modifies the $accumulator parameter.
     * @internal
     */
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

    /**
     * Creates a sequence of nodes by modifying the $accumulator parameter. This is used as a helper method internally. Therefore using the 'iterable()' method is recommended.
     * @param Tree $tree_node The root of the traversal.
     * @param int $order Either of the *_ORDER constants.
     * @param Arr $accumulator The sequence that holds the tree nodes in wanted order after this method has been called.
     */
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
                throw new \Exception("Tree::traverse: Unsupported order given. Use a class constant! This error might be caused by having called the iterable method on a tree node.", 1);
        }
    }

    /**
     * Creates a sequence of nodes in a certain order.
     * @param int $order Either of the *_ORDER constants.
     * @return Arr
     */
    public function iterable($order=self::PRE_ORDER) {
        $res = new Arr();
        static::traverse($this, $order, $res);
        return $res;
    }

    // convenience methods for iterators
    /**
     * Shortcut for calling <code>$this->iterable(Tree::PRE_ORDER)</code>
     * @return Arr
     */
    public function pre_order() {
        return $this->iterable(self::PRE_ORDER);
    }

    /**
     * Shortcut for calling <code>$this->iterable(Tree::POST_ORDER)</code>
     * @return Arr
     */
    public function post_order() {
        return $this->iterable(self::POST_ORDER);
    }

    /**
    * Shortcut for calling <code>$this->iterable(Tree::LEVEL_ORDER)</code>
    * @return Arr
    */
    public function level_order() {
        return $this->iterable(self::LEVEL_ORDER);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING HASHABLE
    /**
    * Calculates a hash value of the tree.
    * @return int
    */
    public function hash() {
        // deeper levels has less impact because the greater the level the more nodes there are on that level
        // so we wanna keep the hash as small as possible
        $res = __hash($this->data_source) * ($this->depth() + 1);
        foreach ($this->_children as $child) {
            $res += __hash($child);
        }
        return $res;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING CLONABLE
    /**
    * Creates a deep copy of the tree.
    * @param bool $deep This parameter has no effect and exists due to the interface of clonable.
    * @return Tree
    */
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

    /**
    * Indicates whether the tree is equal to another object.
    * @param mixed $tree
    * @return bool
    */
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
        foreach ($this->_children as $idx => $child) {
            $res = $res && $child->equals($children[$idx]);
        }
        return $res;
    }

    /**
    * Searches the tree for a node that fulfills the $filter and returns a Set of those nodes.
    * @param callable $filter The filter determines which nodes are considered valid. <code>boolean $filter($node)</code>
    * @return Set
    */
    public function find($filter) {
        $res = new Set();
        foreach ($this->iterable(self::PRE_ORDER) as $idx => $node) {
            if ($filter($node) === true) {
                $res->add($node);
            }
        }
        return $res;
    }

    /**
    * Returns the depth of the tree.
    * @return int
    */
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

    /**
    * Returns the size of the tree. That means the number of all nodes in the tree (including the root).
    * @return int
    */
    public function size() {
        $size = 1;
        foreach ($this->_children as $idx => $child) {
            $size += $child->size();
        }
        return $size;
    }

    /**
    * Returns the level of the node.
    * @return int
    */
    public function level() {
        return $this->path_to_root()->size();
    }

    /**
    * Returns the root of the node.
    * @return Tree
    */
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

    /**
    * Returns the parent of the node.
    * @return Tree
    */
    public function parent() {
        return $this->_parent;
    }

    /**
    * Returns the children of the node.
    * @return Arr
    */
    public function children() {
        return $this->_children;
    }

    /**
    * Returns all leaves of the tree.
    * @return Arr
    */
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

    /**
    * Indicates whether the node is a leaf (a leaf has no children).
    * @return bool
    */
    public function is_leaf() {
        return $this->_children->is_empty();
    }

    /**
    * Returns a list of all siblings with the same parent.
    * @return Arr
    */
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

    /**
    * Returns a list of all nodes in the tree that have the same level.
    * @return Arr
    */
    public function level_siblings() {
        $level = $this->level();
        return $this->root()->find(function($node) use ($level) {
            return $node->level() === $level;
        });
    }

    /**
    * Returns a list of all nodes in the tree (except the current node).
    * @return Arr
    */
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

    /**
    * Returns a list of nodes that describes the path to the root node. The current node is not included.
    * @return Arr
    */
    public function path_to_root() {
        $res = new Arr();
        $parent = $this->_parent;
        while ($parent !== null) {
            $res->push($parent);
            $parent = $parent->parent();
        }
        return $res;
    }

    // /**
    // * Returns a list of nodes that describes the path from the root node. The current node is not included.
    // * @return Arr
    // */
    // public function path_from_root() {
    //     return $this->path_to_root()->reverse();
    // }

    ////////////////////////////////////////////////////////////////////////////////////
    // TREE MODIFICATION

    /**
    * Sets the parent of a node. This method should be used carefully because the parent's children are not modified. <span class="label label-info">Chainable</span>
    * @return Tree
    */
    public function set_parent($new_parent) {
        $this->_parent = $new_parent;
        return $this;
    }

    /**
    * Adds a node as child of the current node.  <span class="label label-info">Chainable</span>
    * @param mixed $tree_node The new node. Also the data source for the node can be passed (it will be wrapped into a node automatically).
    * @param int $index The index can define at what position (among the siblings) the node will be added.
    * @return Tree
    */
    public function add($tree_node, $index=null) {
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

    /**
    * Adds multiple nodes to the tree. <span class="label label-info">Chainable</span>
    * @param Arr $tree_nodes The nodes to add.
    * @param int $index The index can define at what position (among the siblings) the nodes will be added.
    * @return Tree
    */
    public function add_multiple($tree_nodes, $index=null) {
        # inverse for correct indices
        if ($index !== null) {
            $tree_nodes->reverse();
        }
        foreach ($tree_nodes as $idx => $tree_node) {
            $this->add($tree_node, $index);
        }
        return $this;
    }

    /**
    * Sets the children of a node and returns the previous ones.
    * @param Arr $tree_nodes The new children to add.
    * @return Arr The children of the node before calling this method.
    */
    public function set_children($tree_nodes) {
        $old_children = $this->_children;
        $this->_children = new Arr();
        foreach ($tree_nodes as $idx => $tree_node) {
            $this->add($tree_node);
        }
        return $old_children;
    }

    /**
    * Moves the node to another place in the tree. It must not be used for moving a node between different trees! <span class="label label-info">Chainable</span>
    * @param Tree $new_parent The new parent of the node.
    * @param int $index The index can define at what position (among the siblings) the node will be added.
    * @return Tree
    */
    public function move_to($new_parent, $index=null) {
        $this->remove();
        $new_parent->add($this, $index);
        return $this;
    }

    /**
    * Removes a node (and the entire subtree) from the tree. <span class="label label-info">Chainable</span>
    * @return Tree
    */
    public function remove() {
        if ($this->_parent !== null) {
            $this->_parent->children()->remove($this);
            $this->_parent = null;
        }
        return $this;
    }

    /**
    * Removes all nodes from the tree. The children's parents will be set to null. <span class="label label-info">Chainable</span>
    * @return Tree
    */
    public function clear() {
        foreach ($this->_children as $child) {
            $child->set_parent(null);
        }
        $this->_children->clear();
        return $this;
    }
}
