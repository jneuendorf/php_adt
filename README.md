# php_adt
An OOP PHP5.6+ library providing PHP implementations of abstract data types.


## Usage

### Namespaced version

```php
<?php

// import classes into \php_adt namespace
require_once '/path/to/php_adt/php_adt/init.php';

// // import itertools functionality into \php_adt\itertools namespace
// require_once '/path/to/php_adt/php_adt/itertools/init.php';

use php_adt\Arr as Arr;
$array = new Arr(1, 'a');

?>
<html>
...
```

### Global (non-namespaced) version

```php
<?php

// import classes into global namespace
require_once '/path/to/php_adt/init.php';

// // import itertools functionality into global namespace
// require_once '/path/to/php_adt/init.itertools.php';

$array = new Arr(1, 'a');

?>
<html>
...
```


## API

See the API documentation [here](http://jneuendorf.github.io/php_adt/).
