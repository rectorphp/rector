<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SymfonyRequiredTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\NormalizeAutowireMethodNamingRector\NormalizeAutowireMethodNamingRectorTest
 */
final class NormalizeAutowireMethodNamingRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use autowire + class name suffix for method with @required annotation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /** @required */
    public function foo()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /** @required */
    public function autowireSomeClass()
    {
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (! $phpDocInfo->hasByType(SymfonyRequiredTagNode::class)) {
            return null;
        }

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            return null;
        }

        $classShortName = $this->nodeNameResolver->getShortName($className);
        $expectedMethodName = 'autowire' . $classShortName;

        if ((string) $node->name === $expectedMethodName) {
            return null;
        }

        /** @var Identifier $method */
        $method = $node->name;
        $method->name = $expectedMethodName;

        return $node;
    }
}
