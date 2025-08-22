<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\CodeQuality\Rector\Class_\ConvertStaticToSelfRector;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Use ConvertStaticToSelfRector instead
 */
final class ConvertStaticPrivateConstantToSelfRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ConvertStaticToSelfRector $convertStaticToSelfRector;
    public function __construct(ConvertStaticToSelfRector $convertStaticToSelfRector)
    {
        $this->convertStaticToSelfRector = $convertStaticToSelfRector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces static::* constant access with self::* for private constants and in final classes.', [new CodeSample(<<<'CODE_SAMPLE'
class Foo
{
    private const BAR = 'bar';
    public const BAZ = 'baz';

    public function run()
    {
        $bar = static::BAR;
        $baz = static::BAZ;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Foo
{
    private const BAR = 'bar';
    public const BAZ = 'baz';

    public function run()
    {
        $bar = self::BAR;
        $baz = static::BAZ;
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
