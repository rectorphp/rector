<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Isset_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector\IssetOnPropertyObjectToPropertyExistsRectorTest
 *
 * @changelog https://3v4l.org/TI8XL Change isset on property object to property_exists() with not null check
 */
final class IssetOnPropertyObjectToPropertyExistsRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change isset on property object to property_exists() and not null check', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        isset($this->x);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private $x;

    public function run(): void
    {
        property_exists($this, 'x') && $this->x !== null;
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
        return [Isset_::class];
    }
    /**
     * @param Isset_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $newNodes = [];
        foreach ($node->vars as $issetVar) {
            if (!$issetVar instanceof PropertyFetch) {
                continue;
            }
            // Ignore dynamically accessed properties ($o->$p)
            if ($issetVar->name instanceof Variable) {
                continue;
            }
            // has property PHP 7.4 type?
            if ($this->hasPropertyTypeDeclaration($issetVar)) {
                continue;
            }
            $propertyFetchName = $this->getName($issetVar->name);
            if ($propertyFetchName === null) {
                continue;
            }
            $propertyFetchVarType = $this->getType($issetVar->var);
            if ($propertyFetchVarType instanceof TypeWithClassName) {
                if ($propertyFetchVarType->getClassName() === 'stdClass') {
                    continue;
                }
                if (!$this->reflectionProvider->hasClass($propertyFetchVarType->getClassName())) {
                    continue;
                }
                $classReflection = $this->reflectionProvider->getClass($propertyFetchVarType->getClassName());
                if (!$classReflection->hasProperty($propertyFetchName) || $classReflection->isBuiltin()) {
                    $newNodes[] = $this->replaceToPropertyExistsWithNullCheck($issetVar->var, $propertyFetchName, $issetVar);
                } else {
                    $newNodes[] = $this->createNotIdenticalToNull($issetVar);
                }
            }
        }
        return $this->nodeFactory->createReturnBooleanAnd($newNodes);
    }
    private function replaceToPropertyExistsWithNullCheck(Expr $expr, string $property, PropertyFetch $propertyFetch) : BooleanAnd
    {
        $args = [new Arg($expr), new Arg(new String_($property))];
        $propertyExistsFuncCall = $this->nodeFactory->createFuncCall('property_exists', $args);
        return new BooleanAnd($propertyExistsFuncCall, $this->createNotIdenticalToNull($propertyFetch));
    }
    private function createNotIdenticalToNull(PropertyFetch $propertyFetch) : NotIdentical
    {
        return new NotIdentical($propertyFetch, $this->nodeFactory->createNull());
    }
    private function hasPropertyTypeDeclaration(PropertyFetch $propertyFetch) : bool
    {
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return \false;
        }
        return !$phpPropertyReflection->getNativeType() instanceof MixedType;
    }
}
