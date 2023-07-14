<?php

namespace Rector\Symfony\Symfony63\Rector\Class_;

use Rector\Core\Rector\AbstractRector;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\UnionType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Symfony\NodeAnalyzer\ClassAnalyzer;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector\SignalableCommandInterfaceReturnTypeRectorTest
 */
final class SignalableCommandInterfaceReturnTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(ClassAnalyzer $classAnalyzer, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Return int or false from SignalableCommandInterface::handleSignal() instead of void', [new CodeSample(<<<'CODE_SAMPLE'
    public function handleSignal(int $signal): void
    {
    }
CODE_SAMPLE
, <<<'CODE_SAMPLE'

    public function handleSignal(int $signal): int|false
    {
        return false;
    }
CODE_SAMPLE
)]);
    }
    /**
     * @inheritDoc
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
        if (!$this->classAnalyzer->hasImplements($node, 'Symfony\\Component\\Console\\Command\\SignalableCommandInterface')) {
            return null;
        }
        $classMethod = $node->getMethod('handleSignal');
        if (!$classMethod instanceof ClassMethod) {
            return null;
        }
        $unionType = new UnionType([new IntegerType(), new ConstantBooleanType(\false)]);
        if ($this->parentClassMethodTypeOverrideGuard->shouldSkipReturnTypeChange($classMethod, $unionType)) {
            return null;
        }
        $classMethod->returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($unionType, TypeKind::RETURN);
        $classMethod->stmts[] = new Return_($this->nodeFactory->createFalse());
        return $node;
    }
}
