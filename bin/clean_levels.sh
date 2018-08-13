#!/usr/bin/env bash
# trailing whitespaces
sed -i -E 's#\s+$##g' config/level/*/*.yml docs/*.md packages/*/README.md README.md
