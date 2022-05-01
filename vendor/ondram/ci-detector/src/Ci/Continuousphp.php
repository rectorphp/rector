<?php

declare (strict_types=1);
namespace RectorPrefix20220501\OndraM\CiDetector\Ci;

use RectorPrefix20220501\OndraM\CiDetector\CiDetector;
use RectorPrefix20220501\OndraM\CiDetector\Env;
use RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic;
class Continuousphp extends \RectorPrefix20220501\OndraM\CiDetector\Ci\AbstractCi
{
    public static function isDetected(\RectorPrefix20220501\OndraM\CiDetector\Env $env) : bool
    {
        return $env->get('CONTINUOUSPHP') === 'continuousphp';
    }
    public function getCiName() : string
    {
        return \RectorPrefix20220501\OndraM\CiDetector\CiDetector::CI_CONTINUOUSPHP;
    }
    public function isPullRequest() : \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic
    {
        return \RectorPrefix20220501\OndraM\CiDetector\TrinaryLogic::createFromBoolean($this->env->getString('CPHP_PR_ID') !== '');
    }
    public function getBuildNumber() : string
    {
        return $this->env->getString('CPHP_BUILD_ID');
    }
    public function getBuildUrl() : string
    {
        return $this->env->getString('');
    }
    public function getCommit() : string
    {
        return $this->env->getString('CPHP_GIT_COMMIT');
    }
    public function getBranch() : string
    {
        $gitReference = $this->env->getString('CPHP_GIT_REF');
        return \preg_replace('~^refs/heads/~', '', $gitReference) ?? '';
    }
    public function getTargetBranch() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryName() : string
    {
        return '';
        // unsupported
    }
    public function getRepositoryUrl() : string
    {
        return $this->env->getString('');
    }
}
