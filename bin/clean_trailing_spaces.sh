#!/usr/bin/env bash
# trailing whitespaces
sed -i -E 's#\s+$##g' config/level/*/*.yaml docs/*.md README.md
