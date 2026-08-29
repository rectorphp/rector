<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\Collectors\Callable_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ClassTypeDetector;
use RectorPrefix202608\TomasVotruba\UnusedPublic\Configuration;
/**
 * @implements Collector<Expr\Array_, non-empty-array<string>|null>
 */
final class CallableTypeCollector implements Collector
{
    /**
     * @readonly
     */
    private Configuration $configuration;
    /**
     * @readonly
     */
    private ClassTypeDetector $classTypeDetector;
    public function __construct(Configuration $configuration, ClassTypeDetector $classTypeDetector)
    {
        $this->configuration = $configuration;
        $this->classTypeDetector = $classTypeDetector;
    }
    public function getNodeType(): string
    {
        return Array_::class;
    }
    /**
     * @param Expr\Array_ $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if (!$this->configuration->shouldCollectMethods()) {
            return null;
        }
        // skip calls in tests, as they are not used in production
        $classReflection = $scope->getClassReflection();
        if ($classReflection instanceof ClassReflection && $this->classTypeDetector->isTestClass($classReflection)) {
            return null;
        }
        $callableType = $scope->getType($node);
        if (!$callableType instanceof ConstantArrayType) {
            return null;
        }
        $classMethodReferences = [];
        foreach ($callableType->getConstantArrays() as $constantArrayType) {
            $typeAndMethodNames = $constantArrayType->findTypeAndMethodNames();
            if ($typeAndMethodNames === []) {
                continue;
            }
            foreach ($typeAndMethodNames as $typeAndMethodName) {
                if ($typeAndMethodName->isUnknown()) {
                    continue;
                }
                $objectClassNames = $typeAndMethodName->getType()->getObjectClassNames();
                foreach ($objectClassNames as $objectClassName) {
                    $classMethodReferences[] = $objectClassName . '::' . $typeAndMethodName->getMethod();
                }
            }
        }
        return $classMethodReferences;
    }
}
