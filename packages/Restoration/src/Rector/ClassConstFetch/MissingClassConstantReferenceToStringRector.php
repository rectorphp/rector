<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Restoration\Tests\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector\MissingClassConstantReferenceToStringRectorTest
 */
final class MissingClassConstantReferenceToStringRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert missing class reference to string', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return NonExistingClass::class;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return 'NonExistingClass';
    }
}
PHP
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
