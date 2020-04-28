#!/bin/bash
version=$1
[[ -z "${version}" ]] && {
  echo Missing version number
  exit 1
}

docker stop bg
docker rm -v bg
docker run --rm -d --name bg -v ${PWD}/runtime:/data/runtime onlymaker/virgo-bg:${version}

docker stop php
docker rm -v php
docker run --rm -d --name php -p 80:80 -v ${PWD}/runtime:/var/www/runtime onlymaker/virgo:${version}