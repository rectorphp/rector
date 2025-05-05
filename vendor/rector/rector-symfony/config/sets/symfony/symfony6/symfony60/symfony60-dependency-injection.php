<?php

declare (strict_types=1);
namespace RectorPrefix202505;

use PhpParser\Node\Scalar\String_;
use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReplaceServiceArgumentRector::class, [new ReplaceServiceArgument('Psr\\Container\\ContainerInterface', new String_('service_container')), new ReplaceServiceArgument('Symfony\\Component\\DependencyInjection\\ContainerInterface', new String_('service_container'))]);
};
