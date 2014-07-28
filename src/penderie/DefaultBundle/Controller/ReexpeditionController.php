<?php
namespace Penderie\DefaultBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Penderie\DefaultBundle\Entity\Dossier;
use Penderie\DefaultBundle\Form\ReexpeditionType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Réexpédition controller.
 *
 * @Route("/reexpedition")
 */
class ReexpeditionController extends Controller
{
    /**
     * Lists all Dossier entities.
     *
     * @Route("", name="reexpedition")
     * @Method("POST|GET")
     * @Template("PenderieDefaultBundle:Reexpedition:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        // Récupération des différents statut utilisé pour le traitement des réexpédition
        $statutRefuse = $em->getRepository('PenderieDefaultBundle:StatutDossier')->find(Dossier::STATUT_REFUSE);
        $statutRefuseAuto = $em->getRepository('PenderieDefaultBundle:StatutDossier')->find(Dossier::STATUT_REFUSE_AUTOMATIQUE);
        $statutValide = $em->getRepository('PenderieDefaultBundle:StatutDossier')->find(Dossier::STATUT_VALIDE);
        $dossier = new Dossier();
        
        // Récupération des informations sauvegarder en session pour les reutilisés
        if($request->getSession()->get('refRecall') != null){
        	$dossier->setRefRecall($request->getSession()->get('refRecall'));
        }
        
        // Génération du formulaire de réexpédition
        $formReexpedition = $this->createCreateForm($dossier);
        $formReexpedition->handleRequest($request);
        
		if ($formReexpedition->isValid()) {
        	// Si plusieurs dossiers existent prends le dernier dossier
        	$dossierSelection = $em->getRepository('PenderieDefaultBundle:Dossier')->getLastDossierByNumDossier($dossier->getNumDossier());
        	
        	// Vérifie que le dossier existe
        	if ($dossierSelection) {
        		// Vérifie si le dossier est Valide, Refusé ou Refusé automatique
	        	if(in_array($dossierSelection->getStatut(), array($statutValide, $statutRefuse, $statutRefuseAuto))){
		        	// Vérifie si le dossier n'a pas de date de réexpédition et de référence Recall
	        		if($dossierSelection->getDateReexpedition() == null && $dossierSelection->getRefRecall() == null){
	        			// Si toutes les conditions sont ok alors ajoute les informations de reexpédition à toute la famille
	        			$em->getRepository('PenderieDefaultBundle:Dossier')->setReexpeditionFamily($dossier, $this->getUser());
			            $request->getSession()->getFlashBag()->set('notice', "Le $dossier est considéré comme réexpédié");
		        	}else{
			            $request->getSession()->getFlashBag()->set('notice', "Le $dossier à déjà été réexpédié le ".$dossierSelection->getDateReexpedition()->format('d/m/Y à H:i').", via la référence RECALL : ".$dossierSelection->getRefRecall());
		        	}
	        	}else{
		            $request->getSession()->getFlashBag()->set('error', "Le $dossier n'est pas au statut ".$statutValide.", ".$statutRefuse." ou ".$statutRefuseAuto);
	        	}
        	}else{
	            $request->getSession()->getFlashBag()->set('error', "Le numéro de dossier \"".$dossier->getNumDossier()."\" n'a pas été trouvé.");
        	}
        	
        	// Sauvegarde ces informations pour les reutilisés si on créé à la chaine
        	$request->getSession()->set('refRecall', $dossier->getRefRecall());
            return $this->redirect($this->generateUrl('reexpedition'));
        }

        
        
        return array(
            'entity' => $dossier,
            'form'   => $formReexpedition->createView()
        );
    }

    /**
    * Creates a form to create a Dossier entity.
    *
    * @param Dossier $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Dossier $entity)
    {
        $form = $this->createForm(new ReexpeditionType(), $entity, array(
            'action' => $this->generateUrl('reexpedition'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Ajouter'));

        return $form;
    }
    
    /**
     * Finds and displays a Courrier entity.
     *
     * @Route("-ajax", name="reexpedition_ajax")
     * @Method("GET")
     * @Template()
     */
    public function ajaxAction(){
    	$em = $this->getDoctrine()->getManager();
    	
    	$page = $_GET['page']; // get the requested page
    	$limit = $_GET['rows']; // get how many rows we want to have into the grid
    	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    	$sord = $_GET['sord']; // get the direction
    
    	$criteria = array();
    	if(isset($_GET['numDossier'])) $criteria['numDossier']=$_GET['numDossier'];
    	if(isset($_GET['refRecall'])) $criteria['refRecall']=$_GET['refRecall'];
    	
    	$count = $em->getRepository('PenderieDefaultBundle:Dossier')->getNbResultatsReexpedition($criteria, $this->getUser());
    	$entities = $em->getRepository('PenderieDefaultBundle:Dossier')->getSearchReexpedition($criteria, $this->getUser(), array($sidx=>$sord), $limit, $page);

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
    	foreach ($entities as $dossier) {
    		$responce->rows[$i]['id']=$dossier->getId();
    		$responce->rows[$i]['cell']=array(
    				$dossier->getRefRecall(),
    				$dossier->getNumDossier(),
    				($dossier->getDateReexpedition())?$dossier->getDateReexpedition()->format('d/m/Y'):"",
    		);
    		$i++;
    	}
    	return new JsonResponse($responce);
    }
}