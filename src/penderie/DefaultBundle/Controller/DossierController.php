<?php

namespace Penderie\DefaultBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Penderie\DefaultBundle\Entity\Dossier;
use Penderie\DefaultBundle\Entity\Archive;
use Penderie\DefaultBundle\Form\DossierType;
use Penderie\DefaultBundle\Form\ImportDossierType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Dossier controller.
 *
 * @Route("/dossier")
 */
class DossierController extends Controller
{

    /**
     * Lists all Dossier entities.
     *
     * @Route("", name="dossier")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('PenderieDefaultBundle:Dossier')->findAll();
        $thematiques = $em->getRepository('PenderieDefaultBundle:Thematique')->findAll();
        $statutDossier = $em->getRepository('PenderieDefaultBundle:StatutDossier')->findAll();

        return array(
            'entities' => $entities,
            'thematiques' => $thematiques,
            'statutDossier' => $statutDossier,
        	'saisie' => false,
        	'saisieUrl' => 0
        );
    }
    
    /**
     * Creates a new Dossier entity.
     *
     * @Route("-creation", name="dossier_create")
     * @Method("POST")
     * @Template("PenderieDefaultBundle:Dossier:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $dossier = new Dossier();
        $form = $this->createCreateForm($dossier);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $dossier->setDateReceptionAdequation(new \DateTime());
            if(!$this->logiqueDossier($dossier)){
            	return $this->redirect($this->generateUrl('dossier_new'));
            }
            $statut = $em->getRepository('PenderieDefaultBundle:StatutDossier')->find(Dossier::STATUT_RECU);
            $dossier->setStatut($statut);
            $dossier->setUtilisateurDossier($this->getUser());
            
            $em->persist($dossier);
            $em->flush();

            // Check s'il existe un courrier pour le passer au statut reçu
            $em->getRepository('PenderieDefaultBundle:Courrier')->dossierAdequationReçu($dossier);
            
            // Sauvegarde ces informations pour les reutilisés si on créé à la chaine
            $request->getSession()->set('typeDossier', $dossier->getTypeDossier());
            $request->getSession()->set('thematique', $dossier->getThematique());
            
            $request->getSession()->getFlashBag()->set('notice', "Le $dossier à été créé.");
            return $this->redirect($this->generateUrl('dossier_new'));
        }

        return array(
            'entity' => $dossier,
            'form'   => $form->createView(),
        	'saisie' => true,
        	'saisieUrl' => 1
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
        $form = $this->createForm(new DossierType(), $entity, array(
            'action' => $this->generateUrl('dossier_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Valider'));

        return $form;
    }

    /**
     * Displays a form to create a new Dossier entity.
     *
     * @Route("-nouveau", name="dossier_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $em = $this->getDoctrine()->getManager();
        $dossier = new Dossier();

        // Sauvegarde ces informations pour les reutilisés si on créé à la chaine
        if($this->getRequest()->getSession()->get('typeDossier') != null){
        	$dossier->setTypeDossier($this->getRequest()->getSession()->get('typeDossier'));
        }
        if($this->getRequest()->getSession()->get('thematique')){
        	$dossier->setThematique($em->getRepository('PenderieDefaultBundle:Thematique')
        									->find($this->getRequest()->getSession()->get('thematique')->getId()));
        }
        $form   = $this->createCreateForm($dossier);

        $thematiques = $em->getRepository('PenderieDefaultBundle:Thematique')->findAll();
        $statutDossier = $em->getRepository('PenderieDefaultBundle:StatutDossier')->findAll();
        
    	return $this->render(
    			'PenderieDefaultBundle:Dossier:index.html.twig',
    			array(
		            'entity' => $dossier,
	        		'thematiques' => $thematiques,
	        		'statutDossier' => $statutDossier,
		            'form'   => $form->createView(),
		        	'saisie' => true,
		        	'saisieUrl' => 1
		        )
    	);
    }

    /**
     * Finds and displays a Dossier entity.
     *
     * @Route("-{id}", name="dossier_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PenderieDefaultBundle:Dossier')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dossier entity.');
        }

        return array(
            'dossier'      => $entity,
        );
    }

    /**
     * Displays a form to edit an existing Dossier entity.
     *
     * @Route("-edition-{id}", name="dossier_edit", requirements={"id" = "\d+"})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('PenderieDefaultBundle:Dossier')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Dossier entity.');
        }

        $editForm = $this->createEditForm($entity);

        return array(
            'dossier'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Dossier entity.
    *
    * @param Dossier $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Dossier $entity)
    {
        $form = $this->createForm(new DossierType(true), $entity, array(
            'action' => $this->generateUrl('dossier_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Dossier entity.
     *
     * @Route("-modification-{id}", name="dossier_update", requirements={"id" = "\d+"})
     * @Method("PUT")
     * @Template("PenderieDefaultBundle:Dossier:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $dossier = $em->getRepository('PenderieDefaultBundle:Dossier')->find($id);
        if (!$dossier) {
            throw $this->createNotFoundException('Unable to find Dossier entity.');
        }

        $editForm = $this->createEditForm($dossier);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
        	if(!$this->logiqueDossier($dossier)){
        		return $this->redirect($this->generateUrl('dossier_new'));
        	}
            $em->flush();

            // Check s'il existe un courrier pour le passer au statut reçu
            $em->getRepository('PenderieDefaultBundle:Courrier')->dossierAdequationReçu($dossier);
            
            $request->getSession()->getFlashBag()->set('notice', "Le $dossier à été modifié.");
            return $this->redirect($this->generateUrl('dossier'));
        }

        return array(
            'entity'      => $dossier,
            'edit_form'   => $editForm->createView(),
        );
    }
    /**
     * Deletes a Dossier entity.
     *
     * @Route("-suppression-{id}", name="dossier_delete", requirements={"id" = "\d+"})
     * @Method("GET")
     * TODO : peut-être améliorer la protection @ Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('PenderieDefaultBundle:Dossier')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Dossier entity.');
            }
            //Libération de l'archive si le dossier est nouveau (car suppression de tous les enfants également)
			if($entity->getTypeDossier() == Dossier::TYPE_DOSSIER_NOUVEAU)
			{
				$em->getRepository('PenderieDefaultBundle:Dossier')->libereArchive($entity);
			}
            $em->remove($entity);
            $em->flush();
            $request->getSession()->getFlashBag()->set('error', "Le $entity à été supprimé.");

        return $this->redirect($this->generateUrl('dossier'));
    }

    /**
     * Finds and displays a Courrier entity.
     *
     * @Route("-ajax-{saisie}", name="dossier_ajax", defaults={"saisie" = 0})
     * @Method("GET")
     * @Template()
     */
    public function ajaxGetDossierAction($saisie){
    	$page = $_GET['page']; // get the requested page
    	$limit = $_GET['rows']; // get how many rows we want to have into the grid
    	$sidx = $_GET['sidx']; // get index row - i.e. user click to sort
    	$sord = $_GET['sord']; // get the direction

    	$criteria = array();
    	if(isset($_GET['numDossier']))					$criteria['numDossier']=$_GET['numDossier'];

		if(isset($_GET['typeDossier']))					$criteria['typeDossier']=$_GET['typeDossier'];
		if(isset($_GET['thematique']))					$criteria['thematique']=$_GET['thematique'];
		if(isset($_GET['dateReceptionAdequation']))		$criteria['dateReceptionAdequation']=$this->transformeDate($_GET['dateReceptionAdequation']);
		if(isset($_GET['acticall']))					$criteria['acticall']=$_GET['acticall'];
		if(isset($_GET['statut']))						$criteria['statut']=$_GET['statut'];
		if(isset($_GET['commentaire']))					$criteria['commentaire']=$_GET['commentaire'];
		if(isset($_GET['archive']))						$criteria['archive']=$_GET['archive'];
		
    	 
    	$em = $this->getDoctrine()->getManager();
    	if($saisie == 1)
    	{
    		$count = $em->getRepository('PenderieDefaultBundle:Dossier')->getNbResultatsDossier($criteria, $this->getUser());
    		$entities = $em->getRepository('PenderieDefaultBundle:Dossier')->getSearchDossier($criteria, $this->getUser(), array($sidx=>$sord), $limit, $page);
    	}else{
    		$count = $em->getRepository('PenderieDefaultBundle:Dossier')->getNbResultats($criteria, array($sidx=>$sord));
    		$entities = $em->getRepository('PenderieDefaultBundle:Dossier')->getSearch($criteria, array($sidx=>$sord), $limit, $page);
    	}
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
			$archive = "";
    		if($dossier->getArchive())
    		{
    			$archive = $dossier->getArchive()->GetId();
    		}
    		$responce->rows[$i]['id']=$dossier->getId();
    		$responce->rows[$i]['cell']=array(
    				$dossier->getNumDossier(),
    				$dossier->getTypeDossier(),
    				$dossier->getThematique()->getLibelle(),
    				$dossier->getDateReceptionAdequation()->format('d/m/Y'),
    				$dossier->getActicall(),
    				$dossier->getStatut()->getLibelle(),
    				$dossier->getCommentaire(),
    				$archive
    		);
    		$i++;
    	}
    	return new JsonResponse($responce);
    }
    
    /**
     * Execute la logique métier sur le dossier en cours
     * 
     * @param Dossier $dossier
     */
    private function logiqueDossier(Dossier $dossier){
    	$em = $this->getDoctrine()->getManager();
    	
    	// Test si le dossier existe deja afin d'eviter la création de deux dossiers de type nouveau
    	$rechercheDossierExistant = $em->getRepository('PenderieDefaultBundle:Dossier')->findOneBy(array('numDossier' => $dossier->getNumDossier(), 'typeDossier' => Dossier::TYPE_DOSSIER_NOUVEAU));
    	if($rechercheDossierExistant && $dossier->getTypeDossier() == Dossier::TYPE_DOSSIER_NOUVEAU)
    	{
    		if ($rechercheDossierExistant->getId() != $dossier->getId()) {
	    		$this->getRequest()->getSession()->getFlashBag()->set('error', "Le $dossier existe déjà en tant que nouveau.");
	    		return false;
    		}
    	}
    	
    	if($dossier->getTypeDossier() == Dossier::TYPE_DOSSIER_COMPLEMENT){
	    	// si le dossier est un complément alors on recherche un père
	    	$dossierPere = $em->getRepository('PenderieDefaultBundle:Dossier')->getPere($dossier);
	    	if ($dossierPere != null) {
	    		// affectation du dossier et récupération de l'archive du père
	    		$dossier->setDossierPere($dossierPere);
	    		$dossier->setArchive($dossierPere->getArchive());
	    	}elseif ($dossier->getId() == null){
		    	// Test si le complément que l'on est en train de créer possède bien un père nouveau sinon erreur
	    		$this->getRequest()->getSession()->getFlashBag()->set('error', "Le $dossier ne peut être créé car il n'existe pas de dossier référent de type nouveau.");
	    		return false;
	    	}
    	}
    	return true;
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
    	preg_match("/(\d*)\/(\d*)\/(\d*) ?(\d*)?:?(\d*)?:?(\d*)?/", $dateFrench, $date);
    	if ($date[4] != "") {
			$seconde = ($date[6] == "")?"00":$date[6];   		
    		$dateRetour = new \DateTime($date[3]."-".$date[2]."-".$date[1]." ".$date[4].":".$date[5].":".$seconde);
    	}else{
    		$dateRetour = new \DateTime($date[3]."-".$date[2]."-".$date[1]);
    	}
    	return $dateRetour;
    }
    
    /**
     * Telecharge un fichier excel des courriers non reconnus
     *
     * @Route("-a-traiter", name="dossier_export_a_traiter")
     * @Method("GET")
     * @Template()
     */
    public function ExportDossierATraiterAction(Request $request){
    	// Recherche des courriers
    	$em = $this->getDoctrine()->getManager();
    	$results = $em->getRepository('PenderieDefaultBundle:Dossier')->getATraiter();
    
    	// ask the service for a Excel5
    	$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
    	$now = new \DateTime();
    	$titre = "Export des dossiers à traiter";
    	$nomFichier = "export_dossier_a_traiter_".$now->format('Y-m-d_h-i-s');
    
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
     * Lists all Dossier entities.
     *
     * @Route("-import", name="dossier_import")
     * @Method("POST|GET")
     * @Template()
     */
    public function importAction(Request $request)
    {
    	// Definition des colonnes
    	$col = array(
    		'archive'	=>	0,
    		'numDossier'=>	1,
    		'date'		=>	2,
    	);
    	
        $dossier = new Dossier();
        
        // Génération du formulaire d'import du fichier csv
    	$form = $this->createForm(new ImportDossierType(), $dossier, array(
	            'action' => $this->generateUrl('dossier_import'),
	            'method' => 'POST',
	        ));
	    $form->add('submit', 'submit', array('label' => 'Importer'));
        $form->handleRequest($request);
        $nbErreurs = 0;
        if ($form->isValid()) {
        	$dir = "./upload/importDossierArchive/";
        	// Traite l'import du fichier excel cf : http://goo.gl/rUXx7q
        	$fichierUpload = $form['file']->getData(); // objet UploadedFile
        	 
        	// utiliser le nom de fichier original
        	$nomFichier = "Import_Dossiers_".date("Ymd_His").".csv";
        	$fichierUpload->move($dir, $nomFichier);
        	 
        	if (($handle = fopen ( $dir.$nomFichier, "r" )) !== FALSE) {
        		$em = $this->getDoctrine()->getManager();
        		$maxArchive = 0;
        		$thematique = $em->getRepository('PenderieDefaultBundle:Thematique')->find(DOSSIER::THEMATIQUE_INITIALISATION);
        		while ( ($fileData = fgetcsv ( $handle, 1000, ";" )) !== FALSE ) {
        			if ($fileData[0] && $fileData[1] && $fileData[2]) {
        				if ($fileData[0] > $maxArchive) $maxArchive = $fileData[0];
	        			$data[] = array('archive' 		=> $fileData[0],
		        						'numDossier' 	=> $fileData[1],
		        						'date'			=> $this->transformeDate($fileData[2]),
		        						'dateFin'			=> $this->transformeDate($fileData[2])->add(new \DateInterval('PT3M56S')) // ajoute 3 min 56 sec de traitement
		        					); // initialise la thématique à initialisation comme demandé par laurent le 22/07 à 15:39 par mail
        			}
        		}
        		fclose ( $handle );
        		$nbDossierImport = count($data);
        		
        		// Génere le nombre d'archives necessaire
        		$this->generationArchives($maxArchive);
        		
        		foreach ($data as $row) {
					$archiveImport = $em->getRepository('PenderieDefaultBundle:Archive')->find($row['archive']);
					$archiveImport->setNumDossier($row['numDossier'])
								  ->setId($row['archive']);
        			$em->persist($archiveImport);
        			
					$dossierImport = new Dossier();
					$dossierImport->setArchive($archiveImport)
								->setNumDossier($row['numDossier'])
								->setDateReceptionAdequation(new \DateTime())
								->setDateDebutTraitement($row['date'])
								->setDateFinTraitement($row['dateFin'])
								->setThematique($thematique)
								->setStatut($em->getRepository('PenderieDefaultBundle:StatutDossier')->find(DOSSIER::STATUT_INCOMPLET))
								->setUtilisateur($this->getUser())
								->setUtilisateurDossier($this->getUser())
								->setTypeDossier(DOSSIER::TYPE_DOSSIER_NOUVEAU);
        			$em->persist($dossierImport);
        		}
        		
        		// flush the remaining objects
        		$em->flush();
        	}
        		
        	$pluriel = ($nbErreurs>1)?"s":"";
        	$verbePluriel = ($nbErreurs>1)?"n'ont":"n'a";
        	$plurielImport = ($nbDossierImport>1)?"s":"";
        		
        	$request->getSession()->getFlashBag()->set(($nbErreurs == 0)?'notice':'error', "Le fichier comprenant $nbDossierImport numéro$plurielImport de dossier à été importé avec succès");
        	return $this->redirect($this->generateUrl('dossier', array('import'=>"import")));
        }
    	return array(
    			'form' => $form->createView()
    	);
    }
    
    /**
     * Genere toutes les archives necessaires pour l'import
     * @param unknown $nb
     */
    private function generationArchives($nb){
    	$em = $this->getDoctrine()->getManager();
    	for ($i = 1 ; $i <= $nb; $i++){
    		$a = new Archive();
    		$a->setLibre(true);
    		$em->persist($a);
    	}
    	$em->flush();
    }
}
