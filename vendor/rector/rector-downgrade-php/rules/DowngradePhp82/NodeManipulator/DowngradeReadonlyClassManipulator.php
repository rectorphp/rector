<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\ValueObject\MethodName;
final class DowngradeReadonlyClassManipulator
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function process(Class_ $class) : ?Class_
    {
        if (!$this->visibilityManipulator->isReadonly($class)) {
            return null;
        }
        $this->visibilityManipulator->removeReadonly($class);
        $this->makePropertiesReadonly($class);
        $this->makePromotedPropertiesReadonly($class);
        return $class;
    }
    private function makePropertiesReadonly(Class_ $class) : void
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isReadonly()) {
                continue;
            }
            /**
             * It technically impossible that readonly class has:
             *
             *  - non-typed property
             *  - static property
             *
             * but here to ensure no flip-flop when using direct rule for multiple rules applied
             */
            if ($property->type === null) {
                continue;
            }
            if ($property->isStatic()) {
                continue;
            }
            $this->visibilityManipulator->makeReadonly($property);
        }
    }
    private function makePromotedPropertiesReadonly(Class_ $class) : void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return;
        }
        foreach ($classMethod->getParams() as $param) {
            if ($this->visibilityManipulator->isReadonly($param)) {
                continue;
            }
            /**
             * not property promotion, just param
             */
            if ($param->flags === 0) {
                continue;
            }
            /**
             * also not typed, just param
             */
            if ($param->type === null) {
                continue;
            }
            $this->visibilityManipulator->makeReadonly($param);
        }
    }
}
