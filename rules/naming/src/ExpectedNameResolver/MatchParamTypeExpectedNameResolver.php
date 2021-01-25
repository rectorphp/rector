<?php

declare(strict_types=1);

namespace Rector\Naming\ExpectedNameResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class MatchParamTypeExpectedNameResolver extends AbstractExpectedNameResolver
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @required
     */
    public function autowireMatchParamTypeExpectedNameResolver(
        StaticTypeMapper $staticTypeMapper,
        PropertyNaming $propertyNaming
    ): void {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->propertyNaming = $propertyNaming;
    }

    /**
     * @param Param $node
     */
    public function resolve(Node $node): ?string
    {
        // nothing to verify
        if ($node->type === null) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
        $expectedName = $this->propertyNaming->getExpectedNameFromType($staticType);
        if (! $expectedName instanceof ExpectedName) {
            return null;
        }

        return $expectedName->getName();
    }
}
