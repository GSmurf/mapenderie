<?php

namespace Siplec\UserBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siplec\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * utilisateur controller.
 *
 * @Route("/utilisateur")
 */
class UtilisateurController extends Controller
{

    /**
     * Lists all utilisateur entities.
     *
     * @Route("/", name="utilisateur")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
//     	$em = $this->getDoctrine()->getManager();
//     	$user = $em->getRepository('SiplecUserBundle:User')->find(1);
//     	$user->addRole('USER_ADMIN');
//     	$em->flush($user);
//     	$this->getRequest()->getSession()->getFlashBag()->set('notice', "$user trouvé !");
        return array();
    }

    /**
     * Finds and displays a utilisateur entity.
     *
     * @Route("/{id}", name="utilisateur_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SiplecUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Utilisateur entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }

    /**
     * Finds and displays a utilisateur entity.
     *
     * @Route("-ajax/", name="utilisateur_ajax")
     * @Method("GET")
     * @Template()
     */
    public function ajaxGetUtilisateursAction(){
    	$page = $_GET['page']; // get the requested page
    	$limit = $_GET['rows']; // get how many rows we want to have into the grid
    	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    	$sord = $_GET['sord']; // get the direction
    	
        $em = $this->getDoctrine()->getManager();
    	$count = $em->getRepository('SiplecUserBundle:User')->getNbResultats(array(), array($sidx=>$sord));
        $entities = $em->getRepository('SiplecUserBundle:User')->getSearch(array(), array($sidx=>$sord), $limit, $page);
    	if( $count >0 ) {
    		$total_pages = ceil($count/$limit);
    	} else {
    		$total_pages = 0;
    	}
    	if ($page > $total_pages) $page=$total_pages;
    	$start = $limit*$page - $limit; // do not put $limit*($page - 1)
    	
    	$responce = new \stdClass();
    	$responce->page = $page;
    	$responce->total = $total_pages;
    	$responce->records = $count;
    	$i=0;
    	foreach ($entities as $utilisateur) {
    		$responce->rows[$i]['id']=$utilisateur->getId();
    		$lastLogin = ($utilisateur->getLastLogin() != NULL)?$utilisateur->getLastLogin()->format('d/m/Y H:i'):"";
    		
    		$responce->rows[$i]['cell']=array(
					$utilisateur->getUserName(),
					$lastLogin,
					$utilisateur->getRoles (),
					$utilisateur->isEnabled ()
    		);
    		$i++;
    	}
    	return new JsonResponse($responce);
    }

    /**
     * Met à jour un utilisateur depuis jqgrid
     *
     * @Route("-update-ajax", name="utilisateur_update_ajax")
     * @Method("POST")
     * @Template()
     */
    public function ajaxUpdateUtilisateursAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $utilisateur = $em->getRepository('SiplecUserBundle:User')->find($_POST['id']);

        if (!$utilisateur) {
        	return new JsonResponse(array(false, "l'utilisateur $utilisateur n'a pas été trouvé", ""));
        }
		$utilisateur->setRoles(array($_POST['roles']));
		$em->flush(); // Sauvegarde les modifications en BD
        return new JsonResponse(array(true, "l'utilisateur $utilisateur à bien été modifié", "Congratulation."));
    }
}