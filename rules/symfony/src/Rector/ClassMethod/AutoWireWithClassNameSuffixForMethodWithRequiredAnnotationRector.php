<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRectorTest
 */
final class AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REQUIRED_TAG = 'required';

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
        if (! $this->hasTagByName($node, self::REQUIRED_TAG)) {
            return null;
        }

        $classShortName = $node->getAttribute(AttributeKey::CLASS_SHORT_NAME);
        if ($classShortName === null) {
            return null;
        }

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
