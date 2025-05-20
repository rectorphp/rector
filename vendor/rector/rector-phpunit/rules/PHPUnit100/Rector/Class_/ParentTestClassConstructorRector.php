<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\Class_;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3975
 * @see https://github.com/sebastianbergmann/phpunit/commit/705874f1b867fd99865e43cb5eaea4e6d141582f
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\Class_\ParentTestClassConstructorRector\ParentTestClassConstructorRectorTest
 */
final class ParentTestClassConstructorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('PHPUnit\\Framework\\TestCase requires a parent constructor call', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeHelper extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeHelper extends TestCase
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        // it already has a constructor, skip as it might require specific tweaking
        if ($node->getMethod(MethodName::CONSTRUCT)) {
            return null;
        }
        $constructorClassMethod = new ClassMethod(MethodName::CONSTRUCT);
        $constructorClassMethod->flags |= Modifiers::PUBLIC;
        $constructorClassMethod->stmts[] = new Expression($this->createParentConstructorCall());
        $node->stmts = \array_merge([$constructorClassMethod], $node->stmts);
        return $node;
    }
    private function createParentConstructorCall() : StaticCall
    {
        $staticClassConstFetch = new ClassConstFetch(new Name('static'), 'class');
        return new StaticCall(new Name('parent'), MethodName::CONSTRUCT, [new Arg($staticClassConstFetch)]);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        if ($class->isAbstract()) {
            return \true;
        }
        if ($class->isAnonymous()) {
            return \true;
        }
        $className = $this->getName($class);
        // loaded automatically by PHPUnit
        if (\substr_compare((string) $className, 'Test', -\strlen('Test')) === 0) {
            return \true;
        }
        if (\substr_compare((string) $className, 'TestCase', -\strlen('TestCase')) === 0) {
            return \true;
        }
        return (bool) $class->getAttribute('hasRemovedFinalConstruct');
    }
}
