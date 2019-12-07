 #!/usr/bin/env bash

case $1 in
    'master' )
        (rm ./backend)
        (rm ./frontend)
        (rm ./docker)

        (ln -s ./backend-master ./backend)
        (ln -s ./frontend-master ./frontend)
        (ln -s ./docker-master ./docker)

        (cp -f builders/genPEM.sh backend/bin)
        (cp -f builders/env-backend backend/.env)
        (cp -f builders/build-backend.sh backend/build.sh)

        (cp -f builders/env-frontend frontend/.env)
        (cp -f builders/build-frontend.sh frontend/build.sh)

        (cp -f builders/env-docker docker/.env)
        (cp -f builders/docker-compose-setup.yml docker)
        (cp -f builders/docker docker/bin)
        ;;
    'develop' )
        (rm ./backend)
        (rm ./frontend)
        (rm ./docker)

        (ln -s ./backend-develop ./backend)
        (ln -s ./frontend-develop ./frontend)
        (ln -s ./docker-master ./docker)

        (cp -f builders/genPEM.sh backend/bin)
        (cp -f builders/env-backend backend/.env)
        (cp -f builders/build-backend.sh backend/build.sh)

        (cp -f builders/env-frontend frontend/.env)
        (cp -f builders/build-frontend.sh frontend/build.sh)

        (cp -f builders/env-docker docker/.env)
        (cp -f builders/docker-compose-setup.yml docker)
        (cp -f builders/docker docker/bin)
        ;;
    *)
        echo 'Usage: setup.sh <branch>';
        echo '';
        echo 'branch options:';
        printf "master - setup for master branch";
        printf "develop - setup for develop branch";
        ;;
esac
