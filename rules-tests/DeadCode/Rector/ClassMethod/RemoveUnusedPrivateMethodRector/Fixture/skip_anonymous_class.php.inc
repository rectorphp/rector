<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;

class SkipAnonymousClass
{
    public function createNodeVisitor(): NodeVisitor
    {
        return new class($this->classManipulator, $this->propertiesByClass) extends NodeVisitorAbstract {
            /**
             * @var VariableInfo[][]
             */
            private $propertiesByClass = [];

            /**
             * @var ClassManipulator
             */
            private $classManipulator;

            /**
             * @var ClassAnalyzer
             */
            private $classAnalyzer;

            /**
             * @param VariableInfo[][] $propertiesByClass
             */
            public function __construct(ClassManipulator $classManipulator, array $propertiesByClass, ClassAnalyzer $classAnalyzer)
            {
                $this->classManipulator = $classManipulator;
                $this->propertiesByClass = $propertiesByClass;
                $this->classAnalyzer = $classAnalyzer;
            }

            public function enterNode(Node $node): ?Node
            {
                if (! $node instanceof Class_ || $this->classAnalyzer->isAnonymousClass($node)) {
                    return null;
                }

                return $this->processClassNode($node);
            }

            private function processClassNode(Class_ $classNode): Class_
            {
                $variableInfos = $this->propertiesByClass[spl_object_hash($classNode)] ?? [];
                foreach ($variableInfos as $propertyInfo) {
                    $this->classManipulator->addConstructorDependency($classNode, $propertyInfo);
                }

                return $classNode;
            }
        };
    }
}
