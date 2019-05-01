# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

PRs and issues are linked, so you can find more about it. Thanks to [ChangelogLinker](https://github.com/Symplify/ChangelogLinker).

<!-- changelog-linker -->

## [v0.4.12] - 2019-05-02

### Added

- [#1326] [CodingStyle] Add SplitStringClassConstantToClassConstFetchRector
- [#1327] [CodingStyle] Add ImportFullyQualifiedNamesRector
- [#1363] [PHP] Add AddDefaultValueForUndefinedVariableRector
- [#1347] [RemovingStatic] Add new level
- [#1333] Add PrivatizeLocalClassConstantRector to SOLID, Thanks to [@mxr576]
- [#1362] [PHP 7.4] Add ReservedFnFunctionRector
- [#1346] Add test case for [#1286]

### Changed

- [#1323] allow Nette 3.0, Thanks to [@mimmi20]
- [#1325] [DeadCode] Skip magic methods in RemoveUnusedParameterRector
- [#1351] [DeadCode] Keep different case in RemoveDoubleAssignRector
- [#1353] [DeadCode] Skip traits in RemoveUnusedPrivateMethodRector
- [#1370] make ImportFullyQualifiedNamesRector take into account existing imports on combination of PHP and doc block
- [#1354] Speedup tests by 90 % from 41 secs to 4
- [#1357] Tests improvements
- [#1359] Notice file rectors on run

### Fixed

- [#1369] [CodingStyle] ImportsInClassCollection fixes
- [#1368] [CodingStyle] Fix ImportFullyQualifiedNamesRector for self imports
- [#1365] [CodingStyle] Fix interface short name identical with class name in ImportFullyQualifiedNamesRector
- [#1348] [DeadCode] Remove overriden fix
- [#1352] [DeadCode] Fix RemoveUnusedPrivateMethodRector for self call
- [#1350] [Laravel] Fix MinutesToSecondsInCacheRector DateTimeInterface argument
- [#1361] [Symfony] Fix GetRequestRector for get non method calls
- [#1375] Fix file removal in MultipleClassFileToPsr4ClassesRector
- [#1320] [CakePHP]FIx rule for cakephp37, Thanks to [@o0h]
- [#1331] Use `dev` as Symfony default env to fix issue [#1319], Thanks to [@BernhardWebstudio]

### Removed

- [#1349] [DeadCode] Remove double

## [v0.4.11] - 2019-04-14

### Added

- [#1317] Add Changelog
- [#1302] [Symfony 4.3] Add SimplifyWebTestCaseAssertionsRector
- [#1302] [Symfony 4.3] Add `SimplifyWebTestCaseAssertionsRector`
- [#1311] [CodingStyle] Add `SplitGroupedConstantsAndPropertiesRector`
- [#1301] [PHPUnit] Add `RemoveExpectAnyFromMockRector`
- [#1304] [SOLID] Add `PrivatizeLocalClassConstantRector`
- [#1303] [SOLID] Add `FinalizeClassesWithoutChildrenRector`
- [#1302] [Symfony 4.3] Add `SimplifyWebTestCaseAssertionsRector`

### Changed

- [#1316] Merge collected nodes to ParsedNodesByType
- [#1314] rename `Attribute` to `AttributeKey` to prevent duplicated names with other projects
- [#1318] Update reference to drupal8-rector/drupal8-rector, Thanks to [@mxr576]
- [#1316] Merge collected nodes to ParsedNodesByType

### Fixed

- [#1305] [Symfony 3.0] Fix wrong indentation in symfony30.yaml, Thanks to [@Dodenis]

[#1302]: https://github.com/rectorphp/rector/pull/1302
[#1314]: https://github.com/rectorphp/rector/pull/1314
[#1311]: https://github.com/rectorphp/rector/pull/1311
[#1305]: https://github.com/rectorphp/rector/pull/1305
[#1304]: https://github.com/rectorphp/rector/pull/1304
[#1303]: https://github.com/rectorphp/rector/pull/1303
[#1301]: https://github.com/rectorphp/rector/pull/1301
[@Dodenis]: https://github.com/Dodenis
[#1318]: https://github.com/rectorphp/rector/pull/1318
[#1317]: https://github.com/rectorphp/rector/pull/1317
[#1316]: https://github.com/rectorphp/rector/pull/1316
[@mxr576]: https://github.com/mxr576
[#1375]: https://github.com/rectorphp/rector/pull/1375
[#1370]: https://github.com/rectorphp/rector/pull/1370
[#1369]: https://github.com/rectorphp/rector/pull/1369
[#1368]: https://github.com/rectorphp/rector/pull/1368
[#1365]: https://github.com/rectorphp/rector/pull/1365
[#1363]: https://github.com/rectorphp/rector/pull/1363
[#1362]: https://github.com/rectorphp/rector/pull/1362
[#1361]: https://github.com/rectorphp/rector/pull/1361
[#1359]: https://github.com/rectorphp/rector/pull/1359
[#1357]: https://github.com/rectorphp/rector/pull/1357
[#1354]: https://github.com/rectorphp/rector/pull/1354
[#1353]: https://github.com/rectorphp/rector/pull/1353
[#1352]: https://github.com/rectorphp/rector/pull/1352
[#1351]: https://github.com/rectorphp/rector/pull/1351
[#1350]: https://github.com/rectorphp/rector/pull/1350
[#1349]: https://github.com/rectorphp/rector/pull/1349
[#1348]: https://github.com/rectorphp/rector/pull/1348
[#1347]: https://github.com/rectorphp/rector/pull/1347
[#1346]: https://github.com/rectorphp/rector/pull/1346
[#1333]: https://github.com/rectorphp/rector/pull/1333
[#1331]: https://github.com/rectorphp/rector/pull/1331
[#1327]: https://github.com/rectorphp/rector/pull/1327
[#1326]: https://github.com/rectorphp/rector/pull/1326
[#1325]: https://github.com/rectorphp/rector/pull/1325
[#1323]: https://github.com/rectorphp/rector/pull/1323
[#1320]: https://github.com/rectorphp/rector/pull/1320
[#1319]: https://github.com/rectorphp/rector/pull/1319
[#1286]: https://github.com/rectorphp/rector/pull/1286
[@o0h]: https://github.com/o0h
[@mimmi20]: https://github.com/mimmi20
[@BernhardWebstudio]: https://github.com/BernhardWebstudio
[v0.4.11]: https://github.com/rectorphp/rector/compare/v0.4.11...v0.4.11
