#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344) so we can see what is going on
set -x

cd build

# init non-existing .git or fetch existing one
if [ ! -d .git ]; then git init; git remote add -f origin git@github.com:rectorphp/rector-prefixed.git; else git fetch origin; fi

# to keep only diff commits

git add .
git commit -m "rebuild prefixed Rector" # date?

#git pull origin master --rebase
# https://stackoverflow.com/a/7929499/1348344
git fetch origin            # Updates origin/master
#git rebase origin/master    # Rebases current branch onto origin/master

# https://demisx.github.io/git/rebase/2015/07/02/git-rebase-keep-my-branch-changes.html
git rebase -Xours origin/master    # Rebases current branch onto origin/master, prefers our "master" in conflicts

git push origin master
