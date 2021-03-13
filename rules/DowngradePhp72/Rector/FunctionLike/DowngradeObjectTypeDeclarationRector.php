<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectWithoutClassType;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp71\TypeDeclaration\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\DowngradeObjectTypeDeclarationRectorTest
 */
final class DowngradeObjectTypeDeclarationRector extends AbstractRector
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
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->params as $param) {
            $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType(
                $param,
                $node,
                ObjectWithoutClassType::class
            );
        }

        $this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, ObjectWithoutClassType::class);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove the "object" param and return type, add a @param and @return tags instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction(object $someObject): object
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param object $someObject
     * @return object
     */
    public function someFunction($someObject)
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }
}
