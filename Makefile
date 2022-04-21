start:
	docker-compose up

database: database-delete database-create database-migrate

database-create:
	bin/console d:d:c

database-migrate:
	bin/console d:m:m --no-interaction

database-delete:
	bin/console d:d:d --force
