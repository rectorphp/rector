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
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Application\FunctionLikeNodeCollector;

final class NodeCollectorNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    /**
     * @var FunctionLikeNodeCollector
     */
    private $functionLikeNodeCollector;

    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    public function __construct(
        FunctionLikeNodeCollector $functionLikeNodeCollector,
        ClassLikeNodeCollector $classLikeNodeCollector,
        ConstantNodeCollector $constantNodeCollector
    ) {
        $this->functionLikeNodeCollector = $functionLikeNodeCollector;
        $this->classLikeNodeCollector = $classLikeNodeCollector;
        $this->constantNodeCollector = $constantNodeCollector;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_ && ! $this->isClassAnonymous($node)) {
            $this->classLikeNodeCollector->addClass($node);
            return;
        }

        if ($node instanceof Interface_) {
            $this->classLikeNodeCollector->addInterface($node);
            return;
        }

        if ($node instanceof ClassConst) {
            $this->constantNodeCollector->addConstant($node);
            return;
        }

        if ($node instanceof Trait_) {
            $this->classLikeNodeCollector->addTrait($node);
            return;
        }

        if ($node instanceof ClassMethod) {
            $this->functionLikeNodeCollector->addMethod($node);
            return;
        }

        if ($node instanceof Function_) {
            $this->functionLikeNodeCollector->addFunction($node);
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
