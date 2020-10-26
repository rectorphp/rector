#!/bin/bash
# This bash script downgrades the PHP versions for packages
# Usage from within a GitHub workflow:
# To downgrade from PHP 7.4 to 7.1, execute:
# .github/workflows/scripts/downgrade_packages.sh 7.3 7.2 7.1

# This variable contains all paths to be downgraded, separated by space
PATHS_TO_DOWNGRADE=""
# This variable contains which sets to run on the path, separated by space
SETS_TO_RUN_ON_PATH=""

# Switch to production
composer install --no-dev

for version in "$@"
do
    echo Downgrading to PHP version "$version"
    # Obtain the list of packages for production that need a higher version that the input one.
    # Those must be downgraded
    PACKAGES=$(composer why-not php "$version.*" --no-interaction | grep -o "\S*\/\S*")
    if [ -n "$PACKAGES" ]; then
        for package in $PACKAGES
        do
            echo Analyzing package $package
            # Obtain the package's path from Composer
            # Format is "package path", so extract the 2nd word with awk to obtain the path
            PATHS_TO_DOWNGRADE="$PATHS_TO_DOWNGRADE $(composer info $package --path | awk '{print $2;}')"
            # echo Path is $packagePath
            # # Execute the downgrade
            # ./vendor/bin/rector process $packagePath --set=downgrade-74 --dry-run
        done
    else
        echo No packages to downgrade
    fi
done

# Switch to dev again
composer install

# Execute Rector on all the paths
for package in $PATHS_TO_DOWNGRADE
do
    echo Downgrading package $package
    # Execute the downgrade
    ./vendor/bin/rector process $packagePath --set=downgrade-74 --dry-run
done
