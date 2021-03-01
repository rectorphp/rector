<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeReturnSelfTypeDeclarationRector\DowngradeReturnSelfTypeDeclarationRectorTest
 */
final class DowngradeReturnSelfTypeDeclarationRector extends AbstractRector
{
    /**
     * @var PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;

    public function __construct(PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "self" return type, add a "@return self" tag instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function foo(): self
    {
        return $this;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function foo()
    {
        return $this;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, ThisType::class);

        return $node;
    }
}
