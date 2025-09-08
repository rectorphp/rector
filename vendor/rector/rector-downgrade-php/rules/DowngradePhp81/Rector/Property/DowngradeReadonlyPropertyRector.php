<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/readonly_properties_v2
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\Property\DowngradeReadonlyPropertyRector\DowngradeReadonlyPropertyRectorTest
 */
final class DowngradeReadonlyPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @var string
     */
    private const TAGNAME = 'readonly';
    public function __construct(VisibilityManipulator $visibilityManipulator, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class, ClassMethod::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove "readonly" property type, add a "@readonly" tag instead', [new CodeSample(<<<'CODE_SAMPLE'
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
     * @param Property|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            if (!$this->visibilityManipulator->isReadonly($node)) {
                return null;
            }
            $this->addPhpDocTag($node);
            $this->visibilityManipulator->removeReadonly($node);
            return $node;
        }
        if (!$this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->params as $param) {
            if (!$this->visibilityManipulator->isReadonly($param)) {
                continue;
            }
            $this->addPhpDocTag($param);
            $this->visibilityManipulator->removeReadonly($param);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $node
     */
    private function addPhpDocTag($node): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasByName(self::TAGNAME)) {
            return;
        }
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@' . self::TAGNAME, new GenericTagValueNode('')));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
    }
}
