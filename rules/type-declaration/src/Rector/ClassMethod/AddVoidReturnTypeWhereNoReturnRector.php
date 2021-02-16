<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\CodingStyle\ValueObject\ObjectMagicMethods;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\AddVoidReturnTypeWhereNoReturnRectorTest
 */
final class AddVoidReturnTypeWhereNoReturnRector extends AbstractRector
{
    /**
     * @var SilentVoidResolver
     */
    private $silentVoidResolver;

    public function __construct(SilentVoidResolver $silentVoidResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type void to function like without any return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues()
    {
        $value = 1000;
        return;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getValues(): void
    {
        $value = 1000;
        return;
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
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if ($node->returnType !== null) {
            return null;
        }

        if ($this->isNames($node, ObjectMagicMethods::METHOD_NAMES)) {
            return null;
        }

        if (! $this->silentVoidResolver->hasExlusiveVoid($node)) {
            return null;
        }

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            $node->returnType = new Identifier('void');
        }

        return $node;
    }
}
