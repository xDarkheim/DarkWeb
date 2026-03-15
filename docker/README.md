# docker/

> ⚠️ Internal infrastructure — end users do not need to touch anything here.

This folder contains the Docker build internals for the `web` container.

| File | Purpose |
|---|---|
| `Dockerfile` | Builds the PHP 8.4 + Apache image |
| `entrypoint.sh` | Runs on every container start: creates dirs, sets permissions, configures cron, starts Apache |
| `.dockerignore` | Excludes unnecessary files from the build context |
