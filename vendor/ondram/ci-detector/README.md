# CI Detector

[![Latest Stable Version](https://img.shields.io/packagist/v/ondram/ci-detector.svg?style=flat-square)](https://packagist.org/packages/ondram/ci-detector)
[![Packagist Downloads](https://img.shields.io/packagist/dt/OndraM/ci-detector?style=flat-square)](https://packagist.org/packages/ondram/ci-detector)
[![Coverage Status](https://img.shields.io/coveralls/OndraM/ci-detector/main.svg?style=flat-square)](https://coveralls.io/r/OndraM/ci-detector)
[![GitHub Actions Build Status](https://img.shields.io/github/workflow/status/OndraM/ci-detector/Tests%20and%20linting?style=flat-square&label=GitHub%20Actions%20build)](https://github.com/OndraM/ci-detector/actions)
[![Travis Build Status](https://img.shields.io/travis/com/OndraM/ci-detector.svg?style=flat-square&label=Travis%20build)](https://travis-ci.com/OndraM/ci-detector)
[![AppVeyor Build Status](https://img.shields.io/appveyor/ci/OndraM/ci-detector.svg?style=flat-square&label=AppVeyor%20build)](https://ci.appveyor.com/project/OndraM/ci-detector)

PHP library to detect continuous integration environment and to provide a unified interface to read the build information.

## Why

This library is useful if you need to detect whether some CLI script/tool is running in an automated environment (on a CI server).
Based on that, your script may behave differently (for example hide some information which relevant only for a real person - like status bar, etc.).

Plus, you may want to detect some information about the current build: build ID, git commit, branch etc.
For example, if you'd like to record these values to log, publish them to Slack, etc.

## How

The detection is based on environment variables injected to the build environment by each CI server.
However, these variables are named differently in each CI. This library contains adapters for many supported
CI servers to handle these differences, so you can make your scripts (and especially CLI tools) portable to multiple
build environments.

## Supported continuous integration servers

These CI servers are currently recognized:

 - [AppVeyor](https://www.appveyor.com/)
 - [AWS CodeBuild](https://aws.amazon.com/codebuild/)
 - [Azure DevOps Pipelines](https://azure.microsoft.com/en-us/services/devops/pipelines/)
 - [Bamboo](https://www.atlassian.com/software/bamboo)
 - [Bitbucket Pipelines](https://bitbucket.org/product/features/pipelines)
 - [Buddy](https://buddy.works/)
 - [CircleCI](https://circleci.com/)
 - [Codeship](https://codeship.com/)
 - [continuousphp](https://continuousphp.com/)
 - [drone](https://drone.io/)
 - [GitHub Actions](https://github.com/features/actions)
 - [GitLab](https://about.gitlab.com/gitlab-ci/)
 - [Jenkins](https://www.jenkins.io/)
 - [SourceHut](https://sourcehut.org/)
 - [TeamCity](https://www.jetbrains.com/teamcity/)
 - [Travis CI](https://travis-ci.org/)
 - [Wercker](https://devcenter.wercker.com/)

If your favorite CI server is missing, feel free to send a pull-request!

## Installation

Install using [Composer](https://getcomposer.org/):

```sh
$ composer require ondram/ci-detector
```

Ci-detector requires PHP 7.1+, but if you need compatibility with PHP <7.1, you can still use old ci-detector version 2.x.

## Example usage

```php
<?php

$ciDetector = new \OndraM\CiDetector\CiDetector();

if ($ciDetector->isCiDetected()) {  // Make sure we are on CI environment
    echo 'You are running this script on CI server!';
    $ci = $ciDetector->detect();    // Returns class implementing CiInterface or throws CiNotDetectedException

    // Example output when run inside GitHub Actions build:
    echo $ci->getCiName(); // "GitHub Actions"
    echo $ci->getBuildNumber(); // "33"
    echo $ci->getBranch(); // "feature/foo-bar" or empty string if not detected

    // Conditional code for pull request:
    if ($ci->isPullRequest()->yes()) {
        echo 'This is pull request. The target branch is: ';
        echo $ci->getTargetBranch(); // "main"
    }

    // Conditional code for specific CI server:
    if ($ci->getCiName() === OndraM\CiDetector\CiDetector::CI_GITHUB_ACTIONS) {
        echo 'This is being built on GitHub Actions';
    }

    // Describe all detected values in human-readable form:
    print_r($ci->describe());
    // Array
    // (
    //     [ci-name] => GitHub Actions
    //     [build-number] => 33
    //     [build-url] => https://github.com/OndraM/ci-detector/commit/abcd/checks
    //     [commit] => fad3f7bdbf3515d1e9285b8aa80feeff74507bde
    //     [branch] => feature/foo-bar
    //     [target-branch] => main
    //     [repository-name] => OndraM/ci-detector
    //     [repository-url] => https://github.com/OndraM/ci-detector
    //     [is-pull-request] => Yes
    // )

} else {
    echo 'This script is not run on CI server';
}
```

## API methods reference

Available methods of `CiInterface` instance (returned from `$ciDetector->detect()`):

| Method                | Example value                                              | Description                                                                                                                                                                                                                                                                                                                                               |
|-----------------------|------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `getCiName()`         | `GitHub Actions`                                           | Name of the CI server.<br>The value is one of `CiDetector::CI_*` constants.                                                                                                                                                                                                                                                                                 |
| `getBuildNumber()`    | `33`                                                       | Get number of this concrete build.<br>Build number is usually human-readable increasing number sequence. It should increase each time this particular job was run on the CI server. Most CIs use simple numbering sequence like: 1, 2, 3... However, some CIs do not provide this simple human-readable value and rather use for example alphanumeric hash. |
| `getBuildUrl()`       | `https://github.com/OndraM/ci-detector/commit/abcd/checks`<br>or empty string | Get URL where this build can be found and viewed or empty string if it cannot be determined.                                                                                                                                                                                                                                                              |
| `getCommit()`         | `b9173d94(...)`                                            | Get hash of the git (or other VCS) commit being built.                                                                                                                                                                                                                                                                                                    |
| `getBranch()`         | `my-feature`<br>or empty string                            | Get name of the git (or other VCS) branch which is being built or empty string if it cannot be determined.<br>Use `getTargetBranch()` to get name of the branch where this branch is targeted.                                                                                                                                                              |
| `getTargetBranch()`   | `main`<br>or empty string                                  | Get name of the target branch of a pull request or empty string if it cannot be determined.<br>This is the base branch to which the pull request is targeted.                                                                                                                                                                                               |
| `getRepositoryName()` | `OndraM/ci-detector`<br>or empty string                    | Get name of the git (or other VCS) repository which is being built or empty string if it cannot be determined.<br>This is usually in form "user/repository", for example `OndraM/ci-detector`.                                                                                                                                                              |
| `getRepositoryUrl()`  | `https://github.com/OndraM/ci-detector`<br>or empty string | Get URL where the repository which is being built can be found or empty string if it cannot be determined.<br>This is either HTTP URL like `https://github.com/OndraM/ci-detector` but may be a git ssh url like `ssh://git@bitbucket.org/OndraM/ci-detector`                                                                                               |
| `isPullRequest()`     | `TrinaryLogic` instance                                    | Detect whether current build is from a pull/merge request.<br>Returned `TrinaryLogic` object's value will be true if the current build is from a pull/merge request, false if it not, and maybe if we can't determine it (see below for what CI supports PR detection).<br>Use condition like `if ($ci->isPullRequest()->yes()) { /*...*/ }` to use the value. |
| `describe()`          | `[...]`<br>(array of values)                               | Return key-value map of all detected properties in human-readable form.                                                                                                                                                                                                                                                                                   |

## Supported properties of each CI server

Most CI servers support (✔) detection of all information. However some don't expose
necessary environment variables, thus reading some information may be unsupported (❌).

| CI server                                              | Constant of `CiDetector` | `is​PullRequest` | `get​Branch` | `getTargetBranch` | `get​Repository​Name` | `get​Repository​Url` | `get​Build​Url` |
|--------------------------------------------------------|----------------------|---|---|---|---|---|---|
| [AppVeyor](https://www.appveyor.com/)                  | `CI_APPVEYOR`        | ✔ | ✔ | ✔ | ✔ | ❌ | ✔ |
| [AWS CodeBuild](https://aws.amazon.com/codebuild/)     | `CI_AWS_CODEBUILD`   | ✔ | ✔ | ❌ | ❌ | ✔ | ✔ |
| [Azure Pipelines](https://azure.microsoft.com/en-us/services/devops/pipelines/) | `CI_AZURE_PIPELINES` | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [Bamboo](https://www.atlassian.com/software/bamboo)    | `CI_BAMBOO`          | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [Bitbucket Pipelines](https://bitbucket.org/product/features/pipelines)| `CI_BITBUCKET_PIPELINES` | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [Buddy](https://buddy.works/)                          | `CI_BUDDY`           | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [CircleCI](https://circleci.com/)                      | `CI_CIRCLE`          | ✔ | ✔ | ❌ | ✔ | ✔ | ✔ |
| [Codeship](https://codeship.com/)                      | `CI_CODESHIP`        | ✔ | ✔ | ❌ | ✔ | ❌ | ✔ |
| [continuousphp](https://continuousphp.com/)            | `CI_CONTINUOUSPHP`   | ✔ | ✔ | ❌ | ❌ | ✔ | ✔ |
| [drone](https://drone.io/)                             | `CI_DRONE`           | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [GitHub Actions](https://github.com/features/actions)  | `CI_GITHUB_ACTIONS`  | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [GitLab](https://about.gitlab.com/gitlab-ci/)          | `CI_GITLAB`          | ✔ | ✔ | ✔ | ✔ | ✔ | ✔ |
| [Jenkins](https://www.jenkins.io/)                     | `CI_JENKINS`         | ❌ | ✔ | ❌ | ❌ | ✔ | ✔ |
| [SourceHut](https://sourcehut.org/)                    | `CI_SOURCEHUT`       | ✔ | ❌ | ❌ | ❌ | ❌ | ✔ |
| [TeamCity](https://www.jetbrains.com/teamcity/)        | `CI_TEAMCITY`        | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| [Travis CI](https://travis-ci.org/)                    | `CI_TRAVIS`          | ✔ | ✔ | ✔ | ✔ | ❌ | ✔ |
| [Wercker](https://devcenter.wercker.com/)              | `CI_WERCKER`         | ❌ | ✔ | ❌ | ✔ | ❌ | ✔ |

## Testing

Check codestyle, static analysis and run unit-tests:

```sh
composer all
```

To automatically fix codestyle violations run:

```sh
composer fix
```

## Standalone CLI version
If you want to use CI Detector as a standalone CLI command (ie. without using inside code of PHP project),
see [ci-detector-standalone](https://github.com/OndraM/ci-detector-standalone) repository, where you can
download CI Detector as a standalone PHAR file with simple command line interface.

## Changelog
For latest changes see [CHANGELOG.md](CHANGELOG.md) file. This project follows [Semantic Versioning](http://semver.org/).

## Similar libraries for other languages

Similar "CI Info" libraries exists for some other languages, for example:

 - Go - [KlotzAndrew/ci-info](https://github.com/KlotzAndrew/ci-info)
 - JavaScript/Node.js - [watson/ci-info](https://github.com/watson/ci-info)
 - Python - [mgxd/ci-info](https://github.com/mgxd/ci-info)
 - Rust - [sagiegurari/ci_info](https://github.com/sagiegurari/ci_info)
