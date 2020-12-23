<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanTypeMapperChecker\DataProvider;

use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class SupportedTypeMappersDataProvider
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers;

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }

    /**
     * @return string[]
     */
    public function provide(): array
    {
        $supportedPHPStanTypeClasses = [];
        foreach ($this->typeMappers as $typeMappers) {
            $supportedPHPStanTypeClasses[] = $typeMappers->getNodeClass();
        }

        return $supportedPHPStanTypeClasses;
    }
}
