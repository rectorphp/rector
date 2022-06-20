<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

final class SkipStrReplacePassArraySubject
{
    public function __construct(array $roles)
    {
        return str_replace('PRIVILEGE_', '', $roles);
    }
}
