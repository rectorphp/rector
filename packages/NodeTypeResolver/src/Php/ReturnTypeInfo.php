<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

final class ReturnTypeInfo extends AbstractTypeInfo
{
    /**
     * @var string[]
     */
    protected $typesToRemove = ['resource', 'real'];
}
