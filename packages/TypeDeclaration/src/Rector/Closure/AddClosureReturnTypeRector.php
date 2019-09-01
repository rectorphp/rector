<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\FunctionLikeManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class AddClosureReturnTypeRector extends AbstractRector
{
    /**
     * @var FunctionLikeManipulator
     */
    private $functionLikeManipulator;

    public function __construct(FunctionLikeManipulator $functionLikeManipulator)
    {
        $this->functionLikeManipulator = $functionLikeManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add known return type to functions', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup): bool {
            return is_object($meetup);
        });
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
        return [Closure::class];
    }

    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion('7.0')) {
            return null;
        }

        if ($node->returnType) {
            return null;
        }

        /** @var Scope|null $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $staticReturnType = $this->functionLikeManipulator->resolveStaticReturnTypeInfo($node);
        if ($staticReturnType === null) {
            return null;
        }

        $returnTypeNode = $staticReturnType->getFqnTypeNode();
        if ($returnTypeNode === null) {
            return null;
        }

        $node->returnType = $returnTypeNode;

        return $node;
    }
}
