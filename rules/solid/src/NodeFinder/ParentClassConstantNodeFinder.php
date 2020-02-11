<?php

declare(strict_types=1);

namespace Rector\SOLID\NodeFinder;

use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParentClassConstantNodeFinder
{
    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(ParsedNodeCollector $parsedNodeCollector)
    {
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    public function find(string $class, string $constant): ?ClassConst
    {
        $classNode = $this->parsedNodeCollector->findClass($class);
        if ($classNode === null) {
            return null;
        }

        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return null;
        }

        return $this->parsedNodeCollector->findClassConstant($parentClassName, $constant);
    }
}
