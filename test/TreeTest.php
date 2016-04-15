<?php

require_once __DIR__.'/../Tree.php';
require_once __DIR__.'/Test.php';

$tree = new Tree();

test(
    'adding a node',
    function() use ($tree) {
        $tree->add('my node');
        echo $tree;
    },
    ''
);

?>
