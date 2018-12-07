<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

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
            foreach ($childrenClassLikes as $childrenClassLike) {
                $childrenClassMethod = $childrenClassLike->getMethod($methodName);
                if ($childrenClassMethod === null) {
                    continue;
                }

                if ($childrenClassMethod->returnType !== null) {
                    continue;
                }

                if ($returnTypeInfo->getTypeNode() instanceof NullableType) {
                    $childrenClassMethod->returnType = $returnTypeInfo->getTypeNode();
                } elseif ($returnTypeInfo->getTypeNode()->toString() === 'self') {
                    $childrenClassMethod->returnType = new FullyQualified($className);
                } elseif ($returnTypeInfo->getTypeNode()->toString() === 'parent') {
                    $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
                    $childrenClassMethod->returnType = new FullyQualified($parentClassName);
                } else {
                    $childrenClassMethod->returnType = $returnTypeInfo->getTypeNode();
                }

                // let the method now it was changed now
                $childrenClassMethod->returnType->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);

                // reprint the file
                /** @var SmartFileInfo $fileInfo */
                $fileInfo = $childrenClassMethod->getAttribute(Attribute::FILE_INFO);

                $this->filesToReprintCollector->addFileInfo($fileInfo);
            }
        }

        return $node;
    }
}
