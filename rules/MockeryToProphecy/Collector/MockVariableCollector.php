<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class MockVariableCollector
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @param FuncCall|StaticCall $node
     * @return array<string, class-string>
     */
    public function collectMockVariableName(Node $node): array
    {
        $mockVariableTypesByNames = [];

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Assign) {
            return [];
        }

        if (! $parentNode->var instanceof Variable) {
            return [];
        }

        /** @var Variable $variable */
        $variable = $parentNode->var;

        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($variable);

        $type = $node->args[0]->value;

        $mockedType = $this->valueResolver->getValue($type);
        $mockVariableTypesByNames[$variableName] = $mockedType;

        return $mockVariableTypesByNames;
    }
}
