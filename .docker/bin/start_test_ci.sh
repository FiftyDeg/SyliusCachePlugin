#!/bin/bash

export HOST_UID=$(id -u)
export HOST_GID=$(id -g)

docker-compose -f docker-compose.yml -f docker-compose.test.yml \
    --env-file ./.env.test up \
    --remove-orphans \
    --force-recreate \
    --build \
    --abort-on-container-exit \
    --exit-code-from php
