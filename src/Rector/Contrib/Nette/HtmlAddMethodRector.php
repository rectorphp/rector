<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;
use Rector\Deprecation\SetNames;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;

final class HtmlAddMethodRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $variableTypes = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var Node[]
     */
    private $fileNodes = [];

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    public function beforeTraverse(array $nodes): void
    {
        $this->fileNodes = $nodes;
    }

    public function isCandidate(Node $node): bool
    {
        $this->recordVariableTypes($node);

        if ($this->isOnTypeCall($node, Html::class)) {
            return true;
        }

        if ($this->isStaticCall($node)) {
            return true;
        }

        return false;
    }

    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->name->name = 'addHtml';

        return $node;
    }

    private function isStaticCall(Node $node): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if (! $node->name instanceof Identifier) {
            return false;
        }

        if ($node->class->getLast() !== 'Html') {
            return false;
        }

        if ((string) $node->name !== 'add') {
            return false;
        }

        return true;
    }

    /**
     * check elements type:
     * inspire: https://github.com/phpstan/phpstan/blob/355060961eb4a33304c66dfbfc0cd32870a0b9d4/src/Rules/Methods/CallMethodsRule.php#L74
     * https://github.com/phpstan/phpstan/blob/355060961eb4a33304c66dfbfc0cd32870a0b9d4/src/Analyser/Scope.php.
     */
    private function isOnTypeCall(Node $node, string $class): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $node->var instanceof Variable) {
            return false;
        }

        $type = $this->nodeTypeResolver->getTypeForNode($node->var, $this->fileNodes);
        dump($type->getType($node->var));
        die;

        $varNode = $node->var;

        dump($varNode); // get type of this node!!
        die;

        if (isset($this->variableTypes[$varNode->name])) {
            if ($this->variableTypes[$varNode->name] === $class) {
                return true;
            }
        }

        return false;
    }

    private function recordVariableTypes(Node $node): void
    {
        if ($node instanceof Expression && $node->expr instanceof Assign) {
            $assignNode = $node->expr;
            $variableName = $assignNode->var->name;
            if ($assignNode->expr instanceof New_) {
                $variableType = (string) $assignNode->expr->class;
                $this->variableTypes[$variableName] = $variableType;
            }

            // @todo: decouple to services... TypePesolver packages/...
                // add another type :)
//                dump($node->var);
//                dump($node->expr);
        }

        //        dump($node);
    }
}
