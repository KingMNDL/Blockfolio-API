# Blockfolio API

## Introduction

Blockfolio API written on Symfony4. Powered by CoinGecko API.

## Installation

### **You need to have docker-compose installed.**
Run:

```bash
$ docker-compose up -d
```

After containers are up, shell into php container:

```bash
$ docker-compose exec php-fpm bash
```

Install dependencies:

```bash
$ composer install
```

Setup database and create test admin user (for testing/development purposes automated admin credentials: admin / password)

```bash
$ bash install-clean.sh
```

While running this script you will be prompted to enter JWT passphrase. Use the one defined in dot even file. (for eg blockfoliojwt)

After that You are done! You can visit Nelmio Api Doc bundle to read docs or use API: `http://localhost:8000/api/doc` 

## Tests

### Behat
1. Copy `behat.yml.dist` to `behat.yml` 
2. Run behat: `vendor/bin/behat`

