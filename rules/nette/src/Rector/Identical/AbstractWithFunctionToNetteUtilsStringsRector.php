<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Nette\Contract\WithFunctionToNetteUtilsStringsRectorInterface;
use Rector\Nette\ValueObject\ContentExprAndNeedleExpr;

abstract class AbstractWithFunctionToNetteUtilsStringsRector extends AbstractRector implements WithFunctionToNetteUtilsStringsRectorInterface
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $contentExprAndNeedleExpr = $this->resolveContentExprAndNeedleExpr($node);
        if (! $contentExprAndNeedleExpr instanceof ContentExprAndNeedleExpr) {
            return null;
        }

        $staticCall = $this->nodeFactory->createStaticCall('Nette\Utils\Strings', $this->getMethodName(), [
            $contentExprAndNeedleExpr->getContentExpr(),
            $contentExprAndNeedleExpr->getNeedleExpr(),
        ]);

        if ($node instanceof NotIdentical) {
            return new BooleanNot($staticCall);
        }

        return $staticCall;
    }

    /**
     * @param Identical|NotIdentical $binaryOp
     */
    private function resolveContentExprAndNeedleExpr(BinaryOp $binaryOp): ?ContentExprAndNeedleExpr
    {
        if ($binaryOp->left instanceof Variable) {
            return $this->matchContentAndNeedleOfSubstrOfVariableLength($binaryOp->right, $binaryOp->left);
        }

        if ($binaryOp->right instanceof Variable) {
            return $this->matchContentAndNeedleOfSubstrOfVariableLength($binaryOp->left, $binaryOp->right);
        }

        return null;
    }
}
