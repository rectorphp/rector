<?php

declare(strict_types=1);

namespace Rector\Php81\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EnumFactory
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private BuilderFactory $builderFactory
    ) {
    }

    public function createFromClass(Class_ $class): Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName);

        // constant to cases
        foreach ($class->getConstants() as $classConst) {
            $enum->stmts[] = $this->createEnumCaseFromConst($classConst);
        }

        return $enum;
    }

    public function createFromSpatieClass(Class_ $class): Enum_
    {
        $shortClassName = $this->nodeNameResolver->getShortName($class);
        $enum = new Enum_($shortClassName);

        // constant to cases
        $classDocInfo = $this->phpDocInfoFactory->createFromNode($class);
        $docBlockMethods = $classDocInfo?->getTagsByName('@method');
        if ($docBlockMethods !== null) {
            foreach ($docBlockMethods as $docBlockMethod) {
                $enum->stmts[] = $this->createEnumCaseFromDocComment($docBlockMethod);
            }
        }

        return $enum;
    }

    private function createEnumCaseFromConst(ClassConst $classConst): EnumCase
    {
        $constConst = $classConst->consts[0];
        $enumCase = new EnumCase($constConst->name, $constConst->value);

        // mirror comments
        $enumCase->setAttribute(AttributeKey::PHP_DOC_INFO, $classConst->getAttribute(AttributeKey::PHP_DOC_INFO));
        $enumCase->setAttribute(AttributeKey::COMMENTS, $classConst->getAttribute(AttributeKey::COMMENTS));

        return $enumCase;
    }

    private function createEnumCaseFromDocComment(PhpDocTagNode $docTagNode): EnumCase
    {
        /**
         * @var MethodTagValueNode $nodeValue
         */
        $nodeValue = $docTagNode->value;
        return new EnumCase($nodeValue->methodName, $this->builderFactory->val($nodeValue->methodName));
    }
}
