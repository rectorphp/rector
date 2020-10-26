#!/bin/bash
########################################################################
# This bash script downgrades the code to the selected PHP version
#
# Usage from within a GitHub workflow:
# .github/workflows/scripts/downgrade_packages.sh $target_php_version
# where $target_php_version is one of the following values:
# - 70 (for PHP 7.0)
# - 71 (for PHP 7.1)
# - 72 (for PHP 7.2)
# - 73 (for PHP 7.3)
# - 74 (for PHP 7.4)
#
# Currently highest PHP version from which we can downgrade:
# - 8.0
#
# Eg: To downgrade to PHP 7.1, execute:
# .github/workflows/scripts/downgrade_packages.sh 71
########################################################################
# Variables to modify when new PHP versions are released

supported_target_php_versions=(70 71 72 73 74)

declare downgrade_php_versions=( \
    [70]="7.1 7.2 7.3 7.4 8.0" \
    [71]="7.2 7.3 7.4 8.0" \
)
declare downgrade_php_whynots=( \
    [70]="7.0.* 7.1.* 7.2.* 7.3.* 7.4.*" \
    [71]="7.1.* 7.2.* 7.3.* 7.4.*" \
)
declare downgrade_php_sets=( \
    [70]="downgrade-71 downgrade-72 downgrade-73 downgrade-74 downgrade-80" \
    [71]="downgrade-72 downgrade-73 downgrade-74 downgrade-80" \
)

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
if [[ ! " ${supported_target_php_versions[@]} " =~ " ${target_php_version} " ]]; then
    versions=$(join_by ", " ${supported_target_php_versions[@]})
    fail "Version $target_php_version is not supported for downgrading. Supported versions: $versions"
fi

target_downgrade_php_versions=($(echo ${downgrade_php_versions[$target_php_version]} | tr " " "\n"))
target_downgrade_php_whynots=($(echo ${downgrade_php_whynots[$target_php_version]} | tr " " "\n"))
target_downgrade_php_sets=($(echo ${downgrade_php_sets[$target_php_version]} | tr " " "\n"))

# This variable contains all paths to be downgraded, separated by space
paths_to_downgrade=()
# This variable contains which sets to run on the path, separated by space
sets_to_downgrade=()

# Switch to production
# composer install --no-dev

counter=1
while [ $counter -le ${#target_downgrade_php_versions[@]} ]
do
    pos=$(( $counter - 1 ))
    version=${target_downgrade_php_versions[$pos]}
    whynot=${target_downgrade_php_whynots[$pos]}
    set=${target_downgrade_php_sets[$pos]}
    setFile="/../../../../config/set/$set.php"
    echo Downgrading to PHP version "$version"

    # Obtain the list of packages for production that need a higher version that the input one.
    # Those must be downgraded
    PACKAGES=$(composer why-not php $whynot --no-interaction | grep -o "\S*\/\S*")
    if [ -n "$PACKAGES" ]; then
        for package in $PACKAGES
        do
            echo Analyzing package $package
            if [ $package = "rector/rector" ]
            then
                path="$(pwd)/src"
            else
                path=$(composer info $package --path | awk '{print $2;}')
            fi
            # Obtain the package's path from Composer
            # Format is "package path", so extract the 2nd word with awk to obtain the path
            paths_to_downgrade+=($path)
            sets_to_downgrade+=($setFile)
            # echo Path is $packagePath
            # # Execute the downgrade
            # ./vendor/bin/rector process $packagePath --set=$setFile --dry-run
        done
    else
        echo No packages to downgrade
    fi
    ((counter++))
done

# # Switch to dev again
# composer install

echo Number of paths to downgrade: ${#paths_to_downgrade[@]}
echo Number of sets to downgrade: ${#sets_to_downgrade[@]}

# Execute Rector on all the paths
counter=1
while [ $counter -le ${#paths_to_downgrade[@]} ]
do
    pos=$(( $counter - 1 ))
    path_to_downgrade=${paths_to_downgrade[$pos]}
    set_to_downgrade=${sets_to_downgrade[$pos]}

    echo Pos: $pos
    echo Path to downgrade: $path_to_downgrade
    echo Set to downgrade: $set_to_downgrade

    # Execute the downgrade
    # ./vendor/bin/rector process $path_to_downgrade --set=$set_to_downgrade --dry-run

    ((counter++))
done
