#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344) so we can see what is going on
set -x

cd build

# init non-existing .git or fetch existing one
if [ ! -d .git ]; then
    git init
    git remote add -f origin git@github.com:rectorphp/rector-prefixed.git
else
    git fetch origin
fi

git add .
git commit -m "rebuild prefixed Rector"
# needs to be force pushed to delete old files
git push origin master -f
