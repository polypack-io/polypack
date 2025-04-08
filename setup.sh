#!/bin/sh
# Check if docker is installed
if ! command -v docker 2>&1 >/dev/null
then
  echo "Docker could not be found. Install Docker using with help from their documentation https://docs.docker.com/engine/install/."
  exit 1
fi

# Ask for an email address to provide to Let's Encrypt
read -p "What email address should be provided to Let's Encrypt when provisioning SSL certificates? " email_address
echo $email_address

http_port=80
https_port=443

# Ask if HTTP and HTTPS are on default ports
read -p "Do you wish to use the standard HTTP and HTTPS ports (yes/no)? " use_standard_web_ports
echo $use_standard_web_ports

if [ "$use_standard_web_ports" != 'yes' ]; then
  read -p "Which port do you want to use for HTTP traffic (80 by default)? " http_port
  read -p "Which port do you want to use for HTTPS traffic (443 by default)? " https_port
fi

# Ask for the full domain to be used to access Polypack
read -p "What is the domain you wish to use to access Polypack? " domain_polypack
echo $domain_polypack

# Ask for the full domain to be used to access Minio Object Storage API
read -p "What is the domain you wish to use to access the Minio Object Storage API? " domain_s3_api
echo $domain_s3_api

# Ask for the full domain to be used to access Minio Object Storage API
read -p "What is the domain you wish to use to access the Minio Object Storage API? " domain_s3_console
echo $domain_s3_console

# If .env doesn't exist, copy .env.example
if [ ! -f ./.env ]; then
  echo ".env does not exist, copying .env.example"
  cp .env.example .env
fi

sed -i "s/DOCKER_EXPOSED_HTTP_PORT.*/DOCKER_EXPOSED_HTTP_PORT=$http_port/" .env
sed -i "s/DOCKER_EXPOSED_HTTPS_PORT.*/DOCKER_EXPOSED_HTTPS_PORT=$https_port/" .env
sed -i "s/DOCKER_SSL_ACME_EMAIL.*/DOCKER_SSL_ACME_EMAIL=$email_address/" .env
sed -i "s/DOCKER_DOMAIN_WEB.*/DOCKER_DOMAIN_WEB=$domain_polypack/" .env
sed -i "s/DOCKER_DOMAIN_MINIO_API.*/DOCKER_DOMAIN_MINIO_API=$domain_s3_api/" .env
sed -i "s/DOCKER_DOMAIN_MINIO_CONSOLE.*/DOCKER_DOMAIN_MINIO_CONSOLE=$domain_s3_console/" .env

docker compose -f docker-compose.production.yml up -d --force-recreate --build

read -p "Do you want to seed the database with an initial login (yes/no)? " seed_db
if [ "$seed_db" == 'yes' ]; then
  docker compose -f docker-compose.production.yml exec -it polypack-web php artisan db:seed
fi