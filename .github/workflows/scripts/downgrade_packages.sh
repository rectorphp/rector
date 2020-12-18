#!/bin/bash
########################################################################
# This bash script downgrades the code to the selected PHP version
#
# Usage from within a GitHub workflow:
# .github/workflows/scripts/downgrade_packages.sh $target_php_version
# where $target_php_version is one of the following values:
# - 7.0
# - 7.1
# - 7.2
# - 7.3
# - 7.4
#
# Currently highest PHP version from which we can downgrade:
# - 8.0
#
# Eg: To downgrade to PHP 7.1, execute:
# .github/workflows/scripts/downgrade_packages.sh 7.1
########################################################################
# Variables to modify when new PHP versions are released
# ----------------------------------------------------------------------
supported_target_php_versions=(7.0 7.1 7.2 7.3 7.4)

declare -A downgrade_php_versions=( \
    ["7.0"]="8.0 7.4 7.3 7.2 7.1" \
    ["7.1"]="8.0 7.4 7.3 7.2" \
    ["7.2"]="8.0 7.4 7.3" \
    ["7.3"]="8.0 7.4" \
    ["7.4"]="8.0" \
)
declare -A downgrade_php_whynots=( \
    ["7.0"]="7.4.* 7.3.* 7.2.* 7.1.* 7.0.*" \
    ["7.1"]="7.4.* 7.3.* 7.2.* 7.1.*" \
    ["7.2"]="7.4.* 7.3.* 7.2.*" \
    ["7.3"]="7.4.* 7.3.*" \
    ["7.4"]="7.4.*" \
)
declare -A downgrade_php_rectorconfigs=( \
    ["7.0"]="to-php74 to-php73 to-php72 to-php71 to-php70" \
    ["7.1"]="to-php74 to-php73 to-php72 to-php71" \
    ["7.2"]="to-php74 to-php73 to-php72" \
    ["7.3"]="to-php74 to-php73" \
    ["7.4"]="to-php74" \
)
# Rector configs carry the previous downgrade sets starting from php80
# (eg: to-php73 has downgrade sets php80 and php74)
# so even though we're storing all the stages, only the last item must be executed
# (only to-php73, no need for to-php74) or a same downgrade will be executed more than once
# This logic is a bit redundant, but it enables to execute several config files on each package,
# eg: defining the set to execute using `--set` in the CLI (not supported anymore)
GROUP_RECTOR_CONFIGS=true

# Execute a single call to Rector for all dependencies together?
DOWNGRADE_DEPENDENCIES_TOGETHER=true
########################################################################
# Helper functions
# ----------------------------------------------------------------------
# Failure helper function (https://stackoverflow.com/a/24597941)
function fail {
    printf '%s\n' "$1" >&2  ## Send message to stderr. Exclude >&2 if you don't want it that way.
    exit "${2-1}"  ## Return a code specified by $2 or 1 by default.
}

# Print array helpers (https://stackoverflow.com/a/17841619)
function join_by { local d=$1; shift; local f=$1; shift; printf %s "$f" "${@/#/$d}"; }
########################################################################
# Silent output
# set +x

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
target_downgrade_php_rectorconfigs=($(echo ${downgrade_php_rectorconfigs[$target_php_version]} | tr " " "\n"))

packages_to_downgrade=()
rectorconfigs_to_downgrade=()
declare -A package_paths
declare -A packages_by_rectorconfig
declare -A last_rectorconfig_by_package

# Switch to production
composer install --no-dev

rootPackage=$(composer info -s -N)

numberTargetPHPVersions=${#target_downgrade_php_versions[@]}
counter=1
while [ $counter -le $numberTargetPHPVersions ]
do
    pos=$(( $counter - 1 ))
    version=${target_downgrade_php_versions[$pos]}
    whynot=${target_downgrade_php_whynots[$pos]}
    rector_config=${target_downgrade_php_rectorconfigs[$pos]}
    echo Analyzing packages to downgrade from PHP version "$version"

    # Obtain the list of packages for production that need a higher version that the input one.
    # Those must be downgraded
    PACKAGES=$(composer why-not php $whynot --no-interaction | grep -o "\S*\/\S*")
    if [ -n "$PACKAGES" ]; then
        for package in $PACKAGES
        do
            echo "Enqueueing rector_config $rector_config on package $package"
            # Composer also analyzes the root project but its path is directly the root folder
            if [ $package = "$rootPackage" ]
            then
                path=$(pwd)
            else
                # Obtain the package's path from Composer
                # Format is "package path", so extract everything after the 1st word with cut to obtain the path
                path=$(composer info $package --path | cut -d' ' -f2-)
            fi
            packages_to_downgrade+=($package)
            rectorconfigs_to_downgrade+=($rector_config)
            package_paths[$package]=$path
            packages_by_rectorconfig[$rector_config]=$(echo "${packages_by_rectorconfig[$rector_config]} ${package}")

            # If the downgrades are grouped in the same Rector config, then we must only
            # execute the last config (eg: to-php71) and not the previous ones (eg: to-php72)
            if [ -n "$GROUP_RECTOR_CONFIGS" ]; then
                last_rectorconfig_by_package[$package]=$rector_config
            fi
        done
    else
        echo No packages to downgrade
    fi
    ((counter++))
done

# Switch to dev again
composer install

# Make sure that the number of packages, paths and sets is the same
# otherwise something went wrong
numberPackages=${#packages_to_downgrade[@]}
# numberPaths=${#package_paths[@]}
# if [ ! $numberPaths -eq $numberPackages ]; then
#     fail "Number of paths ($numberPaths) and number of packages ($numberPackages) should not be different"
# fi
numberRectorConfigs=${#rectorconfigs_to_downgrade[@]}
if [ ! $numberRectorConfigs -eq $numberPackages ]; then
    fail "Number of Rector configs ($numberRectorConfigs) and number of packages ($numberPackages) should not be different"
fi

# Execute a single call to Rector for all dependencies together:
# If grouping the PHP-version downgrades together, and downgrading the packages together
if [ -n "$GROUP_RECTOR_CONFIGS" ] && [ -n "$DOWNGRADE_DEPENDENCIES_TOGETHER" ]
then
    # The config is the last element on the array
    target_rector_configs=($(echo ${downgrade_php_rectorconfigs[$target_php_version]} | tr " " "\n"))
    numberElems=${#target_rector_configs[@]}
    lastPos=$(( $numberElems - 1 ))
    rector_config=${target_rector_configs[$lastPos]}

    # Collect the list of all dependencies and their paths
    dependency_packages=()
    dependency_package_paths=()

    # Iterate all the packages, and obtain their paths
    for package_to_downgrade in "${packages_to_downgrade[@]}"; do
        path_to_downgrade=${package_paths[$package_to_downgrade]}

        if [ $package_to_downgrade != "$rootPackage" ]
        then
            #Check it's not been added yet (eg: from needing downgrade for several PHP versions)
            if [[ " ${dependency_packages[@]} " =~ " ${package_to_downgrade} " ]]; then
                continue;
            fi
            dependency_packages+=($package_to_downgrade)
            dependency_package_path=${package_paths[$package_to_downgrade]}
            dependency_package_paths+=($dependency_package_path)
        fi
    done

    # Execute the downgrade
    # Print command in output for testing
    # set -x
    # Downgrade Rector first
    path_to_downgrade=${package_paths[$rootPackage]}
    config=ci/downgrade/rector-downgrade-rector
    config="${config}-${rector_config}.php"
    echo "Running rector_config ${rector_config} for main package ${rootPackage} on path(s) ${path_to_downgrade}"
    bin/rector process $path_to_downgrade --config=$config --ansi

    #Downgrade all the dependencies then
    packages_to_downgrade=$(join_by " " ${dependency_packages[@]})
    paths_to_downgrade=$(join_by " " ${dependency_package_paths[@]})
    config=ci/downgrade/rector-downgrade-dependency
    config="${config}-${rector_config}.php"
    echo "Running rector_config ${rector_config} for dependency packages ${packages_to_downgrade} on paths ${paths_to_downgrade}"
    bin/rector process $paths_to_downgrade --config=$config --ansi
    # set +x

    # Success
    exit 0
fi

# We must downgrade packages in the strict dependency order,
# such as sebastian/diff => symfony/event-dispatcher-contracts => psr/event-dispatcher,
# or otherwise there may be PHP error from inconsistencies (such as from a modified interface)
# To do this, have a double loop to downgrade packages,
# asking if all the "ancestors" for the package have already been downgraded,
# or let it keep iterating until next loop
# Calculate all the dependents for all packages,
# including only packages to be downgraded
echo Calculating package execution order
declare -A package_dependents
counter=1
while [ $counter -le $numberPackages ]
do
    pos=$(( $counter - 1 ))
    package_to_downgrade=${packages_to_downgrade[$pos]}
    rector_config=${rectorconfigs_to_downgrade[$pos]}
    IFS=' ' read -r -a packages_to_downgrade_by_rectorconfig <<< "${packages_by_rectorconfig[$rector_config]}"

    dependents_to_downgrade=()
    # Obtain recursively the list of dependents, keep the first word only,
    # (which is the package name), and remove duplicates
    dependentsAsString=$(composer why "$package_to_downgrade" -r | cut -d' ' -f1 | awk '!a[$0]++' | tr "\n" " ")

    IFS=' ' read -r -a dependents <<< "$dependentsAsString"
    for dependent in "${dependents[@]}"; do
        # A package could provide itself in why (as when using "replaces"). Ignore them
        if [ $dependent = $package_to_downgrade ]; then
            continue
        fi
        # Only add the ones which must themselves be downgraded for that same rector config
        if [[ ! " ${packages_to_downgrade_by_rectorconfig[@]} " =~ " ${dependent} " ]]; then
            continue;
        fi
        dependents_to_downgrade+=($dependent)
    done
    # The dependents are identified per package and rector_config, because a same dependency
    # downgraded for 2 rector_config might have dependencies downgraded for one rector_config and not the other
    key="${package_to_downgrade}_${rector_config}"
    package_dependents[$key]=$(echo "${dependents_to_downgrade[@]}")
    echo "Dependents for package ${package_to_downgrade} and rector_config ${rector_config}: ${dependents_to_downgrade[@]}"
    ((counter++))
done

# echo Package dependents:
# for x in "${!package_dependents[@]}"; do printf "[%s]=%s\n" "$x" "${package_dependents[$x]}" ; done

# In case of circular dependencies (eg: package1 requires package2
# and package2 requires package1), the process will fail
# hasNonDowngradedDependents=()
declare -A circular_reference_packages_by_rector_config

echo Executing Rector to downgrade $numberDowngradedPackages packages
downgraded_packages=()
numberDowngradedPackages=1
previousNumberDowngradedPackages=1
# echo Number packages: $numberPackages
# for package in "${packages_to_downgrade[@]}"; do
#     echo Package: $package
# done
until [ $numberDowngradedPackages -gt $numberPackages ]
do
    counter=1
    while [ $counter -le $numberPackages ]
    do
        pos=$(( $counter - 1 ))
        ((counter++))
        package_to_downgrade=${packages_to_downgrade[$pos]}
        rector_config=${rectorconfigs_to_downgrade[$pos]}
        key="${package_to_downgrade}_${rector_config}"
        # Check if this package has already been downgraded on a previous iteration
        if [[ " ${downgraded_packages[@]} " =~ " ${key} " ]]; then
            continue
        fi
        # Check if all dependents have already been downgraded. Otherwise, keep iterating
        hasNonDowngradedDependent=""
        IFS=' ' read -r -a dependents <<< "${package_dependents[$key]}"
        for dependent in "${dependents[@]}"; do
            dependentKey="${dependent}_${rector_config}"
            if [[ ! " ${downgraded_packages[@]} " =~ " ${dependentKey} " ]]; then
                hasNonDowngradedDependent="true"
                # hasNonDowngradedDependents+=("${dependent}=>${package_to_downgrade}")
                circular_reference_packages_by_rector_config[${rector_config}]=$(echo "${circular_reference_packages_by_rector_config[$rector_config]} ${dependent}")
                # echo "${package_to_downgrade} has non-downgraded dependent: ${dependentKey}"
            fi
        done
        if [ -n "${hasNonDowngradedDependent}" ]; then
            continue
        fi

        # Mark this package as downgraded
        downgraded_packages+=($key)
        ((numberDowngradedPackages++))

        # If the downgrades are grouped in the same Rector config, then we must only
        # execute the last config (eg: to-php71) and not the previous ones (eg: to-php72)
        if [ -n "$GROUP_RECTOR_CONFIGS" ]; then
            # Check that this config and the last one are different
            if [ "${rector_config}" != "${last_rectorconfig_by_package[$package_to_downgrade]}" ]; then
                # Get the last rector_config for this package, replacing the current one
                rector_config=${last_rectorconfig_by_package[$package_to_downgrade]}
                # If it has already been executed, then do nothing
                key="${package_to_downgrade}_${rector_config}"
                if [[ " ${downgraded_packages[@]} " =~ " ${key} " ]]; then
                    continue
                fi
                # Mark this package as downgraded
                downgraded_packages+=($key)
                ((numberDowngradedPackages++))
            fi
        fi

        path_to_downgrade=${package_paths[$package_to_downgrade]}

        if [ $package_to_downgrade = "$rootPackage" ]
        then
            config=ci/downgrade/rector-downgrade-rector
        else
            config=ci/downgrade/rector-downgrade-dependency
        fi
        # Attach the specific rector config as a file suffix
        config="${config}-${rector_config}.php"

        echo "Running rector_config ${rector_config} for package ${package_to_downgrade} on path(s) ${path_to_downgrade}"

        # Execute the downgrade
        # Print command in output for testing
        # set -x
        bin/rector process $path_to_downgrade --config=$config --ansi
        # set +x

        # If Rector fails, already exit
        if [ "$?" -gt 0 ]; then
            fail "Rector downgrade failed on rector_config ${rector_config} for package ${package_to_downgrade}"
        fi
    done
    # If no new package was downgraded, it must be because of circular dependencies
    if [ $numberDowngradedPackages -eq $previousNumberDowngradedPackages ]
    then
        if [ ${#circular_reference_packages_by_rector_config[@]} -eq 0 ]; then
            fail "For some unknown reason, not all packages have been downgraded"
        fi

        # Resolve all involved packages all together
        echo "Resolving circular reference packages all together"
        for rector_config in "${!circular_reference_packages_by_rector_config[@]}";
        do
            circular_packages_to_downgrade=($(echo "${circular_reference_packages_by_rector_config[$rector_config]}" | tr ' ' '\n' | sort -u))
            circular_packages_to_downgrade_for_rectorconfig=()
            circular_paths_to_downgrade_for_rectorconfig=()
            for package_to_downgrade in "${circular_packages_to_downgrade[@]}";
            do
                # Mark this package as downgraded
                key="${package_to_downgrade}_${rector_config}"
                downgraded_packages+=($key)
                ((numberDowngradedPackages++))

                # If the downgrades are grouped in the same Rector config, then we must only
                # execute the last config (eg: to-php71) and not the previous ones (eg: to-php72)
                if [ -n "$GROUP_RECTOR_CONFIGS" ]; then
                    # Check that this config and the last one are different
                    if [ "${rector_config}" != "${last_rectorconfig_by_package[$package_to_downgrade]}" ]; then
                        # Get the last rector_config for this package, replacing the current one
                        package_last_rector_config=${last_rectorconfig_by_package[$package_to_downgrade]}
                        # If it has already been executed, then do nothing
                        key="${package_to_downgrade}_${package_last_rector_config}"
                        if [[ " ${downgraded_packages[@]} " =~ " ${key} " ]]; then
                            continue
                        fi
                    fi
                fi

                # This package does need downgrading (eg: it had not been already downgraded via GROUP_RECTOR_CONFIGS)
                circular_packages_to_downgrade_for_rectorconfig+=($package_to_downgrade)
                # Obtain the path
                circular_paths_to_downgrade_for_rectorconfig+=(${package_paths[$package_to_downgrade]})
            done

            # Check that possibly all packages had already been downgraded via GROUP_RECTOR_CONFIGS
            if [ ${#circular_packages_to_downgrade_for_rectorconfig[@]} -gt 0 ]; then
                paths_to_downgrade=$(join_by " " ${circular_paths_to_downgrade_for_rectorconfig[@]})
                echo "Running rector_config ${rector_config} for packages ${circular_packages_to_downgrade_for_rectorconfig[@]}"
                config="ci/downgrade/rector-downgrade-dependency-${rector_config}.php"
                bin/rector process $paths_to_downgrade --config=$config --ansi

                # If Rector fails, already exit
                if [ "$?" -gt 0 ]; then
                    fail "Rector downgrade failed on rector_config ${rector_config} for package ${package_to_downgrade}"
                fi
            else
                echo "All circular packages had already been downgraded: ${circular_packages_to_downgrade[@]}"
            fi
        done
    fi
    # hasNonDowngradedDependents=()
    circular_reference_packages_by_rector_config=()
    previousNumberDowngradedPackages=$numberDowngradedPackages
done
