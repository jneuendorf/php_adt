<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <script type="text/javascript" src="jquery-2.2.1.min.js"></script>
</head>
<body>
<?php

// use namespaced
require_once __DIR__.'/php_adt/init.php';
require_once __DIR__.'/php_adt/itertools/init.php';



require_once 'test/Test.ns.php';

use function \php_adt\expect as expect;


echo '<h1>"undefined" constant</h1>';
p('echoing undefined: ', undefined, '<br><br>');
expect(undefined)->not_to_be(null);
expect(undefined)->not_to_be([]);
expect(undefined)->to_be(undefined);


require_once 'test/ItertoolsTest.ns.php';
require_once 'test/ArrTest.ns.php';
require_once 'test/CharArrTest.ns.php';
require_once 'test/DictTest.ns.php';
require_once 'test/SetTest.ns.php';
require_once 'test/TreeTest.ns.php';
require_once 'test/ConversionTest.ns.php';

require_once 'test/GenewraporTest.ns.php';

\php_adt\Test::print_stats();

?>
</body>
</html>
