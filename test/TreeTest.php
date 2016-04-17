<?php

require_once __DIR__.'/../Tree.php';
require_once __DIR__.'/Test.php';

$tree = new Tree('root');

test(
    'adding 1st node',
    function() use ($tree) {
        $tree->add('node1');
        echo $tree;
    }
);
test(
    'adding 2nd node',
    function() use ($tree) {
        $tree->add(new Arr('node', false));
        echo $tree;
    }
);

test(
    '<br>ITERATION:',
    function() use ($tree) {
        foreach ($tree->iterable(Tree::LEVEL_ORDER) as $idx => $node) {
            echo $node.'<br>';
        }
    }
);


?>
