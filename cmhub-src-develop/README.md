## Channel Manager Hub

[![pipeline status](http://gitlab.production.smartbox.com/web/cmhub-src/badges/master/pipeline.svg)](http://gitlab.production.smartbox.com/web/cmhub-src/commits/master)

[![coverage report](http://gitlab.production.smartbox.com/web/cmhub-src/badges/master/coverage.svg)](http://gitlab.production.smartbox.com/web/cmhub-src/commits/master)

## Reports

#### Coverage Report:
* [Coverage](http://web.gitlab.production.smartbox.com/cmhub-src/coverage/)

#### Performance Reports:
* [CMHUB Performance - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/DEVINT/cmhub/loadtests/)
* [EAI Performance - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/DEVINT/EAI/loadtests/)
* [iResa Performance - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/DEVINT/iResa/loadtests/)
* [CMHUB Performance - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/PREPROD/cmhub/loadtests/)
* [EAI Performance - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/PREPROD/EAI/loadtests/)
* [iResa Performance - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/PREPROD/iResa/loadtests/)

#### API Reports:
* [CMHUB Report - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/cmhub/DEVINT/newman-report.html)
* [CMHUB Report - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/cmhub/PREPROD/newman-report.html)
* [iResa Report - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/iResa/DEVINT/newman-report.html)
* [iResa Report - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/iResa/PREPROD/newman-report.html)
* [EAI Report - DEVINT](http://web.gitlab.production.smartbox.com/cmhub-src/EAI/DEVINT/newman-report.html)
* [EAI Report - PREPROD](http://web.gitlab.production.smartbox.com/cmhub-src/EAI/PREPROD/newman-report.html)

## Development Environment Installation

#### Build and start container ####
`
docker-compose build --build-arg SSH_KEY="$(cat ~/.ssh/id_rsa)" && docker-compose up -d
`

#### Create database ####
`
docker exec -it cmhub_php bin/console doctrine:database:create
`
#### Run migrations ####
`
docker exec -it cmhub_php bin/console doctrine:migrations:migrate
`
#### Populate fixtures ####
`
docker exec -it cmhub_php bin/console hautelook:fixtures:load
`
#### Create Kibana and ElasticSearch local environment ####

##### Run all containers
`
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
`
###### If you have memory problems, you need to increase your local virtual memory

`
sudo sysctl -w vm.max_map_count=262144
` 

#### Access app ####
http://localhost/admin

`Username : adminuser, 
Password : adminuser`
## Access the container
`
docker exec -it cmhub_php /bin/sh
`
## Running code sniffers
#### PHP CS ####
`
docker exec -it cmhub_php bin/phpcs --standard=phpcs_ruleset.xml --ignore=tests src
`
#### PHPMD ####
`
docker exec -it cmhub_php bin/phpmd src text phpmd_ruleset.xml --exclude src/Tests/,src/DataFixtures,src/Entity,src/Migrations,src/Model/Factory
`
#### PHPCPD ####
`
docker exec -it cmhub_php bin/phpcpd --progress src --exclude=Tests --exclude=Entity
`
#### PHPSTAN ####
`
docker exec -it cmhub_php bin/phpstan analyse -l 2 -c phpstan.neon src
`
## Running tests
#### PHPSPEC (UNIT TESTS) ####
`
docker exec -it cmhub_php bin/phpspec run
`
>to see coverage in our local container we need to install the phpdbg into the cmhub_php
`
docker exec -it cmhub_php apk add php7-phpdbg@cast
`
>Then run `docker exec -it cmhub_php phpdbg7 -qrr bin/phpspec run`

#### PHPUNIT (FUNCTIONAL TESTS) ####
`
docker exec -it cmhub_php bin/phpunit
`
#### Contract Tests ####
`php ./bin/phpunit -vvv --config=./pact/phpunit.pact.xml`
* Pact broker : http://10.10.0.234:32584


#### API Tests(LOCAL Environment) ####
* Install newman : `npm install -g newman`
* Run tests : `newman run tests/PostmanScripts/CMHUB_APIs-Automation.postman_collection.json -e tests/PostmanScripts/LOCAL.postman_environment.json`
* To run only specific collection use `--folder` option
* To add htmlextra report use `-r htmlextra --reporter-htmlextra-export=<folder directory to store html report>` option

#### Performance Tests ####
* Download Jmeter docker image :`docker pull justb4/jmeter`
* Create directory to store Performance Report: `mkdir -p ${CI_PROJECT_DIR}/performance/Reports/PREPROD/EAI/loadtests/` 
* Command to run CMHUB tests in DEVINT Environment : `jmeter -n -t ${CI_PROJECT_DIR}/performance/EAILoadTesting_Preprod.jmx -Jthreads=10 -Jrampup=10 -Jloopcount=10 -f -l ${CI_PROJECT_DIR}/performance/Reports/EAILoadtestingPreprod-results.jtl -e -o ${CI_PROJECT_DIR}/performance/Reports/PREPROD/EAI/loadtests/`
