#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344) so we can see what is going on
set -x

cd build

# init non-existing .git or fetch existing one
if [ ! -d .git ]; then
    git init
    # travis needs token to push
    if [ $TRAVIS == true ]; then
        git remote add -f origin https://$GITHUB_TOKEN@github.com/rectorphp/rector-prefixed.git
    else
        git remote add -f origin git@github.com:rectorphp/rector-prefixed.git
    fi

else
    git fetch origin
fi

git add .
git commit -m "rebuild prefixed Rector"
# needs to be force pushed to delete old files
git push origin master -f
