<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Php\ReturnTypeInfo;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReturnTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    /**
     * @var string[]
     */
    private $excludeClassMethodNames = ['__construct', '__destruct', '__clone'];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change @return types and type from static analysis to type declarations if not a BC-break',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    /**
     * @return int
     */
    public function getCount()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    /**
     * @return int
     */
    public function getCount(): int
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip excluded methods
        if ($node instanceof ClassMethod && $this->isNames($node, $this->excludeClassMethodNames)) {
            return null;
        }

        // already set → skip
        $hasNewType = false;
        if ($node->returnType) {
            $hasNewType = $node->returnType->getAttribute(self::HAS_NEW_INHERITED_TYPE, false);
            if ($hasNewType === false) {
                return null;
            }
        }

        $returnTypeInfo = $this->resolveReturnType($node);
        if ($returnTypeInfo === null) {
            return null;
        }

        // this could be void
        if ($returnTypeInfo->getTypeNode() === null) {
            if ($returnTypeInfo->getTypeCount() !== 0) {
                return null;
            }

            // use
            $returnTypeInfo = $this->functionLikeMaintainer->resolveStaticReturnTypeInfo($node);

            if ($returnTypeInfo === null) {
                return null;
            }
        }

        // @todo is it violation?
        if ($hasNewType) {
            // should override - is it subtype?
            $possibleOverrideNewReturnType = $returnTypeInfo->getTypeNode();

            if ($possibleOverrideNewReturnType !== null) {
                if ($node->returnType) {
                    if ($this->isSubtypeOf($possibleOverrideNewReturnType, $node->returnType)) {
                        // allow override
                        $node->returnType = $returnTypeInfo->getTypeNode();
                    }
                } else {
                    $node->returnType = $returnTypeInfo->getTypeNode();
                }
            }
        } else {
            $node->returnType = $returnTypeInfo->getTypeNode();
        }

        $this->populateChildren($node, $returnTypeInfo);

        return $node;
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    private function resolveReturnType(FunctionLike $node): ?ReturnTypeInfo
    {
        $docReturnTypeInfo = $this->docBlockAnalyzer->getReturnTypeInfo($node);
        $codeReturnTypeInfo = $this->functionLikeMaintainer->resolveStaticReturnTypeInfo($node);

        // code has priority over docblock
        if ($docReturnTypeInfo === null) {
            return $codeReturnTypeInfo;
        }

        if ($codeReturnTypeInfo && $codeReturnTypeInfo->getTypeNode()) {
            return $codeReturnTypeInfo;
        }

        return $docReturnTypeInfo;
    }

    private function addReturnTypeToMethod(
        ClassLike $classLikeNode,
        string $methodName,
        Node $node,
        ReturnTypeInfo $returnTypeInfo
    ): void {
        $classMethod = $classLikeNode->getMethod($methodName);
        if ($classMethod === null) {
            return;
        }

        // already has a type
        if ($classMethod->returnType !== null) {
            return;
        }

        $resolvedChildType = $this->resolveChildType($returnTypeInfo, $node, $classMethod);
        if ($resolvedChildType === null) {
            return;
        }

        $resolvedChildType = $this->resolveChildType($returnTypeInfo, $node, $classMethod);
        if ($resolvedChildType === null) {
            return;
        }

        $classMethod->returnType = $resolvedChildType;

        // let the method now it was changed now
        $classMethod->returnType->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);

        $this->notifyNodeChangeFileInfo($classMethod);
    }

    /**
     * Add typehint to all children
     */
    private function populateChildren(Node $node, ReturnTypeInfo $returnTypeInfo): void
    {
        if (! $node instanceof ClassMethod) {
            return;
        }

        /** @var string $methodName */
        $methodName = $this->getName($node);

        /** @var string $className */
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        $childrenClassLikes = $this->classLikeNodeCollector->findClassesAndInterfacesByType($className);

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if (! $childClassLike instanceof Class_) {
                continue;
            }

            $usedTraits = $this->classLikeNodeCollector->findUsedTraitsInClass($childClassLike);
            foreach ($usedTraits as $trait) {
                $this->addReturnTypeToMethod($trait, $methodName, $node, $returnTypeInfo);
            }

            $this->addReturnTypeToMethod($childClassLike, $methodName, $node, $returnTypeInfo);
        }
    }
}
