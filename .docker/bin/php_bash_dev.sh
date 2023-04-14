#!/bin/bash

export $(grep -v '^#' .env.dev | xargs)

docker exec -it "${COMPOSE_PROJECT_NAME}_php" bash
