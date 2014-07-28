<?php
namespace Siplec\CdBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SiplecExceptionListener
{
    /**
     * Constructor
     *
     * @param Router $router The router
     * @param array $routes The routes for redirection
     */
    public function __construct(Router $router, Request $request)
    {
        $this->router = $router;
        $this->request = $request;
    }
    
	public function onKernelException(GetResponseForExceptionEvent $event)
	{            
		$exception = $event->getException();
		switch ($exception->getStatusCode()) {
			case 404:
				$message = "La page que vous demandé n'a pas été trouvée.";
				break;
			case 403:
				$message = "Vous n'avez pas accès à cette page.";
				break;
			default:
				$message = $exception->getMessage()." (".$exception->getCode().")";
			break;
		}
		
		$this->request->getSession()->getFlashBag()->set('error', $message);
		$url = $this->router->generate('_welcome');
		$event->setResponse(new RedirectResponse($url));
	}
}