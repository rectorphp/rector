<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;

/**
 * @see \Rector\Restoration\Tests\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\MissingClassConstantReferenceToStringRectorTest
 */
final class MissingClassConstantReferenceToStringRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert missing class reference to string', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return NonExistingClass::class;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 'NonExistingClass';
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
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'class')) {
            return null;
        }

        $referencedClass = $this->getName($node->class);
        if ($referencedClass === null) {
            return null;
        }

        if (ClassExistenceStaticHelper::doesClassLikeExist($referencedClass)) {
            return null;
        }

        return new String_($referencedClass);
    }
}
