<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Deprecated;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\FqsenResolver;

/**
 * This tag factory creates StandardTagFactory with only tags,
 * that are used. Preventing mailformed exceptions from default annotations.
 */
final class TagFactoryFactory
{
    /**
     * @var FqsenResolver
     */
    private $fqsenResolver;

    public function __construct(FqsenResolver $fqsenResolver)
    {
        $this->fqsenResolver = $fqsenResolver;
    }

    public function create(): TagFactory
    {
        return new StandardTagFactory($this->fqsenResolver, [
            'deprecated' => Deprecated::class,
            'method' => Method::class,
            'param' => Param::class,
            'return' => Return_::class,
            'see' => See::class,
            'var' => Var_::class,
        ]);
    }
}
