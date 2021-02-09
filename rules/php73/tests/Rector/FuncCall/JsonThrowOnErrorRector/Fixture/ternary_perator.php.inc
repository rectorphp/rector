<?php

namespace Rector\Php73\Tests\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

final class TernaryOperator
{
    public function run() : array
    {
        $campaigns = json_decode($input['campaigns'] ?? '', true) ?: [];
        
        return $campaigns;
    }
}
?>
