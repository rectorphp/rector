<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\RectorGenerator\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
final class ConfigurationNodeFactory
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $ruleConfiguration
     * @return Property[]
     */
    public function createProperties(array $ruleConfiguration) : array
    {
        $properties = [];
        foreach (\array_keys($ruleConfiguration) as $privatePropertyName) {
            $property = $this->nodeFactory->createPrivateArrayProperty($privatePropertyName);
            $property->props[0]->default = new Array_([]);
            $properties[] = $property;
        }
        return $properties;
    }
}
