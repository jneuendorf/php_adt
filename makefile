# the following files must be prefixed by the namespace php_adt
PHP_ADT_FILES = "Arr.php CharArr.php Dict.php Genewrapor.php Set.php Tree.php"
TEST_FILES = "test/ArrTest.php test/CharArrTest.php test/ConversionTest.php test/DictTest.php test/GenewraporTest.php test/ItertoolsTest.php test/SetTest.php test/Test.php test/TreeTest.php"


namespaced:
	/usr/bin/env php ./make/add_namespace.php $(PHP_ADT_FILES) php_adt
	/usr/bin/env php ./make/add_namespace.php $(TEST_FILES) php_adt test

api: namespaced
	rm -rf docs
	mkdir docs
	./apigen.phar generate --source="_php_adt,php_adt" --destination="docs" --template-theme bootstrap --title "php_adt" --tree --exclude="*init.php"
	# ./apigen.phar generate --source . --destination ./docs --template-theme bootstrap --title "php_adt" --tree --exclude="php_adt/*,test/*,api_changes.php,init.php,init.itertools.php,make/*,index.php,index.ns.php"

all: namespaced api
