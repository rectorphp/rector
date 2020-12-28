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
    private $supportedTypeMappersResolver;

    /**
     * @var PHPStanTypeClassFinder
     */
    private $phpStanTypeClassFinder;

    public function __construct(
        PHPStanTypeClassFinder $phpStanTypeClassFinder,
        SupportedTypeMappersDataProvider $supportedTypeMappersResolver
    ) {
        $this->supportedTypeMappersResolver = $supportedTypeMappersResolver;
        $this->phpStanTypeClassFinder = $phpStanTypeClassFinder;
    }

    /**
     * @return string[]
     */
    public function resolve(): array
    {
        $typeClasses = $this->phpStanTypeClassFinder->find();
        $supportedTypeClasses = $this->supportedTypeMappersResolver->provide();

        $unsupportedTypeClasses = [];
        foreach ($typeClasses as $phpStanTypeClass) {
            foreach ($supportedTypeClasses as $supportedPHPStanTypeClass) {
                if (is_a($phpStanTypeClass, $supportedPHPStanTypeClass, true)) {
                    continue 2;
                }
            }

            $unsupportedTypeClasses[] = $phpStanTypeClass;
        }

        $typesToRemove = [NonexistentParentClassType::class, ParserNodeTypeToPHPStanType::class];

        return array_diff($unsupportedTypeClasses, $typesToRemove);
    }
}
