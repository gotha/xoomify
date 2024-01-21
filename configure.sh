#!/usr/bin/env bash

generateSecret() {
  openssl rand -hex 32
}

generateUsername() {
  openssl rand -hex 8
}

if [ -f ".env" ]; then
  echo ".env file already exists; Delete the file and re-run the command to generate new passwords"
  exit 1
fi


APP_SECRET=$(generateSecret)
APP_ENV="prod"
APP_DEBUG=0
APP_VERSION="0.2.0"
POSTGRES_USER=$(generateUsername)
POSTGRES_PASSWORD=$(generateSecret)
POSTGRES_DB="xoomify"
DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@db:5432/${POSTGRES_DB}?serverVersion=15&charset=utf8"
read -p "SPOTIFY_CLIENT_ID:" SPOTIFY_CLIENT_ID
read -p "SPOTIFY_CLIENT_SECRET:" SPOTIFY_CLIENT_SECRET
read -p "SPOTIFY_REDIRECT_URI:" SPOTIFY_REDIRECT_URI

{
  echo "APP_SECRET=${APP_SECRET}"
  echo "APP_ENV=${APP_ENV}"
  echo "APP_DEBUG=${APP_DEBUG}"
  echo "APP_VERSION=${APP_VERSION}"
  echo "POSTGRES_USER=${POSTGRES_USER}"
  echo "POSTGRES_PASSWORD=${POSTGRES_PASSWORD}"
  echo "POSTGRES_DB=${POSTGRES_DB}"
  echo "DATABASE_URL=${DATABASE_URL}"
  echo "SPOTIFY_CLIENT_ID=${SPOTIFY_CLIENT_ID}"
  echo "SPOTIFY_CLIENT_SECRET=${SPOTIFY_CLIENT_SECRET}"
  echo "SPOTIFY_REDIRECT_URI=${SPOTIFY_REDIRECT_URI}"
} >.env
