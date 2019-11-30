#!/bin/sh
composer install
bin/genPEM.sh 1234
bin/phing build
bin/phing database:rebuild
bin/phing database:fixture
