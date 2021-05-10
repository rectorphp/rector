#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$DIR"

if [[ ! -d "$ROOT_DIR/tools/abnfgen" ]]; then
	rm -rf "$ROOT_DIR/temp/abnfgen"
	mkdir -p "$ROOT_DIR/temp/abnfgen"

	tar xf "$ROOT_DIR/tests/abnfgen-0.20.tar.gz" \
		--directory "$ROOT_DIR/temp/abnfgen" \
		--strip-components 1

	cd "$ROOT_DIR/temp/abnfgen"
	./configure
	make

	mkdir -p "$ROOT_DIR/tools/abnfgen"
	mv abnfgen "$ROOT_DIR/tools/abnfgen"
	rm -rf "$ROOT_DIR/temp/abnfgen" "$ROOT_DIR/temp/abnfgen.tar.gz"
fi
