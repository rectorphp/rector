<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\MagicDisclosure\Matcher\ClassNameTypeMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\MagicDisclosure\Tests\Rector\ClassMethod\ReturnThisRemoveRector\ReturnThisRemoveRectorTest
 */
final class ReturnThisRemoveRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASSES_TO_DEFLUENT = '$classesToDefluent';

    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @var ClassNameTypeMatcher
     */
    private $classNameTypeMatcher;

    public function __construct(ClassNameTypeMatcher $classNameTypeMatcher)
    {
        $this->classNameTypeMatcher = $classNameTypeMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Removes "return $this;" from *fluent interfaces* for specified classes.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
class SomeExampleClass
{
    public function someFunction()
    {
        return $this;
    }

    public function otherFunction()
    {
        return $this;
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeExampleClass
{
    public function someFunction()
    {
    }

    public function otherFunction()
    {
    }
}
PHP
                    ,
                    [
                        '$classesToDefluent' => ['SomeExampleClass'],
                    ]
                ),
            ]
        );
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
        $returnThis = $this->matchSingleReturnThis($node);
        if ($returnThis === null) {
            return null;
        }

        if (! $this->classNameTypeMatcher->doesExprMatchNames($returnThis->expr, $this->classesToDefluent)) {
            return null;
        }

        $this->removeNode($returnThis);

        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            throw new ShouldNotHappenException();
        }

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            $classMethod->returnType = new Identifier('void');
        }

        $this->removePhpDocTagValueNode($classMethod, ReturnTagValueNode::class);

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->classesToDefluent = $configuration[self::CLASSES_TO_DEFLUENT] ?? [];
    }

    /**
     * Matches only 1st level "return $this;"
     */
    private function matchSingleReturnThis(ClassMethod $classMethod): ?Return_
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, Return_::class);

        // can be only 1 return
        if (count($returns) !== 1) {
            return null;
        }

        $return = $returns[0];
        if ($return->expr === null) {
            return null;
        }

        if (! $this->isVariableName($return->expr, 'this')) {
            return null;
        }

        $parentNode = $return->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode !== $classMethod) {
            return null;
        }

        return $return;
    }
}
