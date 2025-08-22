<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use ConvertStaticToSelfRector instead
 */
final class StaticToSelfStaticMethodCallOnFinalClassRector extends AbstractRector
{
    /**
     * @readonly
     */
    private \Rector\CodeQuality\Rector\Class_\ConvertStaticToSelfRector $convertStaticToSelfRector;
    public function __construct(\Rector\CodeQuality\Rector\Class_\ConvertStaticToSelfRector $convertStaticToSelfRector)
    {
        $this->convertStaticToSelfRector = $convertStaticToSelfRector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `static::methodCall()` to `self::methodCall()` on final class', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function d()
    {
        echo static::run();
    }

    private static function run()
    {
        echo 'test';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function d()
    {
        echo self::run();
    }

    private static function run()
    {
        echo 'test';
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
    public function refactor(Node $node) : ?Class_
    {
        return $this->convertStaticToSelfRector->refactor($node);
    }
}
