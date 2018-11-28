<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ParamScalarTypehintRector extends AbstractScalarTypehintRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change @param types to scalar typehints if not a BC-break', [
            new CodeSample(
                <<<'CODE_SAMPLE'
<?php

class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change($number)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
<?php

class ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}

final class ChildClass extends ParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change(int $number)
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
        if (empty($node->params)) {
            return null;
        }

        $paramTagInfos = $this->docBlockAnalyzer->getParamTypeInfos($node);

        // no tags, nothing to complete here
        if ($paramTagInfos === []) {
            return null;
        }

        foreach ($node->params as $i => $paramNode) {
            // already set → skip
            $hasNewType = false;
            if ($paramNode->type) {
                $hasNewType = $paramNode->type->getAttribute(self::HAS_NEW_INHERITED_TYPE, false);
                if ($hasNewType === false) {
                    continue;
                }
            }

            $paramNodeName = $this->getName($paramNode->var);

            // no info about it
            if (! isset($paramTagInfos[$paramNodeName])) {
                continue;
            }

            $paramTagInfo = $paramTagInfos[$paramNodeName];

            if ($paramTagInfo->isTypehintAble() === false) {
                continue;
            }

            if ($node instanceof ClassMethod && $this->isChangeVendorLockedIn($node)) {
                continue;
            }

            if ($hasNewType) {
                // should override - is it subtype?
                $possibleOverrideNewReturnType = $paramTagInfo->getTypeNode();

                if ($this->isSubtypeOf($possibleOverrideNewReturnType, $paramNode->type)) {
                    // allow override
                    $paramNode->type = $paramTagInfo->getTypeNode();
                }
            } else {
                $paramNode->type = $paramTagInfo->getTypeNode();
            }

            // inherit typehint to all children
            if ($node instanceof ClassMethod) {
                /** @var string $className */
                $className = $node->getAttribute(Attribute::CLASS_NAME);
                $childrenClasses = $this->classLikeNodeCollector->findChildrenOfClass($className);

                /** @var string $methodName */
                $methodName = $node->getAttribute(Attribute::METHOD_NAME);

                // update their methods as well
                foreach ($childrenClasses as $childrenClass) {
                    $childrenClassMethod = $childrenClass->getMethod($methodName);
                    if ($childrenClassMethod) {
                        if (! isset($childrenClassMethod->params[$i])) {
                            continue;
                        }

                        $childrenClassMethodParam = $childrenClassMethod->params[$i];

                        if ($childrenClassMethodParam->type === null) {
                            $childrenClassMethodParam->type = $paramTagInfo->getTypeNode();
                            // let the method know it was changed now
                            $childrenClassMethodParam->type->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);
                        }
                    }
                }
            }
        }

        return $node;
    }
}
