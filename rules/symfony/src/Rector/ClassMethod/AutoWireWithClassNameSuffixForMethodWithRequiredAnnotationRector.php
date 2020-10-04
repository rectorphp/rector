<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector\AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRectorTest
 */
final class AutoWireWithClassNameSuffixForMethodWithRequiredAnnotationRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/gn2P0C/1
     */
    private const REQUIRED_DOCBLOCK_REGEX = '#\*\s+@required\n?#';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use autowire + class name suffix for method with @required annotation', [
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
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return null;
        }

        if (! Strings::match($docComment->getText(), self::REQUIRED_DOCBLOCK_REGEX)) {
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
