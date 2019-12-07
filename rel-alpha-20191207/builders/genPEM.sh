#!/bin/sh
openssl genrsa -passout pass:{1} -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -passin pass:{1} -out config/jwt/public.pem