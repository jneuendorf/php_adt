<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tests</title>
    <script type="text/javascript" src="jquery-2.2.1.min.js"></script>
</head>
<body>
<?php
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'php_adt', 'init.php']);
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'php_adt', 'itertools', 'init.php']);
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'Test.php']);

use function \php_adt\expect as expect;


echo '<h1>"undefined" constant</h1>';
\php_adt\p('echoing undefined: ', undefined, '<br><br>');
expect(undefined)->not_to_be(null);
expect(undefined)->not_to_be([]);
expect(undefined)->to_be(undefined);


// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'ItertoolsTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'ArrTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'CharArrTest.php']);
require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'TupleTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'DictTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'SetTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'TreeTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'ConversionTest.php']);
// require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'test', 'GenewraporTest.php']);

\php_adt\Test::print_stats();
?>
</body>
</html>
