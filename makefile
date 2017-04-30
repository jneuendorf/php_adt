api: namespaced
	rm -rf docs
	mkdir docs
	./apigen.phar generate --source="_php_adt,php_adt" --destination="docs" --template-theme bootstrap --title "php_adt" --tree --exclude="*init.php"
	# ./apigen.phar generate --source . --destination ./docs --template-theme bootstrap --title "php_adt" --tree --exclude="php_adt/*,test/*,api_changes.php,init.php,init.itertools.php,make/*,index.php,index.ns.php"

all: api

install:
	composer install
