<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareFullyQualifiedIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\MakeGetComponentAssignAnnotatedRectorTest
 */
final class MakeGetComponentAssignAnnotatedRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add doc type for magic $control->getComponent(...) assign', [
            new CodeSample(
                <<<'PHP'
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('another');
    }
}

final class ExternalControl extends Control
{
    public function createComponentAnother(): AnotherControl
    {
        return new AnotherControl();
    }
}

final class AnotherControl extends Control
{
}
PHP
,
                <<<'PHP'
use Nette\Application\UI\Control;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}

final class ExternalControl extends Control
{
    public function createComponentAnother(): AnotherControl
    {
        return new AnotherControl();
    }
}

final class AnotherControl extends Control
{
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isOnClassMethodCall($node->expr, 'Nette\Application\UI\Control', 'getComponent')) {
            return null;
        }

        if (! $node->var instanceof Variable) {
            return null;
        }

        $variableName = $this->getName($node->var);
        if ($variableName === null) {
            return null;
        }

        $nodeVar = $this->getObjectType($node->var);
        if (! $nodeVar instanceof MixedType) {
            return null;
        }

        /** @var MethodCall $methodCall */
        $methodCall = $node->expr;
        $createComponentClassMethodReturnType = $this->resolveGetComponentReturnType($methodCall);
        if (! $createComponentClassMethodReturnType instanceof TypeWithClassName) {
            return null;
        }

        $phpDocInfo = $this->resolvePhpDocInfo($node);
        if ($phpDocInfo->getVarTagValue() !== null) {
            return null;
        }

        $attributeAwareFullyQualifiedIdentifierTypeNode = new AttributeAwareFullyQualifiedIdentifierTypeNode(
            $createComponentClassMethodReturnType->getClassName()
        );
        $varTagValueNode = new VarTagValueNode(
            $attributeAwareFullyQualifiedIdentifierTypeNode,
            '$' . $variableName,
            ''
        );
        $phpDocInfo->addTagValueNode($varTagValueNode);

        return $node;
    }

    private function resolvePhpDocInfo(Assign $assign): PhpDocInfo
    {
        $currentStmt = $assign->getAttribute(AttributeKey::CURRENT_STATEMENT);
        if ($currentStmt instanceof Expression) {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $currentStmt->getAttribute(AttributeKey::PHP_DOC_INFO);
        } else {
            /** @var PhpDocInfo|null $phpDocInfo */
            $phpDocInfo = $assign->getAttribute(AttributeKey::PHP_DOC_INFO);
        }

        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($assign);
        }

        $phpDocInfo->makeSingleLined();

        return $phpDocInfo;
    }

    private function resolveGetComponentReturnType(MethodCall $methodCall): Type
    {
        /** @var Scope|null $scope */
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return new MixedType();
        }

        $calledOnType = $scope->getType($methodCall->var);

        if (count($methodCall->args) !== 1) {
            return new MixedType();
        }

        $firstArgumentValue = $methodCall->args[0]->value;
        if (! $firstArgumentValue instanceof String_) {
            return new MixedType();
        }

        $componentName = $this->getValue($firstArgumentValue);
        $methodName = sprintf('createComponent%s', ucfirst($componentName));

        if (! $calledOnType instanceof ObjectType) {
            return new MixedType();
        }

        if (! $calledOnType->hasMethod($methodName)->yes()) {
            return new MixedType();
        }

        // has method
        $method = $calledOnType->getMethod($methodName, $scope);

        return ParametersAcceptorSelector::selectSingle($method->getVariants())->getReturnType();
    }
}
