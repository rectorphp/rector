#!/usr/bin/env bash
# trailing whitespaces
sed -i '' -E 's#\\s+$##g' config/set/*/*.yaml docs/*.md README.md
