#!/usr/bin/env bash

# print each statement before run (https://stackoverflow.com/a/9966150/1348344)
set -x

cd build

git init
git remote add origin git@github.com:rectorphp/rector-prefixed.git
git add .
git commit -m "rebuild prefixed Rector"
git push origin master -f

# clone local repository to /.subsplit directory, so anyone can work on master
#git subsplit init .git

# get last tag
#LAST_TAG="$(git tag -l  --sort=committerdate | tail -n1)"

# get current branch
#HEADS="$(git branch | grep \* | cut -d ' ' -f2)"

# split last tag and current branch
# from <directory>:git@github.com:Symplify/BetterPhpDocParser.git
# to build:git@github.com:<Github/Repository>.git
#git subsplit publish --heads=$HEADS --tags=$LAST_TAG build:git@github.com:rectorphp/rector-prefixed.git --work-dir=.

#rm -rf .subsplit

# inspired by https://github.com/Symplify/Symplify/blob/master/bin/subtree-split-master-and-last-tag.sh
