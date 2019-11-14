#!/usr/bin/env bash
# trailing whitespaces

#see https://stackoverflow.com/questions/2320564/sed-i-command-for-in-place-editing-to-work-with-both-gnu-sed-and-bsd-osx
if sed --version >/dev/null 2>&1; then
  #GNU sed (common to linux)
  sed -i -E 's#\\s+$##g' config/set/*/*.yaml docs/*.md README.md;
else
  #BSD sed (common to osX)
  sed -i '' -E 's#\\s+$##g' config/set/*/*.yaml docs/*.md README.md
fi
