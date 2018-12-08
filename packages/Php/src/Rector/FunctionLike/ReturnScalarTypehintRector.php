<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReturnScalarTypehintRector extends AbstractScalarTypehintRector
{
    /**
     * @var string[]
     */
    private $excludeClassMethodNames = ['__construct', '__destruct', '__clone'];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change @return types to scalar typehints if not a BC-break', [
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
        ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // already set → skip
        $hasNewType = false;
        if ($node->returnType) {
            $hasNewType = $node->returnType->getAttribute(self::HAS_NEW_INHERITED_TYPE, false);
            if ($hasNewType === false) {
                return null;
            }
        }

        $returnTypeInfo = $this->docBlockAnalyzer->getReturnTypeInfo($node);
        if ($returnTypeInfo === null) {
            return null;
        }

        if ($returnTypeInfo->getTypeNode() === null) {
            return null;
        }

        // skip excluded methods
        if ($node instanceof ClassMethod && $this->isNames($node, $this->excludeClassMethodNames)) {
            return null;
        }

        // @todo is it violation?

        if ($hasNewType) {
            // should override - is it subtype?
            $possibleOverrideNewReturnType = $returnTypeInfo->getTypeNode();

            if ($this->isSubtypeOf($possibleOverrideNewReturnType, $node->returnType)) {
                // allow override
                $node->returnType = $returnTypeInfo->getTypeNode();
            }
        } else {
            $node->returnType = $returnTypeInfo->getTypeNode();
        }

        // inherit typehint to all children
        if ($node instanceof ClassMethod) {
            /** @var string $className */
            $className = $node->getAttribute(Attribute::CLASS_NAME);

            $childrenClassLikes = $this->classLikeNodeCollector->findClassesAndInterfacesByType($className);

            /** @var string $methodName */
            $methodName = $node->getAttribute(Attribute::METHOD_NAME);

            // update their methods as well
            foreach ($childrenClassLikes as $childClassLike) {
                $childClassMethod = $childClassLike->getMethod($methodName);
                if ($childClassMethod === null) {
                    continue;
                }

                // already has a type
                if ($childClassMethod->returnType !== null) {
                    continue;
                }

                $childClassMethod->returnType = $this->resolveChildType($returnTypeInfo, $node, $childClassMethod);

                // let the method now it was changed now
                $childClassMethod->returnType->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);

                $this->notifyNodeChangeFileInfo($childClassMethod);
            }
        }

        return $node;
    }
}
