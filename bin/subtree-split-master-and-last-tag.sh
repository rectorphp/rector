#!/usr/bin/env bash
git subsplit init git@github.com:rectorphp/rector.git

LAST_TAG=$(git tag -l  --sort=committerdate | tail -n1);

git subsplit publish --heads="master" --tags=$LAST_TAG packages/NodeTypeResolver:git@github.com:rectorphp/node-type-resolver.git

rm -rf .subsplit/

# inspired by laravel: https://github.com/laravel/framework/blob/5.4/build/illuminate-split-full.sh
# they use SensioLabs now though: https://github.com/laravel/framework/pull/17048#issuecomment-269915319
