# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

PRs and issues are linked, so you can find more about it. Thanks to [ChangelogLinker](https://github.com/Symplify/ChangelogLinker).

<!-- changelog-linker -->

## [v0.5.3] - 2019-06-01

### Added

- [#1520] [PHP] Add `is_countable` to `CountOnNull`

### Changed

- [#1521] make `LevelOptionResolver` smarter + add `--set` alias to `--level`

## [v0.5.2] - 2019-05-31

### Fixed

- [#1510] [CodeQuality] Add trait and parent class support for `CompleteDynamicPropertiesRector`
- [#1508] [CodeQuality] Fix unneeded return in `CallableThisArrayToAnonymousFunctionRector`
- [#1509] [PHP] Fix `AddDefaultValueForUndefinedVariableRector` for static variable
- [#1507] [PHP] Fix `BinaryOpBetweenNumberAndStringRector` for concat
- [#1517] fix `RenameClassRector` to change only direct class names, not children
- [#1511] fix `PHPStormVarAnnotationRector` for too nested var [closes [#1407]]
- [#1513] Make Symfony 4.3 + phpdoc-parser 0.3.4 compatible
- [#1506] Ensure `static` variables are considered as declared, Thanks to [@Aerendir]
- [#1502] Test concatenation dot is ignored, Thanks to [@Aerendir]

## [v0.5.1] - 2019-05-30

### Added

- [#1496] [Symfony 4.3] Add class renames, method renames and added arguments

### Fixed

- [#1493] Fix documentation minor mistake, Thanks to [@alterphp]

<!-- dumped content end -->

## [v0.5.0] - 2019-05-28

### Added

- [#1487] [Legacy] Remove singleton
- [#1468] [MultipleClassFileToPsr4ClassesRector] Original file is deleted even if class matches filename, Thanks to [@JanMikes]
- [#1424] [SplitStringClassConstantToClassConstFetchRector] Remove duplicated namespace separator, Thanks to [@mxr576]
- [#1470] [PHP][CodingStyle] string class to ::class
- [#1367] [DeadCode] Anonymous class implementing an interface doesn't respect interface signature, Thanks to [@pierredup]
- [#1404] [TypeDeclaration] Create new set
- [#1414] [PSR-4 Split] Split interfaces and traits as well, Thanks to [@JanMikes]
- [#1416] [PHP 7.4] Spread array
- [#1419] [CodeQuality] Add For to foreach
- [#1443] [CodeQuality] Add CompactToVariablesRector
- [#1488] 🎉[CodeQuality] Add CompleteDynamicPropertiesRector
- [#1390] [CodeQuality] Add AndAssignsToSeparateLinesRector
- [#1485] [CodingStyle] Add VarConstantCommentRector
- [#1484] [CodingStyle] Add SplitDoubleAssignRector
- [#1483] [CodingStyle] Add ArrayPropertyDefaultValueRector
- [#1482] [CodingStyle] Add CatchExceptionNameMatchingTypeRector
- [#1481] [CodingStyle] Add FollowRequireByDirRector
- [#1480] [CodingStyle] Add ConsistentPregDelimiterRector
- [#1447] [CodingStyle] add partial support already imported support to ImportFullyQualifiedNamesRector
- [#1389] [DeadCode] Add RemoveAndTrueRector
- [#1392] [DeadCode] Add RemoveDefaultArgumentValueRector
- [#1451] [PHP] Add RemoveMissingCompactVariableRector
- [#1418] [PHP-DI] Add php-di [@Inject] annotation import
- [#1460] [Psr4] Add supprot for namespace less MultipleClassFileToPsr4ClassesRector
- [#1486] [SOLID] Add AbstractChildlessUnusedClassesRector
- [#1406] [TypeDeclaration] Add AddFunctionReturnTypeRector
- [#1403] [Symfony 4.3] Add swapped dispatch() arguments for EventDispatcher
- [#1429] Add missing end bracket at HowItWorks.md's sample, Thanks to [@sasezaki]
- [#1430] Add working directory option, Thanks to [@ktomk]
- [#1417] Add trait analysis without class dependency
- [#1491] add JsonOutputFormatter
- [#1492] Symplify 6 bump + add relative paths to JsonOutputFormatter
- [#1410] [PHP 7.4] Add ClosureToArrowFunctionRector
- [#1449] [PHP 7.1] Add BinaryOpBetweenNumberAndStringRector
- [#1450] [PHP 7.1] Add float to BinaryOpBetweenNumberAndStringRector
- [#1452] Add non-namespaced support to ImportFullyQualifiedNamesRector
- [#1461] [supporŧ] add funding Github - news from Github Satellite
- [#1478] composer: add authors
- [#1382] Add support to rename classes and it's namespace, Thanks to [@JanMikes]
- [#1377] Add function support to ImportFullyQualifiedNamesRector
- [#1489] [backers] add Jan Votruba

### Changed

- [#1412] [ImportFullyQualifiedNamesRector] Allow to opt-out from doc block modification, Thanks to [@mxr576]
- [#1439] [PSR4] Improve MultipleClassFileToPsr4ClassesRector output
- [#1477] introduce OutputFormatterCollector to allow extension of output formatters
- [#1446] Narrow ArraySpreadInsteadOfArrayMergeRector to numeric-arrays only
- [#1479] update to php-parser 4.2.2

### Fixed

- [#1395] Preserve file permissions when updating a file, Thanks to [@LeSuisse]
- [#1397] [DeadCode] Various set fixes
- [#1398] Fix travis for Laravel self-run
- [#1391] fix ImportFullyQualifiedNamesRector on multiple files
- [#1444] fix ReservedObjectRector for lowercased object
- [#1471] fix deleting file that matches class name
- [#1425] speedup RenameClassRector on doc comments
- [#1464] do not override printing files with previous stmts if possible

### Removed

- [#1415] Removed duplicated code, Thanks to [@DaveLiddament]

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

[#1492]: https://github.com/rectorphp/rector/pull/1492
[#1491]: https://github.com/rectorphp/rector/pull/1491
[#1489]: https://github.com/rectorphp/rector/pull/1489
[#1488]: https://github.com/rectorphp/rector/pull/1488
[#1487]: https://github.com/rectorphp/rector/pull/1487
[#1486]: https://github.com/rectorphp/rector/pull/1486
[#1485]: https://github.com/rectorphp/rector/pull/1485
[#1484]: https://github.com/rectorphp/rector/pull/1484
[#1483]: https://github.com/rectorphp/rector/pull/1483
[#1482]: https://github.com/rectorphp/rector/pull/1482
[#1481]: https://github.com/rectorphp/rector/pull/1481
[#1480]: https://github.com/rectorphp/rector/pull/1480
[#1479]: https://github.com/rectorphp/rector/pull/1479
[#1478]: https://github.com/rectorphp/rector/pull/1478
[#1477]: https://github.com/rectorphp/rector/pull/1477
[#1471]: https://github.com/rectorphp/rector/pull/1471
[#1470]: https://github.com/rectorphp/rector/pull/1470
[#1468]: https://github.com/rectorphp/rector/pull/1468
[#1464]: https://github.com/rectorphp/rector/pull/1464
[#1461]: https://github.com/rectorphp/rector/pull/1461
[#1460]: https://github.com/rectorphp/rector/pull/1460
[#1452]: https://github.com/rectorphp/rector/pull/1452
[#1451]: https://github.com/rectorphp/rector/pull/1451
[#1450]: https://github.com/rectorphp/rector/pull/1450
[#1449]: https://github.com/rectorphp/rector/pull/1449
[#1447]: https://github.com/rectorphp/rector/pull/1447
[#1446]: https://github.com/rectorphp/rector/pull/1446
[#1444]: https://github.com/rectorphp/rector/pull/1444
[#1443]: https://github.com/rectorphp/rector/pull/1443
[#1439]: https://github.com/rectorphp/rector/pull/1439
[#1430]: https://github.com/rectorphp/rector/pull/1430
[#1429]: https://github.com/rectorphp/rector/pull/1429
[#1425]: https://github.com/rectorphp/rector/pull/1425
[#1424]: https://github.com/rectorphp/rector/pull/1424
[#1419]: https://github.com/rectorphp/rector/pull/1419
[#1418]: https://github.com/rectorphp/rector/pull/1418
[#1417]: https://github.com/rectorphp/rector/pull/1417
[#1416]: https://github.com/rectorphp/rector/pull/1416
[#1415]: https://github.com/rectorphp/rector/pull/1415
[#1414]: https://github.com/rectorphp/rector/pull/1414
[#1412]: https://github.com/rectorphp/rector/pull/1412
[#1410]: https://github.com/rectorphp/rector/pull/1410
[#1406]: https://github.com/rectorphp/rector/pull/1406
[#1404]: https://github.com/rectorphp/rector/pull/1404
[#1403]: https://github.com/rectorphp/rector/pull/1403
[#1398]: https://github.com/rectorphp/rector/pull/1398
[#1397]: https://github.com/rectorphp/rector/pull/1397
[#1395]: https://github.com/rectorphp/rector/pull/1395
[#1392]: https://github.com/rectorphp/rector/pull/1392
[#1391]: https://github.com/rectorphp/rector/pull/1391
[#1390]: https://github.com/rectorphp/rector/pull/1390
[#1389]: https://github.com/rectorphp/rector/pull/1389
[#1382]: https://github.com/rectorphp/rector/pull/1382
[#1377]: https://github.com/rectorphp/rector/pull/1377
[#1367]: https://github.com/rectorphp/rector/pull/1367
[@sasezaki]: https://github.com/sasezaki
[@pierredup]: https://github.com/pierredup
[@ktomk]: https://github.com/ktomk
[@LeSuisse]: https://github.com/LeSuisse
[@JanMikes]: https://github.com/JanMikes
[@Inject]: https://github.com/Inject
[@DaveLiddament]: https://github.com/DaveLiddament
[v0.4.12]: https://github.com/rectorphp/rector/compare/v0.4.11...v0.4.12
[v0.5.0]: https://github.com/rectorphp/rector/compare/v0.4.12...v0.5.0
[#1521]: https://github.com/rectorphp/rector/pull/1521
[#1520]: https://github.com/rectorphp/rector/pull/1520
[#1517]: https://github.com/rectorphp/rector/pull/1517
[#1513]: https://github.com/rectorphp/rector/pull/1513
[#1511]: https://github.com/rectorphp/rector/pull/1511
[#1510]: https://github.com/rectorphp/rector/pull/1510
[#1509]: https://github.com/rectorphp/rector/pull/1509
[#1508]: https://github.com/rectorphp/rector/pull/1508
[#1507]: https://github.com/rectorphp/rector/pull/1507
[#1506]: https://github.com/rectorphp/rector/pull/1506
[#1502]: https://github.com/rectorphp/rector/pull/1502
[#1500]: https://github.com/rectorphp/rector/pull/1500
[#1496]: https://github.com/rectorphp/rector/pull/1496
[#1493]: https://github.com/rectorphp/rector/pull/1493
[#1407]: https://github.com/rectorphp/rector/pull/1407
[v0.5.2]: https://github.com/rectorphp/rector/compare/v0.5.1...v0.5.2
[@alterphp]: https://github.com/alterphp
[@Aerendir]: https://github.com/Aerendir
[v0.5.1]: https://github.com/rectorphp/rector/compare/v0.5.0...v0.5.1