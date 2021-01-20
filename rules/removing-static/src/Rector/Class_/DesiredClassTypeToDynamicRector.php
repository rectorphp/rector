<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\RemovingStatic\NodeAnalyzer\StaticCallPresenceAnalyzer;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\RemovingStatic\Tests\Rector\Class_\DesiredClassTypeToDynamicRector\DesiredClassTypeToDynamicRectorTest
 */
final class DesiredClassTypeToDynamicRector extends AbstractRector
{
    /**
     * @var class-string[]
     */
    private $classTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var StaticCallPresenceAnalyzer
     */
    private $staticCallPresenceAnalyzer;

    public function __construct(
        PropertyNaming $propertyNaming,
        StaticCallPresenceAnalyzer $staticCallPresenceAnalyzer,
        ParameterProvider $parameterProvider
    ) {
        $this->classTypes = $parameterProvider->provideArrayParameter(Option::TYPES_TO_REMOVE_STATIC_FROM);

        $this->propertyNaming = $propertyNaming;
        $this->staticCallPresenceAnalyzer = $staticCallPresenceAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change full static service, to dynamic one', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class AnotherClass
{
    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public static function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class AnotherClass
{
    /**
     * @var SomeClass
     */
    private $someClass;

    public fuction __construct(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }

    public function run()
    {
        SomeClass::someStatic();
    }
}

class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->classTypes as $classType) {
            // do not any dependencies to class itself
            if ($this->isObjectType($node, $classType)) {
                continue;
            }

            $this->completeDependencyToConstructorOnly($node, $classType);

            if ($this->staticCallPresenceAnalyzer->hasClassAnyMethodWithStaticCallOnType($node, $classType)) {
                $singleObjectType = new ObjectType($classType);
                $propertyExpectedName = $this->propertyNaming->fqnToVariableName($classType);
                $this->addConstructorDependencyToClass($node, $singleObjectType, $propertyExpectedName);

                return $node;
            }
        }

        return null;
    }

    private function completeDependencyToConstructorOnly(Class_ $class, string $classType): void
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return;
        }

        $hasStaticCall = $this->staticCallPresenceAnalyzer->hasMethodStaticCallOnType(
            $constructClassMethod,
            $classType
        );

        if (! $hasStaticCall) {
            return;
        }

        $propertyExpectedName = $this->propertyNaming->fqnToVariableName(new ObjectType($classType));

        if ($this->isTypeAlreadyInParamMethod($constructClassMethod, $classType)) {
            return;
        }

        $constructClassMethod->params[] = new Param(
            new Variable($propertyExpectedName),
            null,
            new FullyQualified($classType)
        );
    }

    private function isTypeAlreadyInParamMethod(ClassMethod $classMethod, string $classType): bool
    {
        foreach ($classMethod->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }

            if ($this->isName($param->type, $classType)) {
                return true;
            }
        }

        return false;
    }
}
