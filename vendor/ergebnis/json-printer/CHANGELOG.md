# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

For a full diff see [`3.1.1...main`][3.1.1...main].

## [`3.1.1`][3.1.1]

For a full diff see [`3.1.0...3.1.1`][3.1.0...3.1.1].

### Changed

* Dropped support for PHP 7.1 ([#199]), by [@localheinz]

## [`3.1.0`][3.1.0]

For a full diff see [`3.0.2...3.1.0`][3.0.2...3.1.0].

### Added

* Added support for PHP 8.0 ([#172]), by [@localheinz]

## [`3.0.2`][3.0.2]

For a full diff see [`3.0.1...3.0.2`][3.0.1...3.0.2].

### Fixed

* Brought back support for PHP 7.1 ([#76]), by [@localheinz]

## [`3.0.1`][3.0.1]

For a full diff see [`3.0.0...3.0.1`][3.0.0...3.0.1].

### Fixed

* Removed an inappropriate `replace` configuration from `composer.json` ([#72]), by [@localheinz]

## [`3.0.0`][3.0.0]

For a full diff see [`2.0.1...3.0.0`][2.0.1...3.0.0].

### Changed

* Renamed vendor namespace `Localheinz` to `Ergebnis` after move to [@ergebnis] ([#67]), by [@localheinz]

  Run

  ```
  $ composer remove localheinz/json-printer
  ```

  and

  ```
  $ composer require ergebnis/json-printer
  ```

  to update.

  Run

  ```
  $ find . -type f -exec sed -i '.bak' 's/Localheinz\\Json\\Printer/Ergebnis\\Json\\Printer/g' {} \;
  ```

  to replace occurrences of `Localheinz\Json\Printer` with `Ergebnis\Json\Printer`.

  Run

  ```
  $ find -type f -name '*.bak' -delete
  ```

  to delete backup files created in the previous step.

### Fixed

* Removed support for PHP 7.1 ([#55]), by [@localheinz]
* Required implicit dependencies `ext-json` and `ext-mbstring` explicitly ([#63]), by [@localheinz]

## [`2.0.1`][2.0.1]

For a full diff see [`2.0.0...2.0.1`][2.0.0...2.0.1].

### Fixed

* Started rejecting mixed tabs and spaces as indent ([#37]), by [@localheinz]

## [`2.0.0`][2.0.0]

For a full diff see [`1.1.0...2.0.0`][1.1.0...2.0.0].

### Changed

* Allowed specifying new-line character ([#33]), by [@localheinz]

## [`1.1.0`][1.1.0]

For a full diff see [`1.0.0...1.1.0`][1.0.0...1.1.0].

## [`1.0.0`][1.0.0]

For a full diff see [`8849fc6...1.0.0`][8849fc6...1.0.0].

[1.0.0]: https://github.com/ergebnis/json-printer/releases/tag/1.0.0
[1.1.0]: https://github.com/ergebnis/json-printer/releases/tag/1.1.0
[2.0.0]: https://github.com/ergebnis/json-printer/releases/tag/2.0.0
[2.0.1]: https://github.com/ergebnis/json-printer/releases/tag/2.0.1
[3.0.0]: https://github.com/ergebnis/json-printer/releases/tag/3.0.0
[3.0.1]: https://github.com/ergebnis/json-printer/releases/tag/3.0.1
[3.0.2]: https://github.com/ergebnis/json-printer/releases/tag/3.0.2
[3.1.0]: https://github.com/ergebnis/json-printer/releases/tag/3.1.0
[3.1.1]: https://github.com/ergebnis/json-printer/releases/tag/3.1.1

[8849fc6...1.0.0]: https://github.com/ergebnis/json-printer/compare/8849fc6...1.0.0
[1.0.0...1.1.0]: https://github.com/ergebnis/json-printer/compare/1.0.0...1.1.0
[1.1.0...2.0.0]: https://github.com/ergebnis/json-printer/compare/1.1.0...2.0.0
[2.0.0...2.0.1]: https://github.com/ergebnis/json-printer/compare/2.0.0...2.0.1
[2.0.1...3.0.0]: https://github.com/ergebnis/json-printer/compare/2.0.1...3.0.0
[3.0.0...3.0.1]: https://github.com/ergebnis/json-printer/compare/3.0.0...3.0.1
[3.0.1...3.0.2]: https://github.com/ergebnis/json-printer/compare/3.0.1...3.0.2
[3.0.2...3.1.0]: https://github.com/ergebnis/json-printer/compare/3.0.2...3.1.0
[3.1.0...3.1.1]: https://github.com/ergebnis/json-printer/compare/3.1.0...3.1.1
[3.1.1...main]: https://github.com/ergebnis/json-printer/compare/3.1.1...main

[#33]: https://github.com/ergebnis/json-printer/pull/33
[#37]: https://github.com/ergebnis/json-printer/pull/37
[#55]: https://github.com/ergebnis/json-printer/pull/55
[#63]: https://github.com/ergebnis/json-printer/pull/63
[#67]: https://github.com/ergebnis/json-printer/pull/67
[#72]: https://github.com/ergebnis/json-printer/pull/72
[#76]: https://github.com/ergebnis/json-printer/pull/77
[#172]: https://github.com/ergebnis/json-printer/pull/172
[#199]: https://github.com/ergebnis/json-printer/pull/199

[@ergebnis]: https://github.com/ergebnis
[@localheinz]: https://github.com/localheinz
