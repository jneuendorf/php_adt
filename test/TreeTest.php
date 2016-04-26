<?php

require_once __DIR__.'/../Tree.php';
require_once __DIR__.'/Test.php';

$tree = new Tree('root');
$children = $tree->children();

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
