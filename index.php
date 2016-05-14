<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <script type="text/javascript" src="jquery-2.2.1.min.js"></script>
</head>
<body>
<?php

require_once 'init.php';
require_once 'init.itertools.php';
require_once 'test/Test.php';


echo '<h1>"undefined" constant</h1>';
p('echoing undefined: ', undefined, '<br><br>');
expect(undefined)->not_to_be(null);
expect(undefined)->not_to_be([]);
expect(undefined)->to_be(undefined);


require_once 'test/ItertoolsTest.php';
require_once 'test/ArrTest.php';
require_once 'test/CharArrTest.php';
require_once 'test/DictTest.php';
require_once 'test/SetTest.php';
require_once 'test/TreeTest.php';
require_once 'test/ConversionTest.php';
require_once 'test/GenewraporTest.php';

Test::print_stats();

?>
</body>
</html>
