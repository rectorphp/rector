#!/bin/bash
########################################################################
# This bash script downgrades the code to the selected PHP version
#
# Usage from within a GitHub workflow:
# .github/workflows/scripts/downgrade_packages.sh $target_php_version
# where $target_php_version is one of the following values:
# - 7.1
# - 7.2
# - 7.3
# - 7.4
#
# Eg: To downgrade to PHP 7.1, execute:
# .github/workflows/scripts/downgrade_packages.sh 7.1
########################################################################
# Variables to modify when new PHP versions are released

supported_target_php_versions=(7.1 7.2 7.3 7.4)

########################################################################
# Helper functions
# Failure helper function (https://stackoverflow.com/a/24597941)
function fail {
    printf '%s\n' "$1" >&2  ## Send message to stderr. Exclude >&2 if you don't want it that way.
    exit "${2-1}"  ## Return a code specified by $2 or 1 by default.
}

# Print array helpers (https://stackoverflow.com/a/17841619)
function join_by { local d=$1; shift; local f=$1; shift; printf %s "$f" "${@/#/$d}"; }
########################################################################

target_php_version=$1
if [ -z "$target_php_version" ]; then
    versions=$(join_by ", " ${supported_target_php_versions[@]})
    fail "Please provide to which PHP version to downgrade to ($versions) as first argument to the bash script"
fi

# Check the version is supported
if [[ ! " ${supported_target_php_versions[@]} " =~ " ${version} " ]]; then
    versions=$(join_by ", " ${supported_target_php_versions[@]})
    fail "Version $target_php_version is not supported for downgrading. Supported versions: $versions"
fi

# This variable contains all paths to be downgraded, separated by space
PATHS_TO_DOWNGRADE=""
# This variable contains which sets to run on the path, separated by space
SETS_TO_RUN_ON_PATH=""

# Switch to production
composer install --no-dev

for version in "$versions"
do
    echo Downgrading to PHP version "$target_php_version"
    # Obtain the list of packages for production that need a higher version that the input one.
    # Those must be downgraded
    PACKAGES=$(composer why-not php "$target_php_version.*" --no-interaction | grep -o "\S*\/\S*")
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
