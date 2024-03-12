# Changelog

<!-- There is always Unreleased section on the top. Subsections (Added, Changed, Fixed, Removed) should be added as needed. -->

## Unreleased

## 4.2.0 - 2024-03-12
- Require PHP ^7.4.
- Deprecate Wercker, which ceased to exist.

## 4.1.0 - 2021-04-14
- Add SourceHut detection support.

## 4.0.0 - 2021-02-02
### Added
- `getTargetBranch()` method to return the name of the branch where current branch is targeted (aka "base branch").
- Azure DevOps Pipelines detection support.
- `CiDetectorInterface` (extended by `CiDetector`) to allow simpler extension.

### Fixed
- Fix build URL detection on Travis (it always reported travis-ci.org URL, even if the build was on travis-ci.com).

### Changed
- **[BC break]** Rename methods to make them VCS-agnostic. Use `getCommit()` instead of `getGitCommit()` and `getBranch()` instead of `getGitBranch()`.
- **[BC break]** Declare `CiDetector` constructor final.

### Removed
- **[BC break]** Remove `Travis::TRAVIS_BASE_URL` constant.

## 3.5.1 - 2020-09-04
- Allow PHP 8.

## 3.5.0 - 2020-08-25
- Use more suitable value for "build number" on GitHub Actions.
- Add Wercker detection support.

## 3.4.0 - 2020-05-11
- Add AWS CodeBuild detection support.
- Add Bitbucket Pipelines support.
- Allow late static binding in `CiDetector::fromEnvironment()` when inheriting the class.
- Fix branch detection in PR builds on AppVeyor, Buddy and GitHub Actions (target branch of the PR was returned instead).

## 3.3.0 - 2020-03-06
- Allow injecting instance of `Env` using `CiDetector::fromEnvironment()` (useful for environment mocking in unit tests).

## 3.2.0 - 2020-02-18
- Add GitHub Actions detection support.
- Add Buddy detection support.
- Add `getRepositoryName()` method to detect repository name (slug) like `OndraM/ci-detector` (not supported on TeamCity, Jenkins and continuousphp).
- Add `isPullRequest()` to detect if current build has been triggered by a pull request (merge request).
  Be aware that this method returns a `TrinaryLogic` object to handle cases when it cannot be detected
  whether build was triggered by a pull request (like on TeamCity and Jenkins CI).
- Change build URL on AppVeyor to use new permalink URL.

## 3.1.1 - 2019-11-11
- Fix Gitlab 9.0+ support (environment variables were renamed in GitLab 9.0+).

## 3.1.0 - 2018-02-19
- Add continuousphp CI support.
- Add drone CI support.

## 3.0.0 - 2017-10-27
- Require PHP 7.1, use strict types.

## 2.1.0 - 2017-05-26
- Add AppVeyor (Windows cloud CI) support.

## 2.0.0 - 2017-01-07
- **[BC break]** The `detect()` method of `CiDetector` class is no longer static.
- **[BC break]** Rearrange namespaces, all classes are now in `CiDetector` sub-namespace.
- Added `isCiDetected()` method to detect if current environment is CI.
- **[BC break]** `detect()` method always returns instance of `CiInterface` and throws `CiNotDetectedException` if CI environment is not detected.

## 1.1.0 - 2017-01-06
- Add `getGitBranch()` method to detect Git branch of the build (supported by all CIs except TeamCity).
- Add `getRepositoryUrl()` method to detect repository source URL (not supported Codeship, TeamCity, Travis).

## 1.0.0 - 2016-08-20
- Initial release supporting Jenkins, Travis CI, Bamboo, CircleCI, Codeship, GitLab and TeamCity services.
