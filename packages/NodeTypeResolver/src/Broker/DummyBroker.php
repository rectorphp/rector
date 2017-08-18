<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Broker;

use PHPStan\Broker\Broker;
use PHPStan\Type\FileTypeMapper;
use Rector\NodeTypeResolver\Reflection\FunctionReflectionFactory;

final class DummyBroker extends Broker
{
    public function __construct(FunctionReflectionFactory $functionReflection, FileTypeMapper $fileTypeMapper)
    {
        parent::__construct([], [], [], [], $functionReflection, $fileTypeMapper);
    }
}
