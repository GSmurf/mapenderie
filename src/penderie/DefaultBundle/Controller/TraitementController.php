<?php

namespace Penderie\DefaultBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Penderie\DefaultBundle\Entity\Dossier;
use Penderie\DefaultBundle\Entity\Archive;
use Penderie\DefaultBundle\Form\TraitementType;

/**
 * Traitement controller.
 *
 * @Route("/traitement")
 */
class TraitementController extends Controller
{

    /**
     * Lists all Dossier entities.
     *
     * @Route("", name="traitement")
     * @Method("POST|GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $dossier = new Dossier();
        $form   = $this->createFormFirstEtape($dossier);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
        	return $this->logiqueTraitementFirstEtape($dossier);
        }
        
        return array(
            'entity' => $dossier,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Lists all Dossier entities.
     *
     * @Route("-modification-{id}", name="traitement_update")
     * @Method("POST|GET")
     * @Template()
     */
    public function updateAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	$dossier = $em->getRepository('PenderieDefaultBundle:Dossier')->find($id);
    	
    	$form   = $this->createFormSecondEtape($dossier);
    	$form->handleRequest($request);
    	
    	if ($form->isValid()) {
    		return $this->logiqueTraitementSecondEtape($dossier);
    	}
    	
    	return array(
    			'entity' => $dossier,
    			'form'   => $form->createView(),
    	);
    }
    
    /**
     * Logique métier à la validation du premier formulaire
     * 
     * @param Dossier $dossier
     */
    private function logiqueTraitementFirstEtape(Dossier $dossier)
    {
		$em = $this->getDoctrine()->getManager();

		$dossierSelection = $em->getRepository('PenderieDefaultBundle:Dossier')->getFirstDossierByStatutRecuOrEnCours($dossier);

		// Le dossier n'existe pas
		if(!$dossierSelection)
		{
			$this->getRequest()->getSession()->getFlashBag()->add('error', "Le dossier ".$dossier->getNumDossier()." n'existe pas");
			return $this->redirect($this->generateUrl('traitement'));
		}
		// Le dossier est reçu, le statut passe à "en cours de traitement", il recherche l'archive du dossier père et il est redirigé à l'étape 2
		elseif($dossierSelection->getStatut()->getId() == Dossier::STATUT_RECU)
		{
			$this->setTraitementStatutEnCours($dossierSelection);
			$this->rechercheArchivePere($dossierSelection);
			return $this->redirect($this->generateUrl('traitement_update', array('id' => $dossierSelection->getId())));
		}
		// Le dossier est en cours de traitement par le meme opérateur, il doit être redirigé à l'étape 2
		elseif(($dossierSelection->getStatut()->getId() == Dossier::STATUT_EN_COURS_DE_TRAITEMENT) && ($dossierSelection->getUtilisateur() == $this->getUser()))
		{
			$this->rechercheArchivePere($dossierSelection);
			return $this->redirect($this->generateUrl('traitement_update', array('id' => $dossierSelection->getId())));
		}
		// Le dossier est en cours de traitement par un autre opérateur
		elseif(($dossierSelection->getStatut()->getId() == Dossier::STATUT_EN_COURS_DE_TRAITEMENT) && ($dossierSelection->getUtilisateur() != $this->getUser()))
		{
			$this->getRequest()->getSession()->getFlashBag()->add('error', "Le dossier ".$dossierSelection->getNumDossier()." est en cours de traitement par ".$dossierSelection->getUtilisateur()->getUsername()." depuis le ".$dossierSelection->getDateDebutTraitement()->format('d/m/Y à H:i:s'));
			return $this->redirect($this->generateUrl('traitement'));
		}
    }
    
    /**
     * Logique métier à la validation du deuxième formulaire
     * 
     * @param Dossier $dossier
     */
    private function logiqueTraitementSecondEtape(Dossier $dossier)
    {
    	$em = $this->getDoctrine()->getManager();
    	$this->setTraitementDateFin($dossier);
    	
    	switch($dossier->getStatut()->getId())
    	{
    		/**
    		 * Dossier incomplet
    		 * 	Si nouveau, affectation de la première archive libre
    		 * 	Si complément, affectation de l'archive du dossier père
    		 */
    		case Dossier::STATUT_INCOMPLET:
    			switch($dossier->getTypeDossier())
    			{
    				case Dossier::TYPE_DOSSIER_NOUVEAU:
    					$archive = $em->getRepository('PenderieDefaultBundle:Archive')->getFirstLineLibre();
    					if($archive == null)
    					{
    						$archive = new Archive($dossier->getNumDossier());
    						$em->persist($archive);
    					}    						

   						$archive->setNumDossier($dossier->getNumDossier());
   						$archive->setLibre(false);
   						$em->flush(); // Sauvegarde en base pour l'insertion de la nouvelle ligne afin d'avoir un id
   						$dossier->setArchive($archive);
    					break;
    				case Dossier::TYPE_DOSSIER_COMPLEMENT:
    					$archive = $dossier->getArchive();
    					break;
    			}
    			$this->getRequest()->getSession()->getFlashBag()->add('notice', "Le $dossier est terminé, à ranger dans l'".$archive);
    			break;
    		/**
    		 * Dossier validé ou refusé
    		 * 	Suppression du lien entre l'archive et les dossiers
    		 * 	Vidage du numéro de dossier et l'archive est libéré
    		 */
    		case Dossier::STATUT_VALIDE:
    		case Dossier::STATUT_REFUSE:
   				$em->getRepository('PenderieDefaultBundle:Dossier')->libereArchive($dossier);

   				$this->getRequest()->getSession()->getFlashBag()->add('notice', "Le $dossier est terminé");
    			break;
    	}
    	
    	$em->flush();
    	return $this->redirect($this->generateUrl('traitement'));
    }
    
    /**
     * Change le statut du dossier à 'En cours de traitement'
     * Ajout de la date de début de traitement au dossier
     * 
     * @param Dossier $dossier
     */
    
    private function setTraitementStatutEnCours(Dossier $dossier)
    {
    	$em = $this->getDoctrine()->getManager();
    	 
    	$dossier->setUtilisateur($this->getUser());
    	 
    	$statut = $em->getRepository('PenderieDefaultBundle:StatutDossier')->find(Dossier::STATUT_EN_COURS_DE_TRAITEMENT);
    	$dossier->setStatut($statut);
    	$dossier->setDateDebutTraitement(new \DateTime());
    	
    	$em->flush();
    }
    
    private function setTraitementDateFin(Dossier $dossier)
    {
    	$dossier->setDateFinTraitement(new \DateTime());
    }
    /**
     * Creates a form to create a Traitement entity.
     *
     * @param Traitement $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createFormFirstEtape(Dossier $entity)
    {
    	$form = $this->createForm(new TraitementType(1), $entity, array(
    			'action' => $this->generateUrl('traitement'),
    			'method' => 'POST',
    	));
    
    	$form->add('submit', 'submit', array('label' => 'Traiter'));
    
    	return $form;
    }
    
    /**
     * Creates a form to create a Traitement entity.
     *
     * @param Traitement $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createFormSecondEtape(Dossier $entity)
    {
    	$form = $this->createForm(new TraitementType(2), $entity, array(
    			'action' => $this->generateUrl('traitement_update', array('id' => $entity->getId())),
    			'method' => 'POST',
    	));
    
    	$form->add('submit', 'submit', array('label' => 'Traiter'));
    
    	return $form;
    }
    
    /**
     * Recherche de l'archive du dossier père
     * Affichage d'une notice différente suivant si le dossier père a une archive ou pas
     * 
     * @param Dossier $dossier
     */
    
    private function rechercheArchivePere(Dossier $dossier)
    {
    	$archive = null;
    	if($dossier->getDossierPere() != null)
    	{
    		$archive = $dossier->getDossierPere()->getArchive();
    	}
    	if($archive != null)
    	{
    		$this->getRequest()->getSession()->getFlashBag()->add('notice', "Le $dossier est liée à l'".$archive);
    	}
    	else
    	{
    		$this->getRequest()->getSession()->getFlashBag()->add('notice', "Le $dossier est en cours de traitement");
    	} 
    }
}
