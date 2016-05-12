<?php

$files = $argv[1];
$namespace = $argv[2];
if ($argc == 3) {
    $target_folder = $namespace;
}
else {
    $target_folder = $argv[3];
}

foreach (explode(' ', $files) as $file) {
    $contents = file_get_contents($file);
    $lines = explode("\n", $contents);
    unset($lines[0]);
    if ($file !== 'test/ItertoolsTest.php') {
        $contents = "<?php\n\nnamespace $namespace;\n\n";
    }
    else {
        $contents = "<?php
namespace $namespace;

use function \\php_adt\\itertools\\iter as iter;
use function \\php_adt\\itertools\\step as step;
use function \\php_adt\\itertools\\inext as inext;
use function \\php_adt\\itertools\\zip as zip;
use function \\php_adt\\itertools\\enumerate as enumerate;
use function \\php_adt\\itertools\\cycle as cycle;
use function \\php_adt\\itertools\\chain as chain;
use function \\php_adt\\itertools\\permutations as permutations;
use function \\php_adt\\itertools\\product as product;
use function \\php_adt\\itertools\\repeat as repeat;
use function \\php_adt\\itertools\\islice as islice;";
    }
    $contents .= "use \\StdClass as StdClass; use \\Exception as Exception;".implode("\n", $lines);

    $ns_file = $target_folder.'/'.str_replace('.php', '.ns.php', basename($file));
    file_put_contents($ns_file, $contents);
    echo "created '$ns_file' (namespaced version of '$file' with namespace '$namespace').\n";
}
