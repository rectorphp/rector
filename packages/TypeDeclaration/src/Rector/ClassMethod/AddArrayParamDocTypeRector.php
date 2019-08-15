<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\Contract\ParamTypeInfererInterface;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\AddArrayParamDocTypeRectorTest
 */
final class AddArrayParamDocTypeRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var ParamTypeInfererInterface[]
     */
    private $paramTypeInferers = [];

    /**
     * @var ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;

    /**
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        array $paramTypeInferers,
        ParamPhpDocNodeFactory $paramPhpDocNodeFactory
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->paramTypeInferers = $paramTypeInferers;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds @param annotation to array parameters inferred from the rest of the code', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var Param[] $params */
        $params = (array) $node->params;

        if (count($params) === 0) {
            return null;
        }

        foreach ($params as $param) {
            if ($this->shouldSkipParam($param)) {
                return null;
            }

            $types = [];
            foreach ($this->paramTypeInferers as $paramTypeInferer) {
                $types = $paramTypeInferer->inferParam($param);
                if ($types !== []) {
                    break;
                }
            }

            // no types :/
            if ($types === []) {
                return null;
            }

            $this->docBlockManipulator->addTag($node, $this->paramPhpDocNodeFactory->create($types, $param));
        }

        return $node;
    }

    private function shouldSkipParam(Param $param): bool
    {
        // type missing at all
        if ($param->type === null) {
            return true;
        }

        // not an array type
        if (! $this->isName($param->type, 'array')) {
            return true;
        }

        // not an array type
        $paramStaticType = $this->getStaticType($param);
        if (! $paramStaticType instanceof ArrayType) {
            return true;
        }

        // is unknown type?
        if (! $paramStaticType->getIterableValueType() instanceof MixedType) {
            return true;
        }

        // is defined mixed[] explicitly
        /** @var MixedType $mixedType */
        $mixedType = $paramStaticType->getIterableValueType();

        return $mixedType->isExplicitMixed();
    }
}
