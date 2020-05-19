#!/bin/bash
version=$1
[[ -z "${version}" ]] && {
  echo Missing version number
  exit 1
}
docker rmi onlymaker/virgo-bg:${version}
docker build . -t onlymaker/virgo-bg:${version} -f cli/Dockerfile
docker rmi onlymaker/virgo:${version}
docker build . -t onlymaker/virgo:$version