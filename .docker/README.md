# :computer: Docker container for Sylius Plugin development

## :warning: Important notes:
When changing "system level" code (e.g. cron, static analysis, CI/CD docker image) please, remember that we must keep also docker images and shell scripts up to date.

## :notebook: Intro
This container has been developed and tested on Ubuntu 22.04.
I bundles the following images:
- nginx _latest_
- php-fpm _v8.1.12_
- mysql _v8.0_
- node _v16.18.1_

## :scroll: Commandline
There's some useful shell scripts inside the `<project_root>/.docker/bin` folder:
- `cd <project_root>/docker && ./bin/start_dev.sh` - starts the container in dev mode
- `cd <project_root>/docker && ./bin/start_test.sh` - starts the container in test mode


## :ghost: Hosting

### Domains
Once up and running, you'll be able to access syliusplugin from your browser.
Main resolved domains are based on nginx configs and sylius config and should look like those listed below:
- https://syliusplugin.local/

### Hosts file
Remember that, for each domain, you should update your `/etc/hosts` file. For the domains listed above, it should look like:
```sh
# Debian default settings (do not edit them)
127.0.0.1	localhost
127.0.1.1	your-machine-name

# Letshelter settings
127.0.0.1	syliusplugin.local
```

### Add new hosts
If you need to create a new host, you just need to:
- stop the container
- crate a new nginx config in `<project_root>/docker/nginx/volumes/etc/nginx/conf.d/`
- add a new entry in your `/etc/hosts` file
- start the container
- go to `https://syliusplugin.local/admin/`, login and create a new channel


## :bug: Debugging

### PHP
For VSCode, you need to run the `Listen for XDebug on Docker` configuration (defined in `<project_root>/.vscode/launch.json`)
