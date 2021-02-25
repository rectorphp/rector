<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanTypeMapperChecker\Validator;

use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\ParserNodeTypeToPHPStanType;
use Rector\Utils\PHPStanTypeMapperChecker\DataProvider\SupportedTypeMappersDataProvider;
use Rector\Utils\PHPStanTypeMapperChecker\Finder\PHPStanTypeClassFinder;

final class MissingPHPStanTypeMappersResolver
{
    /**
     * @var SupportedTypeMappersDataProvider
     */
    private $supportedTypeMappersDataProvider;

    /**
     * @var PHPStanTypeClassFinder
     */
    private $phpStanTypeClassFinder;

    public function __construct(
        PHPStanTypeClassFinder $phpStanTypeClassFinder,
        SupportedTypeMappersDataProvider $supportedTypeMappersDataProvider
    ) {
        $this->supportedTypeMappersDataProvider = $supportedTypeMappersDataProvider;
        $this->phpStanTypeClassFinder = $phpStanTypeClassFinder;
    }

    /**
     * @return string[]
     */
    public function resolve(): array
    {
        $typeClasses = $this->phpStanTypeClassFinder->find();
        $supportedTypeClasses = $this->supportedTypeMappersDataProvider->provide();

        $unsupportedTypeClasses = [];
        foreach ($typeClasses as $typeClass) {
            foreach ($supportedTypeClasses as $supportedTypeClass) {
                if (is_a($typeClass, $supportedTypeClass, true)) {
                    continue 2;
                }
            }

            $unsupportedTypeClasses[] = $typeClass;
        }

        $typesToRemove = [NonexistentParentClassType::class, ParserNodeTypeToPHPStanType::class];

        return array_diff($unsupportedTypeClasses, $typesToRemove);
    }
}
