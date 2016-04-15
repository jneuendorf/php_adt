<?php

require_once 'init.php';
require_once 'funcs.php';
require_once 'AbstractTree.php';

class Tree {

    public $data_source;
    protected $_children;
    protected $_parent;

    public function __construct($data_source=null, $children=null) {
        $this->data_source = $data_source;
        $this->_children = $children === null ? new Arr() : $children;
        $this->_parent = null;
    }

    // TODO
    public function has($tree_node) {
        return $this === $tree_node || (false);
    }

    public function find($filter) {
        $res = new Arr();
        foreach ($this as $parent => $children) {
            $res->push();
        }
        return $res;
    }

    public function depth() {
        $max_depth = 0;
        foreach ($this->_children as $idx => $child) {
            $depth = $child->depth();
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
        // TODO use pathToRoot
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
        // TODO
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
        foreach ($tree_nodes->reversed() as $idx => $tree_node) {
            $this->add($tree_node, $index);
        }
        return $this;

    }

    public function set_children($tree_nodes) {
        $old_children = $this->_children;
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

    public function remove($tree_node) {
        if ($this->_parent !== null) {
            $this->_parent->set_children($this->_parent->children()->without($this));
            $this->_parent = null;
        }
        return $this;
    }

}

/*
##################################################################################################
# TRAVERSING THE TREE
_traverse: (callback, orderMode = @orderMode or "postorder", info = {idx: 0, ctx: @}) ->
    return @[orderMode](callback, null, info)

each: () ->
    return @_traverse.apply(@, arguments)

postorder: (callback, level = 0, info = {idx: 0, ctx: @}) ->
    for child in @children when child?
        child.postorder(callback, level + 1, info)
        info.idx++

    if callback.call(info.ctx, @, level, info.idx) is false
        return @
    return @

preorder: (callback, level = 0, info = {idx: 0, ctx: @}) ->
    if callback.call(info.ctx, @, level, info.idx) is false
        return @

    for child in @children when child?
        child.preorder(callback, level + 1, info)
        info.idx++

    return @

inorder: (callback, level = 0, index = @children.length // 2, info = {idx: 0, ctx: @}) ->
    for i in [0...index]
        @children[i]?.inorder(callback, level + 1, index, info)
        info.idx++

    if callback.call(info.ctx, @, level, info.idx) is false
        return @

    for i in [index...@children.length]
        @children[i]?.inorder(callback, level + 1, index, info)
        info.idx++

    return @

levelorder: (callback, level = 0, info = {idx: 0, ctx: @, levelIdx: 0}) ->
    list = [@]

    startLevel = @level
    prevLevel = 0

    while list.length > 0
        # remove 1st elem from list
        el = list.shift()

        # this is only in case any child is null. this is the case with binary trees
        if el?
            currentLevel = el.level - startLevel

            # going to new level => reset level index
            if currentLevel > prevLevel
                info.levelIdx = 0

            if callback.call(info.ctx, el, currentLevel, info) is false
                return @

            prevLevel = currentLevel

            info.idx++
            info.levelIdx++

            list = list.concat el.children

    return @
*/

?>
