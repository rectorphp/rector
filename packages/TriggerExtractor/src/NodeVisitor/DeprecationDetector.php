<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\NodeVisitor;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Rector\TriggerExtractor\Deprecation\DeprecationCollector;

final class DeprecationDetector extends NodeVisitorAbstract // @todo use : class aware node visitor
{
    /**
     * @var DeprecationCollector
     */
    private $deprecationCollector;

    public function __construct(DeprecationCollector $deprecationCollector)
    {
        $this->deprecationCollector = $deprecationCollector;
    }

    public function enterNode(Node $node): void
    {
        if (! $this->isTriggerErrorUserDeprecated($node)) {
            return;
        }

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $node;

        $messageNode = $funcCallNode->args[0]->value;
        $message = '';
        if ($messageNode instanceof Concat) {
            $message .= $this->processConcatNode($messageNode->left);
            $message .= $this->processConcatNode($messageNode->right);
        }

        $this->deprecationCollector->addDeprecation($message);
    }

    /**
     * This detects: "trigger_error(<some-content>, E_USER_DEPREDCATED)";
     */
    private function isTriggerErrorUserDeprecated(Node $node): bool
    {
        if (! $this->isFunctionWithName($node, 'trigger_error')) {
            return false;
        }

        /** @var FuncCall $node */
        if (count($node->args) !== 2) {
            return false;
        }

        /** @var Arg $secondArgumentNode */
        $secondArgumentNode = $node->args[1];
        if (! $secondArgumentNode->value instanceof ConstFetch) {
            return false;
        }

        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $secondArgumentNode->value;

        return $constFetchNode->name->toString() === 'E_USER_DEPRECATED';
    }

    private function processConcatNode(Node $node): string
    {
        if ($node instanceof Method) {
            $classMethodNode = $this->findParentOfType($node, ClassMethod::class);

            return $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        throw new Exception(sprintf(
            'Not implemented yet %s::%s()',
            __CLASS__,
            __METHOD__
        ));
    }

    private function findParentOfType(Node $node, string $type): Node
    {
        $parentNode = $node->getAttribute('parent');

        while (! is_a($parentNode, $type, true)) {
            $parentNode = $parentNode->getAttribute('parent');
        }

        return $parentNode;
    }

    private function isFunctionWithName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }


        return $node->name->toString() === $name;
    }
}
