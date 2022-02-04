#!/bin/bash
PARENT_PATH=$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )
source $PARENT_PATH/../lib.sh

eval "$COMPOSE_COMMAND down --remove-orphans $@"
