<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeContainer\ParsedNodesByType;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_ && ! $this->isClassAnonymous($node)) {
            $this->parsedNodesByType->addClass($node);
            return;
        }

        if ($node instanceof Interface_) {
            $this->parsedNodesByType->addInterface($node);
            return;
        }

        if ($node instanceof ClassConst) {
            $this->parsedNodesByType->addClassConstant($node);
            return;
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            $this->parsedNodesByType->addClassConstantFetch($node);
            return;
        }

        if ($node instanceof Trait_) {
            $this->parsedNodesByType->addTrait($node);
            return;
        }

        if ($node instanceof ClassMethod) {
            $this->parsedNodesByType->addMethod($node);
            return;
        }

        if ($node instanceof Function_) {
            $this->parsedNodesByType->addFunction($node);
            return;
        }
    }

    private function isClassAnonymous(Class_ $classNode): bool
    {
        if ($classNode->isAnonymous()) {
            return true;
        }

        if ($classNode->name === null) {
            return false;
        }

        // PHPStan polution
        return Strings::startsWith($classNode->name->toString(), 'AnonymousClass');
    }
}
