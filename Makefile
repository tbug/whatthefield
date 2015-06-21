.PHONY:
	test

test:
	vendor/bin/phpunit

dev:
	while inotifywait --exclude 'clover.xml' -e close_write,moved_to,create -r src tests; do vendor/bin/phpunit; done
