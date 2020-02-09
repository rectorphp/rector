<?php

declare(strict_types=1);

namespace Rector\SOLID\NodeFinder;

use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ParentClassConstantNodeFinder
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function find(string $class, string $constant): ?ClassConst
    {
        $classNode = $this->parsedNodesByType->findClass($class);
        if ($classNode === null) {
            return null;
        }

        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return null;
        }

        return $this->parsedNodesByType->findClassConstant($parentClassName, $constant);
    }
}
