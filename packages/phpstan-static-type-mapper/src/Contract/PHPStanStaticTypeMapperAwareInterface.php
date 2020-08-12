<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Contract;

use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

interface PHPStanStaticTypeMapperAwareInterface
{
    public function setPHPStanStaticTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void;
}
