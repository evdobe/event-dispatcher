#!/bin/bash
source $PARENT_PATH/../config.sh
source $PARENT_PATH/../paths.sh
COMPOSE_COMMAND="HOST_UID=\"$(id -u)\" HOST_GID=\"$(id -g)\" docker-compose --env-file $COMPOSE_PATH/.env -f $COMPOSE_PATH/docker-compose.yaml"

