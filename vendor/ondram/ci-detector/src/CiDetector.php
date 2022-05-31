<?php

declare (strict_types=1);
namespace RectorPrefix20220531\OndraM\CiDetector;

use RectorPrefix20220531\OndraM\CiDetector\Ci\CiInterface;
use RectorPrefix20220531\OndraM\CiDetector\Exception\CiNotDetectedException;
/**
 * Unified way to get environment variables from current continuous integration server
 */
class CiDetector implements \RectorPrefix20220531\OndraM\CiDetector\CiDetectorInterface
{
    public const CI_APPVEYOR = 'AppVeyor';
    public const CI_AWS_CODEBUILD = 'AWS CodeBuild';
    public const CI_AZURE_PIPELINES = 'Azure Pipelines';
    public const CI_BAMBOO = 'Bamboo';
    public const CI_BITBUCKET_PIPELINES = 'Bitbucket Pipelines';
    public const CI_BUDDY = 'Buddy';
    public const CI_CIRCLE = 'CircleCI';
    public const CI_CODESHIP = 'Codeship';
    public const CI_CONTINUOUSPHP = 'continuousphp';
    public const CI_DRONE = 'drone';
    public const CI_GITHUB_ACTIONS = 'GitHub Actions';
    public const CI_GITLAB = 'GitLab';
    public const CI_JENKINS = 'Jenkins';
    public const CI_SOURCEHUT = 'SourceHut';
    public const CI_TEAMCITY = 'TeamCity';
    public const CI_TRAVIS = 'Travis CI';
    public const CI_WERCKER = 'Wercker';
    /** @var Env */
    private $environment;
    public final function __construct()
    {
        $this->environment = new \RectorPrefix20220531\OndraM\CiDetector\Env();
    }
    public static function fromEnvironment(\RectorPrefix20220531\OndraM\CiDetector\Env $environment) : self
    {
        $detector = new static();
        $detector->environment = $environment;
        return $detector;
    }
    public function isCiDetected() : bool
    {
        $ciServer = $this->detectCurrentCiServer();
        return $ciServer !== null;
    }
    public function detect() : \RectorPrefix20220531\OndraM\CiDetector\Ci\CiInterface
    {
        $ciServer = $this->detectCurrentCiServer();
        if ($ciServer === null) {
            throw new \RectorPrefix20220531\OndraM\CiDetector\Exception\CiNotDetectedException('No CI server detected in current environment');
        }
        return $ciServer;
    }
    /**
     * @return string[]
     */
    protected function getCiServers() : array
    {
        return [\RectorPrefix20220531\OndraM\CiDetector\Ci\AppVeyor::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\AwsCodeBuild::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\AzurePipelines::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Bamboo::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\BitbucketPipelines::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Buddy::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Circle::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Codeship::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Continuousphp::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Drone::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\GitHubActions::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\GitLab::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Jenkins::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\SourceHut::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\TeamCity::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Travis::class, \RectorPrefix20220531\OndraM\CiDetector\Ci\Wercker::class];
    }
    protected function detectCurrentCiServer() : ?\RectorPrefix20220531\OndraM\CiDetector\Ci\CiInterface
    {
        $ciServers = $this->getCiServers();
        foreach ($ciServers as $ciClass) {
            $callback = [$ciClass, 'isDetected'];
            if (\is_callable($callback)) {
                if ($callback($this->environment)) {
                    return new $ciClass($this->environment);
                }
            }
        }
        return null;
    }
}
