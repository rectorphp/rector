<?php

declare(strict_types=1);

namespace Rector\PHPStan\Rector\Node;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\While_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPStan\Tests\Rector\Node\RemoveNonExistingVarAnnotationRector\RemoveNonExistingVarAnnotationRectorTest
 *
 * @see https://github.com/phpstan/phpstan/commit/d17e459fd9b45129c5deafe12bca56f30ea5ee99#diff-9f3541876405623b0d18631259763dc1
 */
final class RemoveNonExistingVarAnnotationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes non-existing @var annotations above the code', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function get()
    {
        /** @var Training[] $trainings */
        return $this->getData();
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function get()
    {
        return $this->getData();
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
        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $varTagValue = $phpDocInfo->getVarTagValue();
        if ($varTagValue === null) {
            return null;
        }

        $variableName = $varTagValue->variableName;
        if ($variableName === null) {
            return null;
        }

        $nodeContentWithoutPhpDoc = $this->printWithoutComments($node);

        // it's there
        if (Strings::match($nodeContentWithoutPhpDoc, '#' . preg_quote($variableName, '#') . '\b#')) {
            return null;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);

        return $node;
    }

    private function shouldSkip(Node $node): bool
    {
        return ! $node instanceof Assign
            && ! $node instanceof AssignRef
            && ! $node instanceof Foreach_
            && ! $node instanceof Static_
            && ! $node instanceof Echo_
            && ! $node instanceof Return_
            && ! $node instanceof Expression
            && ! $node instanceof Throw_
            && ! $node instanceof If_
            && ! $node instanceof While_
            && ! $node instanceof Switch_
            && ! $node instanceof Nop;
    }
}
