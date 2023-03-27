#!/bin/bash

docker-compose exec app make init && \
docker exec -it sylius-cache-plugin_app_1 sh

