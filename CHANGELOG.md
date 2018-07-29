# Changelog

<!-- changelog-linker -->

## Unreleased

### Added

- [#508] [Rector] Add MergeInterfaceRector
- [#509] Add YAML refactoring support
- [#512] Add --with-style option to apply basic style [closes [#506]]

### Changed

- [#493] Improve Test Performance
- [#500] Change RemoveConfiguratorConstantsRector to generic RenameClassConstantsUseToStringsRector
- [#515] Improvements, Thanks to [@carusogabriel]

### Fixed

- [#507] Fix chain method multi-rename
- [#513] Fixed README badge with packagist URL, Thanks to [@fulopattila122]

### Unknown Category

- [#492] Lower Cognitive Complexity to 6
- [#499] Change InjectPropertyRector to generic AnnotatedPropertyInjectToConstructorInjectionRector
- [#501] Change SetInjectToAddTagRector to generic MethodCallToAnotherMethodCallWithArgumentsRector
- [#502] Change more Nette specific Rectors to generic
- [#504] narrow focus to packages with 10 M+ downloads [closes [#495]]
- [#516] init monorepo split [closes [#425]]

## [v0.3.3] - 2018-06-02

### Changed

- [#479] Various NodeTypeResolver and tests improvements
- [#480] Decouple Rectors dependency on specific classes
- [#482] Decouple Symfony-related code to own package
- [#484] Decouple Sensio-related code to own package
- [#485] Decouple Nette-related code to own package
- [#486] Decouple Sylius-related code to own package
- [#487] Decouple Nette-related code to own package
- [#488] Decouple PHP-Parser-related upgrade code to own package
- [#489] Decouple Doctrine-related code to own package

### Unknown Category

- [#478] Lower deps to Symfony3.4
- [#490] Move CodeQuality from Contrib up

<!-- dumped content end -->

[#516]: https://github.com/rectorphp/rector/pull/516
[#515]: https://github.com/rectorphp/rector/pull/515
[#513]: https://github.com/rectorphp/rector/pull/513
[#512]: https://github.com/rectorphp/rector/pull/512
[#509]: https://github.com/rectorphp/rector/pull/509
[#508]: https://github.com/rectorphp/rector/pull/508
[#507]: https://github.com/rectorphp/rector/pull/507
[#506]: https://github.com/rectorphp/rector/pull/506
[#504]: https://github.com/rectorphp/rector/pull/504
[#502]: https://github.com/rectorphp/rector/pull/502
[#501]: https://github.com/rectorphp/rector/pull/501
[#500]: https://github.com/rectorphp/rector/pull/500
[#499]: https://github.com/rectorphp/rector/pull/499
[#495]: https://github.com/rectorphp/rector/pull/495
[#493]: https://github.com/rectorphp/rector/pull/493
[#492]: https://github.com/rectorphp/rector/pull/492
[#490]: https://github.com/rectorphp/rector/pull/490
[#489]: https://github.com/rectorphp/rector/pull/489
[#488]: https://github.com/rectorphp/rector/pull/488
[#487]: https://github.com/rectorphp/rector/pull/487
[#486]: https://github.com/rectorphp/rector/pull/486
[#485]: https://github.com/rectorphp/rector/pull/485
[#484]: https://github.com/rectorphp/rector/pull/484
[#482]: https://github.com/rectorphp/rector/pull/482
[#480]: https://github.com/rectorphp/rector/pull/480
[#479]: https://github.com/rectorphp/rector/pull/479
[#478]: https://github.com/rectorphp/rector/pull/478
[#425]: https://github.com/rectorphp/rector/pull/425
[@fulopattila122]: https://github.com/fulopattila122
[@carusogabriel]: https://github.com/carusogabriel