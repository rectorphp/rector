<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\UnionType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\ParamTypeDeclarationRectorTest
 */
final class ParamTypeDeclarationRector extends AbstractTypeDeclarationRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change @param types to type declarations if not a BC-break', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if (empty($node->params)) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $paramWithTypes = $phpDocInfo->getParamTypesByName();
        // no tags, nothing to complete here
        if ($paramWithTypes === []) {
            return null;
        }

        $this->refactorParams($node, $paramWithTypes);

        return $node;
    }

    /**
     * Add typehint to all children
     * @param ClassMethod|Function_ $node
     */
    private function populateChildren(Node $node, int $position, Type $paramType): void
    {
        if (! $node instanceof ClassMethod) {
            return;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return;
        }

        $childrenClassLikes = $this->classLikeParsedNodesFinder->findClassesAndInterfacesByType($className);

        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if ($childClassLike instanceof Class_) {
                $usedTraits = $this->classLikeParsedNodesFinder->findUsedTraitsInClass($childClassLike);

                foreach ($usedTraits as $trait) {
                    $this->addParamTypeToMethod($trait, $position, $node, $paramType);
                }
            }

            $this->addParamTypeToMethod($childClassLike, $position, $node, $paramType);
        }
    }

    private function addParamTypeToMethod(
        ClassLike $classLike,
        int $position,
        ClassMethod $classMethod,
        Type $paramType
    ): void {
        $methodName = $this->getName($classMethod);

        $currentClassMethod = $classLike->getMethod($methodName);
        if ($currentClassMethod === null) {
            return;
        }

        if (! isset($currentClassMethod->params[$position])) {
            return;
        }

        $paramNode = $currentClassMethod->params[$position];

        // already has a type
        if ($paramNode->type !== null) {
            return;
        }

        $resolvedChildType = $this->resolveChildTypeNode($paramType);
        if ($resolvedChildType === null) {
            return;
        }

        // let the method know it was changed now
        $paramNode->type = $resolvedChildType;
        $paramNode->type->setAttribute(self::HAS_NEW_INHERITED_TYPE, true);

        $this->notifyNodeFileInfo($paramNode);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @param Type[] $paramWithTypes
     */
    private function refactorParams(FunctionLike $functionLike, array $paramWithTypes): void
    {
        foreach ($functionLike->params as $position => $param) {
            // to be sure
            $position = (int) $position;

            if ($this->shouldSkipParam($param)) {
                continue;
            }

            $hasNewType = false;

            $docParamType = $this->matchParamNodeFromDoc($paramWithTypes, $param);
            if ($docParamType === null) {
                continue;
            }

            $paramTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
                $docParamType,
                PHPStanStaticTypeMapper::KIND_PARAM
            );

            if ($paramTypeNode === null) {
                continue;
            }

            if ($functionLike instanceof ClassMethod && $this->vendorLockResolver->isParamChangeVendorLockedIn(
                $functionLike,
                $position
            )) {
                continue;
            }

            $this->changeParamNodeType($hasNewType, $paramTypeNode, $param);

            $this->populateChildren($functionLike, $position, $docParamType);
        }
    }

    private function isResourceType(Node $node): bool
    {
        if ($this->isName($node, 'resource')) {
            return true;
        }

        if ($node instanceof NullableType) {
            return $this->isResourceType($node->type);
        }

        return false;
    }

    /**
     * @param Identifier|Name|NullableType|UnionType $paramTypeNode
     */
    private function changeParamNodeType(bool $hasNewType, Node $paramTypeNode, Param $param): void
    {
        if ($hasNewType) {
            // should override - is it subtype?
            if ($param->type === null) {
                $param->type = $paramTypeNode;
            } elseif ($this->phpParserTypeAnalyzer->isSubtypeOf($paramTypeNode, $param->type)) {
                // allow override
                $param->type = $paramTypeNode;
            }

            return;
        }

        if ($this->isResourceType($paramTypeNode)) {
            // "resource" is valid phpdoc type, but it's not implemented in PHP
            $param->type = null;
        } else {
            $param->type = $paramTypeNode;
        }
    }

    /**
     * @param Type[] $paramWithTypes
     */
    private function matchParamNodeFromDoc(array $paramWithTypes, Param $param): ?Type
    {
        $paramNodeName = '$' . $this->getName($param->var);

        return $paramWithTypes[$paramNodeName] ?? null;
    }

    private function shouldSkipParam(Param $param): bool
    {
        if ($param->variadic) {
            return true;
        }

        // already set → skip
        if ($param->type === null) {
            return false;
        }

        $hasNewType = $param->type->getAttribute(self::HAS_NEW_INHERITED_TYPE, false);

        return ! $hasNewType;
    }
}
