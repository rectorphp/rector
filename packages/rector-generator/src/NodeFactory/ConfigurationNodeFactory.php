<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\PhpParser\Node\NodeFactory;

final class ConfigurationNodeFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     * @return Property[]
     */
    public function createProperties(array $ruleConfiguration): array
    {
        $properties = [];
        foreach (array_keys($ruleConfiguration) as $variable) {
            $variable = ltrim($variable, '$');
            $type = new ArrayType(new MixedType(), new MixedType());
            $properties[] = $this->nodeFactory->createPrivatePropertyFromNameAndType($variable, $type);
        }

        return $properties;
    }

    /**
     * @param array<string, mixed> $ruleConfiguration
     */
    public function createConstructorClassMethod(array $ruleConfiguration): ClassMethod
    {
        $classMethod = $this->nodeFactory->createPublicMethod('__construct');

        $assigns = [];
        $params = [];

        foreach ($ruleConfiguration as $variable => $values) {
            $variable = ltrim($variable, '$');

            $assign = $this->nodeFactory->createPropertyAssignment($variable);
            $assigns[] = new Expression($assign);

            $type = $this->resolveParamType($values);
            $param = $this->nodeFactory->createParamFromNameAndType($variable, $type);
            if ($type instanceof ArrayType) {
                // add default for fast testing property set mgaic purposes - @todo refactor for cleaner way later
                $param->default = new Array_([]);
            }

            $params[] = $param;
        }

        $classMethod->params = $params;
        $classMethod->stmts = $assigns;

        return $classMethod;
    }

    /**
     * @param mixed $value
     */
    private function resolveParamType($value): Type
    {
        if (is_array($value)) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if (is_string($value)) {
            return new StringType();
        }

        if (is_int($value)) {
            return new IntegerType();
        }

        if (is_float($value)) {
            return new FloatType();
        }

        throw new NotImplementedYetException();
    }
}
