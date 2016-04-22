<?php

require_once __DIR__.'/../Tree.php';
require_once __DIR__.'/Test.php';

$tree = new Tree('root');
$children = $tree->children();

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
