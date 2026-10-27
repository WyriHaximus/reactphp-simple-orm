#extra-services-up: #### Start Post for integration tests ##
#	$(MAKE) setup-local-postgres
#
#extra-services-down: #### Stop RabbitMQ ##
#	$(MAKE) teardown-local-postgres

setup-local-postgres: #### Start RabbitMQ for integration tests ##
	docker network rm wyrihaximus-reactphp-simple-orm-postgres-test || true
	docker network create wyrihaximus-reactphp-simple-orm-postgres-test
	docker run -d --network wyrihaximus-reactphp-simple-orm-postgres-test --name wyrihaximus-reactphp-simple-orm-postgres-test --rm -e POSTGRES_PASSWORD=postgres -p 5432:5432 --health-cmd="pg_isready" --health-interval=10s --health-timeout=5s --health-retries=5 postgres:16.2

teardown-local-postgres: #### Stop RabbitMQ ##
	docker stop wyrihaximus-reactphp-simple-orm-postgres-test
	docker network rm wyrihaximus-reactphp-simple-orm-postgres-test

migrations-generate: ## Build dashboard into public/dashboard ####
	docker run -i --network=wyrihaximus-reactphp-simple-orm-postgres-test -v "`pwd`/etc/db:/var/atlas" -w "/var/atlas" -v "/var/run/docker.sock:/var/run/docker.sock" arigaio/atlas migrate diff initial --to file:///var/atlas/schema.sql --dev-url "postgres://postgres:postgres@wyrihaximus-reactphp-simple-orm-postgres-test:5432/postgres?search_path=public&sslmode=disable"

migrations-apply: ## Build dashboard into public/dashboard ####
	docker run -i --network=wyrihaximus-reactphp-simple-orm-postgres-test -v "`pwd`/etc/db:/var/atlas" -w "/var/atlas" -v "/var/run/docker.sock:/var/run/docker.sock" arigaio/atlas migrate apply --url "postgres://postgres:postgres@wyrihaximus-reactphp-simple-orm-postgres-test:5432/postgres?search_path=public&sslmode=disable"
