<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Cache\Cache;
use PHPStan\Parser\FunctionCallStatementFinder;
use PHPStan\Parser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflectionFactory as PhpMethodReflectionFactoryInterface;
use PHPStan\Type\Type;
use ReflectionMethod;

final class PhpMethodReflectionFactory implements PhpMethodReflectionFactoryInterface
{
    /**
     * @var Broker
     */
    private $broker;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FunctionCallStatementFinder
     */
    private $functionCallStatementFinder;

    /**
     * @var Cache
     */
    private $cache;

    public function __construct(
        Parser $parser,
        FunctionCallStatementFinder $functionCallStatementFinder,
        Cache $cache
    ) {
        $this->parser = $parser;
        $this->functionCallStatementFinder = $functionCallStatementFinder;
        $this->cache = $cache;
    }

    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    /**
     * @param Type[] $phpDocParameterTypes
     */
    public function create(
        ClassReflection $declaringClass,
        ReflectionMethod $reflectionMethod,
        array $phpDocParameterTypes,
        ?Type $phpDocReturnType = null
    ): PhpMethodReflection {
        return new PhpMethodReflection(
            $declaringClass,
            $reflectionMethod,
            $this->broker,
            $this->parser,
            $this->functionCallStatementFinder,
            $this->cache,
            $phpDocParameterTypes,
            $phpDocReturnType
        );
    }
}
