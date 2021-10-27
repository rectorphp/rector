<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SkipComplexCondition
{
    public function run(string $fileName, string $dir)
    {
        $ch = curl_init();
        file_exists($fileName) && is_resource($ch) || $ch instanceof \CurlHandle && is_dir($dir);
    }
}
