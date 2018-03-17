#!/usr/bin/env bash
# trailing whitespaces
sed -i -E 's/\s+$//g' src/config/levels/*/*.yml docs/*.md README.md
