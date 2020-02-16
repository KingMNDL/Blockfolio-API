#!/bin/bash

php bin/console doc:data:drop --if-exists --force
php bin/console doc:data:crea
php bin/console doc:sch:crea

php bin/console fos:user:create admin admin@blockfolioapi.com password

mkdir config/jwt

openssl genrsa -out config/jwt/private.pem -aes256 4096

openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem