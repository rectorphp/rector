<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\SingleToManyMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ClassMethod\SingleToManyMethodRector\SingleToManyMethodRectorTest
 */
final class SingleToManyMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @api
     * @deprecated
     * @var string
     */
    public const SINGLES_TO_MANY_METHODS = 'singles_to_many_methods';
    /**
     * @var SingleToManyMethod[]
     */
    private $singleToManyMethods = [];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change method that returns single value to multiple values', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getNode(): string
    {
        return 'Echo_';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}
CODE_SAMPLE
, [new \Rector\Transform\ValueObject\SingleToManyMethod('SomeClass', 'getNode', 'getNodes')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        foreach ($this->singleToManyMethods as $singleToManyMethod) {
            if (!$this->isObjectType($classLike, $singleToManyMethod->getObjectType())) {
                continue;
            }
            if (!$this->isName($node, $singleToManyMethod->getSingleMethodName())) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($singleToManyMethod->getManyMethodName());
            $this->keepOldReturnTypeInDocBlock($node);
            $node->returnType = new \PhpParser\Node\Identifier('array');
            $this->wrapReturnValueToArray($node);
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $singleToManyMethods = $configuration[self::SINGLES_TO_MANY_METHODS] ?? $configuration;
        \RectorPrefix20220209\Webmozart\Assert\Assert::allIsAOf($singleToManyMethods, \Rector\Transform\ValueObject\SingleToManyMethod::class);
        $this->singleToManyMethods = $singleToManyMethods;
    }
    private function keepOldReturnTypeInDocBlock(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        // keep old return type in the docblock
        $oldReturnType = $classMethod->returnType;
        if ($oldReturnType === null) {
            return;
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($oldReturnType);
        $arrayType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), $staticType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $arrayType);
    }
    private function wrapReturnValueToArray(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) {
            if (!$node instanceof \PhpParser\Node\Stmt\Return_) {
                return null;
            }
            $node->expr = $this->nodeFactory->createArray([$node->expr]);
            return null;
        });
    }
}
