<?php
namespace Penderie\DefaultBundle\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Penderie\DefaultBundle\Entity\Courrier;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Courrier controller.
 *
 * @Route("/courrier")
 */
class CourrierController extends Controller
{

    /**
     * Lists all Courrier entities.
     *
     * @Route("", name="courrier")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array('saisie'=>false,
        			'saisieUrl'=>0,
    				'titre'=> "Consultation courriers");
    }

    /**
     * Lists all Courrier entities.
     *
     * @Route("-saisie", name="courrier_new")
     * @Method("GET")
     * @Template()
     */
    public function saisieAction()
    {
    	return $this->render(
    			'PenderieDefaultBundle:Courrier:index.html.twig',
    			array('saisie'=>true,
        			'saisieUrl'=>1,
    				'titre'=> "Saisie courriers")
    	);
    }

    /**
     * Telecharge un fichier excel des courriers non reconnus
     *
     * @Route("-non-reconnus", name="courrier_export_non_reconnus")
     * @Method("GET")
     * @Template()
     */
    public function ExportNonReconnusAction(Request $request){
    	// Recherche des courriers
    	$em = $this->getDoctrine()->getManager();
    	$results = $em->getRepository('PenderieDefaultBundle:Courrier')->getNonReconnus();
    	 
    	// ask the service for a Excel5
    	$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
    	$now = new \DateTime();
    	$titre = "Export des courriers non reconnus";
    	$nomFichier = "export_courrier_non_reconnus_".$now->format('Y-m-d_h-i-s');
    	 
    	$phpExcelObject->getProperties()->setCreator("Siplec Création")
    	->setLastModifiedBy("Siplec Création")
    	->setTitle($titre)
    	->setSubject($titre)
    	->setDescription($titre." - ".$now->format('d/m/Y H:i:s'));
    	 
    	$phpExcelObject->setActiveSheetIndex(0)
    	->setCellValue('A1', $titre)
    	->setCellValue('A2', "Date de génération : ".$now->format('d/m/Y H:i:s'))
    	->setCellValue('A3', "Effectué par : ".$this->getUser())
    	->fromArray($results, NULL, 'A5');
    	$phpExcelObject->getActiveSheet()->setTitle('Export');
    	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    	$phpExcelObject->setActiveSheetIndex(0);
    
    	// create the writer
    	$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
    	// create the response
    	$response = $this->get('phpexcel')->createStreamedResponse($writer);
    	// adding headers
    	$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
    	$response->headers->set('Content-Disposition', "attachment;filename=$nomFichier.xls");
    	$response->headers->set('Pragma', 'public');
    	$response->headers->set('Cache-Control', 'maxage=1');
    
    	return $response;
    }

    /**
     * Finds and displays a Courrier entity.
     *
     * @Route("-ajax-{saisie}", name="courrier_ajax", defaults={"saisie" = 0})
     * @Method("GET")
     * @Template()
     */
    public function ajaxGetCourriersAction($saisie){
    	$page = $_GET['page']; // get the requested page
    	$limit = $_GET['rows']; // get how many rows we want to have into the grid
    	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    	$sord = $_GET['sord']; // get the direction
    	
    	$criteria = array();
    	if(isset($_GET['typeLettre']))			$criteria['typeLettre']=$_GET['typeLettre'];
    	if(isset($_GET['numDossier']))			$criteria['numDossier']=$_GET['numDossier'];
		if(isset($_GET['dateReceptionSiplec']))	$criteria['dateReceptionSiplec']= $this->transformeDate($_GET['dateReceptionSiplec']);
		if(isset($_GET['client']))				$criteria['client']=$_GET['client'];
		if(isset($_GET['typeEnvoi']))			$criteria['typeEnvoi']=$_GET['typeEnvoi'];
		if(isset($_GET['commentaireSiplec']))	$criteria['commentaire']=$_GET['commentaireSiplec'];
		if(isset($_GET['dateEnvoi']))			$criteria['dateEnvoi']= $this->transformeDate($_GET['dateEnvoi']);
		if(isset($_GET['numEnvoi']))			$criteria['numEnvoi']=$_GET['numEnvoi'];
		if(isset($_GET['statut']))				$criteria['statut']=$_GET['statut'];
		if(isset($_GET['dateReception']))		$criteria['dateReception']=$this->transformeDate($_GET['dateReception']);
		
		if ($saisie == '1') {
			// Si on est dans la vue saisie alors n'affiche que les courriers saisie ce jour
			$criteria['dateReceptionSiplec']= new \DateTime(date('Y-m-d'));
		}
    	
        $em = $this->getDoctrine()->getManager();
    	$count = $em->getRepository('PenderieDefaultBundle:Courrier')->getNbResultats($criteria, array($sidx=>$sord));
        $entities = $em->getRepository('PenderieDefaultBundle:Courrier')->getSearch($criteria, array($sidx=>$sord), $limit, $page);
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
    	foreach ($entities as $courrier) {
    		$responce->rows[$i]['id']=$courrier->getId();
    		$dateReceptionAdequation = ($courrier->getDateReceptionAdequation() != NULL)?$courrier->getDateReceptionAdequation()->format('d/m/Y H:i'):"";
    		$responce->rows[$i]['cell']=array(
					$courrier->getNumDossier(),
					$courrier->getDateReceptionSiplec()->format('d/m/Y'),
					$courrier->getClient(),
					$courrier->getTypeLettre(),
					$courrier->getTypeEnvoi(),
					$courrier->getCommentaire(),
					$courrier->getDateEnvoi()->format('d/m/Y'),
					$courrier->getNumEnvoi(),
					$courrier->getStatut(),
					$dateReceptionAdequation
    		);
    		$i++;
    	}
    	return new JsonResponse($responce);
    }

    /**
     * Met à jour un courrier depuis jqgrid
     *
     * @Route("-ajax-modification", name="courrier_ajax_update")
     * @Method("POST")
     * @Template()
     */
    public function ajaxUpdateCourriersAction(Request $request){
    	$new = false;
        $em = $this->getDoctrine()->getManager();
        $courrier = $em->getRepository('PenderieDefaultBundle:Courrier')->find($_POST['id']);

        if (!$courrier){
        	$new = true;
        	$courrier = new Courrier();
        }
		$courrier->setDateReceptionSiplec(($_POST['dateReceptionSiplec'] == "")?new \DateTime(date('Y-m-d 0:0:0')):$this->transformeDate($_POST['dateReceptionSiplec']))
				->setNumDossier($_POST['numDossier'])
				->setClient($_POST['client'])
				->setNumEnvoi($_POST['numEnvoi'])
				->setTypeLettre($_POST['typeLettre'])
				->setTypeEnvoi($_POST['typeEnvoi'])
				->setDateEnvoi(($_POST['dateEnvoi'] == "")?new \DateTime(date('Y-m-d 0:0:0')):$this->transformeDate($_POST['dateEnvoi']))
				->setCommentaire($_POST['commentaireSiplec']);
		$this->logiqueCourrier($courrier);
		if ($new){
			// Fige l'objet si nouvellement créé
			$em->persist($courrier); 
		}
		$em->flush(); // Sauvegarde les modifications en BD
		
        if ($new) {
        	return new JsonResponse(array(true, "le courrier $courrier à bien été créé", "Congratulation."));
        }else{
	        return new JsonResponse(array(true, "le courrier $courrier à bien été modifié", "Congratulation."));
        }
    }
    
    /**
     * Execute la logique métier sur le courrier en cours
     * 
     * @param Courrier $courrier
     */
    private function logiqueCourrier(Courrier &$courrier){
    	$em = $this->getDoctrine()->getManager();
    	
    	// Recherche le dossier d'adéquation pour indiquer qu'il à déjà été reçu
    	$dossier = $em->getRepository('PenderieDefaultBundle:Dossier')->getLastDossierByTypeCourrierAndNumDossier($courrier->getNumDossier(), $courrier->getTypeEnvoi());
    	if($dossier){
	    	$courrier->setDateReceptionAdequation($dossier->getDateReceptionAdequation());
	    	$courrier->setStatut(Courrier::STATUT_RECU);
	    	$courrier->setDossier($dossier);
    	}
    } 
    
    /** 
     * TODO : Voir à placer cette fonction dans une arboresescence plus générique pour être déclaré qu'une seule fois et accéssible partout
     * @param string $dateFrench date saisie au format francais du genre 23/06/2014 12:12
     * @param boolean $dateFrench si true alors si la date saisie est vide alors retourne la date en cours
     *
     * @return \Datetime or null
     */
    function transformeDate($dateFrench, $defaultNow = true) {
    	if ($dateFrench == "") {
    		if ($defaultNow) {
    			return new \DateTime();
    		}else{
    			return null;
    		}
    	}
    	preg_match("/(\d*)\/(\d*)\/(\d*) ?(\d*)?:?(\d*)?/", $dateFrench, $date);
    	if ($date[4] != "") {
    		$dateRetour = new \DateTime($date[3]."-".$date[2]."-".$date[1]." ".$date[4].":".$date[5]);
    	}else{
    		$dateRetour = new \DateTime($date[3]."-".$date[2]."-".$date[1]);
    	}
    	return $dateRetour;
    }
}

