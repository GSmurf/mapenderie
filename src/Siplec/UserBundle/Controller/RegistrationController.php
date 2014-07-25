<?php
namespace Siplec\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
// use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Siplec\UserBundle\Form\Type\RegistrationFormType;

class RegistrationController extends BaseController
{
	public function registerAction()
	{
// 		$form = $this->createForm(new RegistrationFormType(), new User(), array(
// 				'action' => $this->generateUrl('fos_user_security_check'),
// 				'method' => 'POST',
// 		));
		
		$form = $this->container->get('fos_user.registration.form');
		$form->add('submit', 'submit', array('label' => 'Enregistrer'));
		
		$formHandler = $this->container->get('fos_user.registration.form.handler');
		$confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');
	
		$process = $formHandler->process($confirmationEnabled);
		if ($process) {
			$user = $form->getData();
	
			$authUser = false;
			if ($confirmationEnabled) {
				$this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
				$route = 'fos_user_registration_check_email';
			} else {
				$authUser = true;
				$route = 'utilisateur';
			}
	
			$this->setFlash('notice', "l'utilisateur $user à été créé.");
			$url = $this->container->get('router')->generate($route);
			$response = new RedirectResponse($url);
	
			return $response;
		}
	
		return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
				'form' => $form->createView(),
		));
	}
	
}