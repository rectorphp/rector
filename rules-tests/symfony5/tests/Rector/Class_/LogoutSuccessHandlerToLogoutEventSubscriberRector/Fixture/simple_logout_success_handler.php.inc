<?php

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector\Fixture;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

final class SimpleLogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    /**
      * @var HttpUtils
      */
    private $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }

    public function onLogoutSuccess(Request $request)
    {
        $response = $this->httpUtils->createRedirectResponse($request, 'some_url');
        return $response;
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector\Fixture;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

final class SimpleLogoutSuccessHandler implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
      * @var HttpUtils
      */
    private $httpUtils;

    public function __construct(HttpUtils $httpUtils)
    {
        $this->httpUtils = $httpUtils;
    }
    public function onLogout(\Symfony\Component\Security\Http\Event\LogoutEvent $logoutEvent): void
    {
        if ($logoutEvent->getResponse() !== null) {
            return;
        }
        $response = $this->httpUtils->createRedirectResponse($logoutEvent->getRequest(), 'some_url');
        $logoutEvent->setResponse($response);
    }
    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [\Symfony\Component\Security\Http\Event\LogoutEvent::class => ['onLogout', 64]];
    }
}

?>
