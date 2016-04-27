<?php

require_once __DIR__.'/../Tree.php';
require_once __DIR__.'/Test.php';


echo '<h1>Tree class</h1>';


section('tree creation', subsection('', new Test(
    'new',
    function() {
        $children = new Arr(new Tree(1), new Tree(2), new Tree(3), new Tree('4'));
        $tree = new Tree('root', $children);

        return expect($tree->size(), 'size')->to_be(5) &&
        expect(count($tree), 'count')->to_be(5) &&
        expect($tree->children()->size(), 'num children')->to_be(4) &&
        expect($tree->children()->first()->parent())->to_be($tree);
    }
)));


section('tree instance methods',
    subsection(
        '',
        new Test(
            'tree "interface"',
            [
                function() {
                    $tree = $this->tree->copy();
                    $tree->add('new node');
                    $tree->add('new first node', 0);
                    return expect($tree->size(), 'add')->to_be($this->tree->size() + 2) &&
                    expect($tree->children()->first()->data_source, 'add')->to_be('new first node') &&
                    expect($tree->children()->last()->data_source, 'add')->to_be('new node');
                },
                function() {
                    return expect($this->tree->children(), 'children')->to_be($this->children);
                },
                function() {
                    return expect($this->tree->copy()->clear()->size(), 'clear')->to_be(1);
                },
                function() {
                    return expect($this->tree->depth(), 'depth')->to_be(1) &&
                    expect((new Tree())->depth(), 'depth')->to_be(0);
                },
                function() {
                    $tree = $this->tree->copy();
                    $tree->children()->first()->add('subchild');
                    return expect($tree->descendants()->to_set(), 'descendants')->to_be(new Set($tree->children()->first()->children()->first(), ...$tree->children()));
                },
                function() {
                    $nodes = $this->tree->find(function($node) {
                        return $node->data_source === 3;
                    });
                    return expect($nodes->size(), 'find')->to_be(1) &&
                    expect($nodes->has($this->tree->children()->third()), 'find')->to_be(true);
                },
                function() {
                    return expect($this->tree->hash(), 'hash')->to_be($this->tree->copy()->hash()) &&
                    expect($this->tree->hash(), 'hash')->not_to_be($this->tree->copy()->clear()->hash());
                },
                function() {
                    return expect($this->tree->is_leaf(), 'is_leaf')->to_be(false) &&
                    expect($this->tree->children()->first()->is_leaf(), 'is_leaf')->to_be(true) &&
                    expect((new Tree())->is_leaf(), 'is_leaf')->to_be(true);
                },
                function() {
                    return expect($this->tree->leaves(), 'leaves')->to_be($this->tree->children());
                },
                function() {
                    $tree = $this->tree->copy();
                    $tree->children()->first()->add('subchild1');
                    $tree->children()->third()->add('subchild3');
                    return expect($tree->children()->first()->children()->first()->level_siblings(), 'level_siblings')->to_be(new Set($tree->children()->first()->children()->first(), $tree->children()->third()->children()->first()));
                },
                function() {
                    return expect($this->tree->level(), 'level')->to_be(0) &&
                    expect($this->tree->children()->first()->level(), 'level')->to_be(1);
                },
                function() {
                    return expect($this->tree->parent(), 'parent')->to_be(null) &&
                    expect($this->tree->children()->first()->parent(), 'parent')->to_be($this->tree);
                },
                function() {
                    $tree = $this->tree->copy();
                    $tree->children()->first()->add('subchild1');
                    $tree->children()->first()->children()->first()->remove();

                    $res = expect($tree->children()->first()->children()->is_empty(), 'remove')->to_be(true);

                    $tree->children()->last()->remove();
                    $expected = $this->tree->children()->copy();
                    $expected->remove_at(-1);

                    return $res && expect($tree->children(), 'remove')->to_be($expected);
                },
                function() {
                    return expect($this->tree->root(), 'root')->to_be($this->tree) &&
                    expect($this->tree->children()->first()->root(), 'root')->to_be($this->tree);
                },
                function() {
                    return expect($this->tree->siblings()->is_empty(), 'siblings')->to_be(true) &&
                    expect($this->tree->children()->first()->siblings(), 'siblings')->to_be($this->tree->children()['1:']);
                },
            ],
            function() {
                $this->children = new Arr(new Tree(1), new Tree(2), new Tree(3), new Tree('4'));
                $this->tree = new Tree('root', $this->children);
            }
        ),
        new Test(
            'remaining methods',
            [
                // function() {
                //     return expect($this->set->difference($this->set2), 'difference')->to_be(new Set(1,3));
                // },
                // function() {
                //     $set = $this->set->copy();
                //     $set->difference_update($this->set2);
                //     return expect($set, 'difference_update')->to_be($this->set->difference($this->set2));
                // },
            ],
            function() {
                // init
            }
        )
    )
);


section('iteration', subsection('foreach + tree->iterable()',
    new Test(
        'preorder',
        function() {
            $children = new Arr(new Tree(1), new Tree(2), new Tree(3), new Tree('4'));
            $tree = new Tree('root', $children);

            $iterated = new Arr();
            foreach ($tree->iterable(Tree::PRE_ORDER) as $idx => $node) {
                $iterated->push($node->data_source);
            }
            return expect($iterated)->to_be(new Arr('root', 1, 2, 3, '4'));
        }
    ),
    new Test(
        'postorder',
        function() {
            $children = new Arr(new Tree(1), new Tree(2), new Tree(3), new Tree('4'));
            $tree = new Tree('root', $children);

            $iterated = new Arr();
            foreach ($tree->iterable(Tree::POST_ORDER) as $idx => $node) {
                $iterated->push($node->data_source);
            }
            return expect($iterated)->to_be(new Arr(1, 2, 3, '4', 'root'));
        }
    ),
    new Test(
        'level order',
        function() {
            $children = new Arr(new Tree(1, new Arr(new Tree('subchild'))), new Tree(2), new Tree(3), new Tree('4'));
            $tree = new Tree('root', $children);

            $iterated = new Arr();
            foreach ($tree->iterable(Tree::LEVEL_ORDER) as $idx => $node) {
                $iterated->push($node->data_source);
            }
            return expect($iterated)->to_be(new Arr('root', 1, 2, 3, '4', 'subchild'));
        }
    )
));

// $tree = new Tree('root');
// $children = $tree->children();

// test(
//     'adding node 1',
//     function() use ($tree) {
//         $tree->add('node1');
//         echo $tree;
//     }
// );
// test(
//     'adding node 2',
//     function() use ($tree) {
//         $tree->add(new Arr('node2', false));
//         echo $tree;
//     }
// );
// test(
//     'adding node 2.1',
//     function() use ($children) {
//         $children->second()->add('node2.1 yeah');
//     },
//     $tree
// );
//
// test(
//     '<br>ITERATION:',
//     function() use ($tree) {
//         foreach ($tree->iterable(Tree::POST_ORDER) as $idx => $node) {
//             echo $node.'<br>';
//         }
//     }
// );
//
// test(
//     'depth',
//     function() use ($tree) {
//         echo $tree->depth();
//     }
// );
// test(
//     'level',
//     function() use ($children) {
//         echo $children[1]->children()->first()->level();
//     }
// );
// test(
//     'leaves',
//     function() use ($tree) {
//         echo $tree->leaves();
//     }
// );




?>
