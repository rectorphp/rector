<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Property;
final class ConfigurationNodeFactory
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\NodeFactory\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\RectorGenerator\NodeFactory\NodeFactory $nodeFactory)
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
            $property->props[0]->default = new \PhpParser\Node\Expr\Array_([]);
            $properties[] = $property;
        }
        return $properties;
    }
}
