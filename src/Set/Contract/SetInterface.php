<?php

declare (strict_types=1);
namespace Rector\Set\Contract;

interface SetInterface
{
    public function getGroupName() : string;
    public function getName() : string;
    public function getSetFilePath() : string;
}
