api_docs:
	rm -rf ./docs
	mkdir ./docs
	./apigen.phar generate --source . --destination ./docs --template-theme bootstrap --title "php_adt" --tree --exclude="test/*,api_changes.php,init.php"
