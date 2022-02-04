#!/bin/bash
set -e
if [[ -z "$HOST_UID" ]]; then
    echo "ERROR: please set HOST_UID" >&2
    exit 1
fi
if [[ -z "$HOST_GID" ]]; then
    echo "ERROR: please set HOST_GID" >&2
    exit 1
fi
if grep -q '^hostuser:' /etc/passwd; then
    userdel hostuser
fi
if grep -q "hostgroup" /etc/group; then
    groupdel hostgroup
fi
addgroup --gid "$HOST_GID" hostgroup
adduser --uid "$HOST_UID" --gid "$HOST_GID" --gecos "" --home /var/www --disabled-password hostuser
