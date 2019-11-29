### Ubuntu 18.04 GNU/Linux Setup ###

#### Install Docker ####

> sudo apt install docker docker-compose

#### Update Specific Submodules ####

> cd rel-alpha-20191128/docker

> git submodule update --init

If you want Ergonode from Develop / Bleeding Edge :

> cd rel-alpha-20191128/backend-develop

> git submodule update --init

> cd rel-alpha-20191128/frontend-develop

> git submodule update --init

If you want Ergonode from Master 

> cd rel-alpha-20191128/backend-master

> git submodule update --init

> cd rel-alpha-20191128/frontend-master

> git submodule update --init

#### Adjust Settings / Configure Ports ####

> cd rel-alpha-20191128/builders

Set configurations in  env-docker, env-backend, env-frontend


Run setup.sh with specific ergonode branch you want to work on

> cd rel-alpha-20191128

> ./setup.sh master

or

> cd rel-alpha-20191128

> ./setup.sh develop


#### Run Docker in Bash Mode for Manual Setup ####

> cd rel-alpha-20191128/docker

> bin/docker setup-on

Build the backend server

> docker exec -it ergonode-php-setup bash

You are now in Backend API PHP, then run build.sh

> cd /var/www

> ./build.sh

> exit

Build the frontend server

> docker exec -it ergonode-node-setup bash

You are now in Frontend Node, then run build.sh

> cd /Application

> ./build.sh

> exit

Bring down the setup docker instance

> bin/docker setup-off

#### Launch Ergonode #####

> bin/docker serve-on

With your favorite browser, enjoy Ergonode at http://localhost


Note: The frontend serve can take a while to compile.  You may
also configue Ergonode to run on another IP aside from localhost.
