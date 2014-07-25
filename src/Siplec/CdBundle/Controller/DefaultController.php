<?php
namespace Siplec\CdBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function connexionAction()
    {
    	if ($this->getUser()) {
	    	if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
		    	return $this->redirect($this->generateUrl('dossier'));
			}elseif (in_array('ROLE_GEST_OPERATEUR', $this->getUser()->getRoles())) {
		    	return $this->redirect($this->generateUrl('dossier'));
			}elseif (in_array('ROLE_OPERATEUR', $this->getUser()->getRoles())) {
		    	return $this->redirect($this->generateUrl('dossier'));
			}elseif (in_array('ROLE_SIPLEC', $this->getUser()->getRoles())) {
		    	return $this->redirect($this->generateUrl('courrier'));
			}    	
    	}else{
	    	return $this->redirect($this->generateUrl('fos_user_security_login'));
    	}
    }
}