* [TypeDeclaration] Ensure return $node only when changed on AddParamTypeDeclarationRector ([#2399](https://github.com/rectorphp/rector-src/pull/2399)), Thanks @samsonasik!
* [Core] Fix bootstrap stubs load on PHP 7.2 when vendor/ excluded via skip() ([#2394](https://github.com/rectorphp/rector-src/pull/2394)), Thanks @samsonasik!
* [TypeDeclaration] Ensure return $node only when changed on AddReturnTypeDeclarationRector ([#2400](https://github.com/rectorphp/rector-src/pull/2400)), Thanks @samsonasik!
* [scoped] Keep tracy logger as needed in dump() (https://github.com/rectorphp/rector-src/commit/9b7ef717dded89018fd87febd7ffc7358ecaddcd)
* [Attributes] Add UseAliasNameMatcherTest ([#2401](https://github.com/rectorphp/rector-src/pull/2401))
* [Attribute] Fix UseAliasNameMatcher for the last part of use import rename ([#2402](https://github.com/rectorphp/rector-src/pull/2402))
* Use php-parser to work with literal _ number separator ([#2321](https://github.com/rectorphp/rector-src/pull/2321))
* [DowngradePhp82] Add DowngradeReadonlyClassRector to downgrade  readonly class ([#2322](https://github.com/rectorphp/rector-src/pull/2322)), Thanks @samsonasik!
* [PHP 8.0] Fix double annotation change on annotation to attribute ([#2403](https://github.com/rectorphp/rector-src/pull/2403))
* try victor-cleaner (https://github.com/rectorphp/rector-src/commit/0bdcce27500cdff0186c82b4c4dfc584292de3f2)
* remove victor, it handles only metafiles, not unused classes (https://github.com/rectorphp/rector-src/commit/06b0b325da23f9303524f4696a876202d4996cb2)
* add extensions to rector phpstan types (https://github.com/rectorphp/rector-src/commit/424697fdc4110ab40be313b701334afd1cefdec1), #7197
* return intl and mbstring polyfills (https://github.com/rectorphp/rector-src/commit/0693c4f46f7d9fc6d1e65451f1c17ff7e98ca511)
* disable dependabot, as mostly breaking with no help; handle upgrades manually (https://github.com/rectorphp/rector-src/commit/07b9a3077c1fe71974605b4e604af987def300ed)
* remove php-cs-fixer as implicit ECS dependency (https://github.com/rectorphp/rector-src/commit/59445debbe801b58a993fa77b2907350cbb47f92)
* use generic patch for RectorConfig (https://github.com/rectorphp/rector-src/commit/8eac546e111b9d2f4053c722a7552a2eba9e5836)
* [PHP 8.0] Add ConstantListClassToEnumRector ([#2404](https://github.com/rectorphp/rector-src/pull/2404))
* [Php80] Mirror additional docblock on importNames() on ClassPropertyAssignToConstructorPromotionRector ([#2410](https://github.com/rectorphp/rector-src/pull/2410)), Thanks @samsonasik!
* [Scoped] Update full_build.sh to reflect the current Github workflow for build scoped ([#2413](https://github.com/rectorphp/rector-src/pull/2413)), Thanks @samsonasik!
* [Php74] Skip nullable mixed on Php 8.0 feature enabled on TypedPropertyRector ([#2414](https://github.com/rectorphp/rector-src/pull/2414)), Thanks @samsonasik!
* use directly ParamTagValueNode ([#2412](https://github.com/rectorphp/rector-src/pull/2412))
* [PHP 8.0] Add method param to ConstantListClassToEnumRector ([#2415](https://github.com/rectorphp/rector-src/pull/2415))
* [DowngradePhp80] Add DowngradeEnumToConstantListClassRector ([#2416](https://github.com/rectorphp/rector-src/pull/2416))
* [Downgrade] Add class method param to DowngradeEnumToConstantListClassRector   ([#2417](https://github.com/rectorphp/rector-src/pull/2417))
* [PHP 8.0] Add return type support to ConstantListClassToEnumRector ([#2420](https://github.com/rectorphp/rector-src/pull/2420))
* [PHP 8.0] Add property support to ConstantListClassToEnumRector ([#2422](https://github.com/rectorphp/rector-src/pull/2422))
* [DeadCode] Add RemoveJustPropertyFetchForAssignRector ([#2423](https://github.com/rectorphp/rector-src/pull/2423))
* Update PHPStan to ^1.7.10 ([#2424](https://github.com/rectorphp/rector-src/pull/2424)), Thanks @samsonasik!
*  [e2e] Add e2e for parallel process with current directory contains space  ([#2421](https://github.com/rectorphp/rector-src/pull/2421)), Thanks @samsonasik!
* [Renaming] Skip docblock rename different namespace on RenameClassRector ([#2419](https://github.com/rectorphp/rector-src/pull/2419)), Thanks @samsonasik!
* Skip used in new ctor ([#2425](https://github.com/rectorphp/rector-src/pull/2425))
* [Naming] Fix PseudoNamespaceToNamespaceRector reporting on change ([#2426](https://github.com/rectorphp/rector-src/pull/2426))
* bump phpunit phpstan package (https://github.com/rectorphp/rector-src/commit/c8814f5422888e5e5a269bff2649f83c2b08018c)
* remove phpstan enum package, as no longer used (https://github.com/rectorphp/rector-src/commit/eedfd1155da61baff4c5eae44c1d20ab2d5941c7)
