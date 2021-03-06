#!/bin/sh

# Directory containing this script
TESTS_DIR=$(dirname $(readlink -f $0))
cd $TESTS_DIR

# Did any test fail?
HAS_FAILURES=0

for TEST in `find . -mindepth 1 -maxdepth 1 -type d -printf '%f\n'`; do
    echo "-----> Running test $TEST <-----"
    cd $TESTS_DIR/$TEST
    $TESTS_DIR/../../bin/rector process --dry-run --clear-cache
    if [ $? -eq 0 ]; then 
        echo "-----> Result: OK <-----"
    else
        echo "-----> Result: FAILED <-----"
        HAS_FAILURES=1
    fi
done

exit $HAS_FAILURES
