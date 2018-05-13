#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344) so we can see what is going on
set -x

cd build

git init
git remote add origin git@github.com:rectorphp/rector-prefixed.git

# to keep only diff commits
git pull origin master --rebase

git add .
git commit -m "rebuild prefixed Rector" # date?
git push origin master
