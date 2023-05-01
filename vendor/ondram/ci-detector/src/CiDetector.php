<?php

declare (strict_types=1);
namespace RectorPrefix202305\OndraM\CiDetector;

use RectorPrefix202305\OndraM\CiDetector\Ci\CiInterface;
use RectorPrefix202305\OndraM\CiDetector\Exception\CiNotDetectedException;
/**
 * Unified way to get environment variables from current continuous integration server
 */
class CiDetector implements CiDetectorInterface
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
        $this->environment = new Env();
    }
    public static function fromEnvironment(Env $environment) : self
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
    public function detect() : CiInterface
    {
        $ciServer = $this->detectCurrentCiServer();
        if ($ciServer === null) {
            throw new CiNotDetectedException('No CI server detected in current environment');
        }
        return $ciServer;
    }
    /**
     * @return string[]
     */
    protected function getCiServers() : array
    {
        return [Ci\AppVeyor::class, Ci\AwsCodeBuild::class, Ci\AzurePipelines::class, Ci\Bamboo::class, Ci\BitbucketPipelines::class, Ci\Buddy::class, Ci\Circle::class, Ci\Codeship::class, Ci\Continuousphp::class, Ci\Drone::class, Ci\GitHubActions::class, Ci\GitLab::class, Ci\Jenkins::class, Ci\SourceHut::class, Ci\TeamCity::class, Ci\Travis::class, Ci\Wercker::class];
    }
    protected function detectCurrentCiServer() : ?CiInterface
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
