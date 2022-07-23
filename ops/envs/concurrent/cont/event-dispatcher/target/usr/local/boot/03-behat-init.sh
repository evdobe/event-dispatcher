#!/bin/sh
if [ -f vendor/bin/behat ]; then
    runuser -l hostuser -c "vendor/bin/behat --init"
fi