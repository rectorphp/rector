<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PHPStan\Type\ObjectType;
use Rector\CakePHP\Naming\CakePHPFullyQualifiedClassNameResolver;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/AppUsesTask.php
 * @see https://github.com/cakephp/upgrade/search?q=uses&unscoped_q=uses
 *
 * @see \Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\AppUsesStaticCallToUseStatementRectorTest
 */
final class AppUsesStaticCallToUseStatementRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CakePHP\Naming\CakePHPFullyQualifiedClassNameResolver
     */
    private $cakePHPFullyQualifiedClassNameResolver;
    public function __construct(\Rector\CakePHP\Naming\CakePHPFullyQualifiedClassNameResolver $cakePHPFullyQualifiedClassNameResolver)
    {
        $this->cakePHPFullyQualifiedClassNameResolver = $cakePHPFullyQualifiedClassNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change App::uses() to use imports', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
App::uses('NotificationListener', 'Event');

CakeEventManager::instance()->attach(new NotificationListener());
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Event\NotificationListener;

CakeEventManager::instance()->attach(new NotificationListener());
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class, \PhpParser\Node\Stmt\Namespace_::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $appUsesStaticCalls = $this->collectAppUseStaticCalls($node);
        if ($appUsesStaticCalls === []) {
            return null;
        }
        $this->nodeRemover->removeNodes($appUsesStaticCalls);
        $names = $this->resolveNamesFromStaticCalls($appUsesStaticCalls);
        $uses = $this->nodeFactory->createUsesFromNames($names);
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            $node->stmts = \array_merge($uses, $node->stmts);
            return $node;
        }
        return $this->refactorFile($node, $uses);
    }
    /**
     * @return StaticCall[]
     */
    private function collectAppUseStaticCalls(\PhpParser\Node $node) : array
    {
        /** @var StaticCall[] $appUsesStaticCalls */
        $appUsesStaticCalls = $this->betterNodeFinder->find($node, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            $callerType = $this->nodeTypeResolver->getType($node->class);
            if (!$callerType->isSuperTypeOf(new \PHPStan\Type\ObjectType('App'))->yes()) {
                return \false;
            }
            return $this->isName($node->name, 'uses');
        });
        return $appUsesStaticCalls;
    }
    /**
     * @param StaticCall[] $staticCalls
     * @return string[]
     */
    private function resolveNamesFromStaticCalls(array $staticCalls) : array
    {
        $names = [];
        foreach ($staticCalls as $staticCall) {
            $names[] = $this->createFullyQualifiedNameFromAppUsesStaticCall($staticCall);
        }
        return $names;
    }
    /**
     * @param Use_[] $uses
     */
    private function refactorFile(\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $fileWithoutNamespace, array $uses) : ?\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace
    {
        $hasNamespace = $this->betterNodeFinder->findFirstInstanceOf($fileWithoutNamespace, \PhpParser\Node\Stmt\Namespace_::class);
        // already handled above
        if ($hasNamespace !== null) {
            return null;
        }
        $hasDeclare = $this->betterNodeFinder->findFirstInstanceOf($fileWithoutNamespace, \PhpParser\Node\Stmt\Declare_::class);
        if ($hasDeclare !== null) {
            return $this->refactorFileWithDeclare($fileWithoutNamespace, $uses);
        }
        $fileWithoutNamespace->stmts = \array_merge($uses, $fileWithoutNamespace->stmts);
        return $fileWithoutNamespace;
    }
    private function createFullyQualifiedNameFromAppUsesStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : string
    {
        /** @var string $shortClassName */
        $shortClassName = $this->valueResolver->getValue($staticCall->args[0]->value);
        /** @var string $namespaceName */
        $namespaceName = $this->valueResolver->getValue($staticCall->args[1]->value);
        return $this->cakePHPFullyQualifiedClassNameResolver->resolveFromPseudoNamespaceAndShortClassName($namespaceName, $shortClassName);
    }
    /**
     * @param Use_[] $uses
     */
    private function refactorFileWithDeclare(\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $fileWithoutNamespace, array $uses) : \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace
    {
        $newStmts = [];
        foreach ($fileWithoutNamespace->stmts as $stmt) {
            $newStmts[] = $stmt;
            if ($stmt instanceof \PhpParser\Node\Stmt\Declare_) {
                foreach ($uses as $use) {
                    $newStmts[] = $use;
                }
                continue;
            }
        }
        return new \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace($newStmts);
    }
}
