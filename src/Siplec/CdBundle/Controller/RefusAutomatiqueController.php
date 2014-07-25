<?php
namespace Siplec\CdBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Siplec\CdBundle\Entity\Dossier;
use Siplec\CdBundle\Form\RefusAutomatiqueType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * RefusAutomatique controller.
 * TODO : extends ControllerAdéquation qui serai dans le namespace Siplec\CdBundle\Controller qui permettrai de définir au moins deux méthodes : une d'export en excel generique qui attends ($tableauxResultat, $titre, $nomFichier) et la fonction de transformeDate 
 *
 * @Route("/refus-auto")
 */
class RefusAutomatiqueController extends Controller
{
    /**
     * Lists all Dossier entities.
     *
     * @Route("-{import}", name="refus_auto", defaults={"import" = 0}, requirements={"import"="0|import"})
     * @Method("POST|GET")
     * @Template("SiplecCdBundle:RefusAutomatique:index.html.twig")
     */
    public function indexAction(Request $request, $import)
    {
        $em = $this->getDoctrine()->getManager();
        $statutRefuseEnCours = $em->getRepository('SiplecCdBundle:StatutDossier')->find(Dossier::STATUT_REFUS_AUTOMATIQUE_EN_COURS);
        $statutRefuse = $em->getRepository('SiplecCdBundle:StatutDossier')->find(Dossier::STATUT_REFUSE_AUTOMATIQUE);
        $dossier = new Dossier();
        
        // Génération du formulaire de refus automatique
        $formRefusAuto = $this->createCreateForm($dossier);
        $formRefusAuto->handleRequest($request);
        
        // Génération du formulaire d'import du fichier excel
    	$formImport = $this->createForm(new RefusAutomatiqueType(true), $dossier, array(
	            'action' => $this->generateUrl('refus_auto'),
	            'method' => 'POST',
	        ));
	    $formImport->add('submit', 'submit', array('label' => 'Importer'));
        $formImport->handleRequest($request);

        if ($formImport->isValid()) {
			$erreurs = "";
			$nb = $nbErreurs = 0;
        	$dir = "./upload/importRefusAuto/";
        	// Traite l'import du fichier excel cf : http://goo.gl/rUXx7q
        	$fichierUpload = $formImport['file']->getData(); // objet UploadedFile
        	
        	// utiliser le nom de fichier original
        	$nomFichier = "Import_Refus_Auto_".date("Ymd_His").".csv";
        	$fichierUpload->move($dir, $nomFichier);
        	
        	if (($handle = fopen ( $dir.$nomFichier, "r" )) !== FALSE) {
				while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
					$nb++;
					$num = count ( $data );
					for($c = 0; $c < $num; $c ++) {
						$dossier = $em->getRepository('SiplecCdBundle:Dossier')->getLastDossierByNumDossierAndStatutIncomplet($data[$c]);
						// si un dossier correspondant à ce numéro de dossier à été trouvé alors modifie sont statut, sinon reporte cette Erreur à l'utilisateur
						if ($dossier) {
							$dossier->setStatut($statutRefuseEnCours);
							$em->flush();
						}else {
							$nbErreurs++;
							$erreurs .= $data[$c].", ";
						}
					}
				}
				fclose ( $handle );
			}
			
			$pluriel = ($nbErreurs>1)?"s":"";
			$verbePluriel = ($nbErreurs>1)?"n'ont":"n'a";
			$plurielImport = ($nb>1)?"s":"";
			
			if ($erreurs != "") {
				$erreurs = " mais $nbErreurs dossier$pluriel : ".substr($erreurs, 0, -2)." $verbePluriel pas trouvé$pluriel de correspondance.";
			}
	        $request->getSession()->getFlashBag()->set(($nbErreurs == 0)?'notice':'error', "Le fichier comprenant $nb numéro$plurielImport de dossier à été importé avec succès$erreurs");
            return $this->redirect($this->generateUrl('refus_auto', array('import'=>"import")));
        }elseif ($formRefusAuto->isValid()) {
        	// On fait passer le dossier au statut Dossier:STATUT_REFUSE_AUTOMATIQUE
        	// Si plusieurs dossiers existent prends le dernier complément pour le passer à l'état REFUSE_AUTOMATIQUE
        	$dossierSelection = $em->getRepository('SiplecCdBundle:Dossier')->getLastDossierByNumDossier($dossier->getNumDossier());
        	
        	if ($dossierSelection) {
	        	if(in_array($dossierSelection->getStatut(), array($statutRefuseEnCours, $statutRefuse))){
		        	if($dossierSelection->getDateRefus() == null){
		        		$dossierSelection->setStatut($statutRefuse);
			        	$dossierSelection->setDateRefus(new \DateTime());
			        	$dossierSelection->setUtilisateurRefus($this->getUser());
			        	$em->getRepository('SiplecCdBundle:Dossier')->libereArchive($dossierSelection);
			        	$em->flush();
			        	
			            $request->getSession()->getFlashBag()->set('notice', "Le $dossier est passé à l'état refusé automatique.");
		        	}else{
			            $request->getSession()->getFlashBag()->set('notice', "Le $dossier à déjà été refusé le ".$dossierSelection->getDateRefus()->format('d/m/Y à H:i'));
		        	}
	        	}else{
		            $request->getSession()->getFlashBag()->set('error', "Le $dossier n'est pas au statut ".$statutRefuseEnCours);
	        	}
        	}else{
	            $request->getSession()->getFlashBag()->set('error', "Le numéro de dossier \"".$dossier->getNumDossier()."\" n'a pas été trouvé.");
        	}
        	
            return $this->redirect($this->generateUrl('refus_auto'));
        }

        return array(
            'entity' => $dossier,
            'pasErreur' => !$request->getSession()->getFlashBag()->has('error'),
        	'formulaireImportValide' => $import,
            'formRefusAuto'   => $formRefusAuto->createView(),
            'form'   => $formImport->createView()
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
        $form = $this->createForm(new RefusAutomatiqueType(), $entity, array(
            'action' => $this->generateUrl('refus_auto'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Traiter'));

        return $form;
    }
    
    /**
     * Telecharge un fichier excel des courriers non reconnus
     *
     * @Route("-a-traiter", name="refus_auto_export_a_traiter")
     * @Method("GET")
     * @Template()
     */
    public function exportATraiterAction(Request $request){
    	// Recherche des courriers
    	$em = $this->getDoctrine()->getManager();
    	$results = $em->getRepository('SiplecCdBundle:Dossier')->getRefusAutomatiqueATraiter();
    
    	// ask the service for a Excel5
    	$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
    	$now = new \DateTime();
    	$titre = "Export des dossiers refus auto. à traiter";
    	$nomFichier = "export_refus_automatique_a_traiter_".$now->format('Y-m-d_h-i-s');
    
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
     * @Route("-ajax", name="refus_auto_ajax")
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
//        $statutRefuse = $em->getRepository('SiplecCdBundle:StatutDossier')->find(Dossier::STATUT_REFUSE_AUTOMATIQUE);
        
//    	$criteria['statut']=$statutRefuse;
    	// TODO : ajouter la selection par date du jour plus utilisateur connecté
//     	$criteria['dateRefus']=array((new \DateTime('2014-07-16'))->format('Y-m-d'));
    
    	$count = $em->getRepository('SiplecCdBundle:Dossier')->getNbResultatsRefus($criteria, $this->getUser());
    	$entities = $em->getRepository('SiplecCdBundle:Dossier')->getSearchRefus($criteria, $this->getUser(), array($sidx=>$sord), $limit, $page);
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
    				$dossier->getNumDossier(),
    				$dossier->getStatut()->getLibelle(),
    				($dossier->getDateRefus())?$dossier->getDateRefus()->format('d/m/Y'):"",
    		);
    		$i++;
    	}
    	return new JsonResponse($responce);
    }
}