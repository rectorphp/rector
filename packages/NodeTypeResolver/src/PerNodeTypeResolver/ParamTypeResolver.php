<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Broker\Broker;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Reflection\ClassReflectionTypesResolver;

final class ParamTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var ClassReflectionTypesResolver
     */
    private $classReflectionTypesResolver;

    public function __construct(Broker $broker, ClassReflectionTypesResolver $classReflectionTypesResolver)
    {
        $this->broker = $broker;
        $this->classReflectionTypesResolver = $classReflectionTypesResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Param::class];
    }

    /**
     * @param Param $paramNode
     * @return string[]
     */
    public function resolve(Node $paramNode): array
    {
        $paramType = $paramNode->type->toString();

        if (! $this->broker->hasClass($paramType)) {
            return [$paramType];
        }

        $classReflection = $this->broker->getClass($paramType);
        return $this->classReflectionTypesResolver->resolve($classReflection);
    }
}
