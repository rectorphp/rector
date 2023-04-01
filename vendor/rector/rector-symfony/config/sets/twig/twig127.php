<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Twig_Node', 'getLine', 'getTemplateLine'), new MethodCallRename('Twig_Node', 'getFilename', 'getTemplateName'), new MethodCallRename('Twig_Template', 'getSource', 'getSourceContext'), new MethodCallRename('Twig_Error', 'getTemplateFile', 'getTemplateName'), new MethodCallRename('Twig_Error', 'getTemplateName', 'setTemplateName')]);
};
