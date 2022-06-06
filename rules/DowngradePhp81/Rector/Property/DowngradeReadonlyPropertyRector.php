<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/readonly_properties_v2
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\DowngradeReadonlyPropertyRectorTest
 */
final class DowngradeReadonlyPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const TAGNAME = 'readonly';
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Param::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove "readonly" property type, add a "@readonly" tag instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public readonly string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @readonly
     */
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Property|Param $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->visibilityManipulator->isReadonly($node)) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Stmt\Property) {
            $this->addPhpDocTag($node);
        }
        $this->visibilityManipulator->removeReadonly($node);
        return $node;
    }
    private function addPhpDocTag(\PhpParser\Node\Stmt\Property $property) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if ($phpDocInfo->hasByName(self::TAGNAME)) {
            return;
        }
        $phpDocInfo->addPhpDocTagNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@' . self::TAGNAME, new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('')));
    }
}
