<?php
namespace Siplec\CdBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;

/**
 * Dossier
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Siplec\CdBundle\Entity\DossierRepository")
 * TODO : @ Assert\Callback(methods={"estCorrect"})
 */
class Dossier
{
	const TYPE_DOSSIER_NOUVEAU = 'Nouveau';
	const TYPE_DOSSIER_COMPLEMENT = 'Complément';
	
	const THEMATIQUE_CHAUDIERE_SONDES = 1;
	const THEMATIQUE_AICB = 2;
	const THEMATIQUE_ISO = 3;
	const THEMATIQUE_FENETRES = 4;
	const THEMATIQUE_PAC_CESI_SSC = 5;
	const THEMATIQUE_DOSSIERS_MULTIPLES = 6;
	const THEMATIQUE_COMPLEMENTS_APRES_OUVERTURE = 7;
	const THEMATIQUE_INITIALISATION = 8;
	
	const STATUT_RECU = 1;
	const STATUT_EN_COURS_DE_TRAITEMENT = 2;
	const STATUT_VALIDE = 3;
	const STATUT_REFUSE = 4;
	const STATUT_INCOMPLET = 5;
	const STATUT_REFUS_AUTOMATIQUE_EN_COURS = 6;
	const STATUT_REFUSE_AUTOMATIQUE = 7;
	
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\Regex("/[0-9]{4}-[0-9]{2}-[0-9]{6}/")
     * @ORM\Column(name="numDossier", type="string", length=14)
     */
    private $numDossier;

    /**
     * @var string
     *
     * @ORM\Column(name="typeDossier", type="string", columnDefinition="ENUM('Nouveau', 'Complément') NOT NULL") 
     */
    private $typeDossier;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="Thematique")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="thematique", referencedColumnName="id")
     * })
     */
    private $thematique;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateReceptionAdequation", type="datetime")
     */
    private $dateReceptionAdequation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateDebutTraitement", type="datetime", nullable=true)
     */
    private $dateDebutTraitement;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFinTraitement", type="datetime", nullable=true)
     */
    private $dateFinTraitement;

    /**
     * @var time
     *
     * @ORM\Column(name="dureeTraitement", type="time", nullable=true)
     */
    private $dureeTraitement;

    /**
     * @var boolean
     *
     * @ORM\Column(name="acticall", type="boolean")
     */
    private $acticall;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="StatutDossier")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="statut", referencedColumnName="id")
     * })
     */
    private $statut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateRefus", type="datetime", nullable=true)
     */
    private $dateRefus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateReexpedition", type="datetime", nullable=true)
     */
    private $dateReexpedition;

    /**
     * @var string
     *
     * @ORM\Column(name="refRecall", type="string", nullable=true)
     */
    private $refRecall;

    /**
     * @var Utilisateur
     * 
     * @ORM\ManyToOne(targetEntity="Siplec\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateurReexpedition", referencedColumnName="id")
     * })
     */
    private $utilisateurReexpedition;

    /**
     * @var Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Siplec\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateurDossier", referencedColumnName="id")
     * })
     */
    private $utilisateurDossier;

    /**
     * @var Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Siplec\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateurRefus", referencedColumnName="id")
     * })
     */
    private $utilisateurRefus;
    
    /**
     * @var Archive
     *
     * @ORM\ManyToOne(targetEntity="Archive")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="archive", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $archive;
    
    /**
     * @var Utilisateur
     *
     * @ORM\ManyToOne(targetEntity="Siplec\UserBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="utilisateur", referencedColumnName="id")
     * })
     */
    private $utilisateur;

    /**
     * @var integer
     *
     * //http://doctrine-orm.readthedocs.org/en/latest/reference/working-with-associations.html?highlight=cascade#transitive-persistence-cascade-operations
     *
     * @ORM\ManyToOne(targetEntity="Dossier", cascade={"detach"}) 
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dossierPere", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $dossierPere;

    /**
     * @ORM\OneToOne(targetEntity="Courrier", mappedBy="dossier")
     **/
    private $courrier;

    /**
     * @Assert\File(maxSize="6000000")
     */
    public $file;
    
    /**
     * Affichage par defaut d'un dossier
     *
     * @return string
     */
    public function __toString()
    {
    	$complement = "";
    	if($this->getTypeDossier() == self::TYPE_DOSSIER_COMPLEMENT)$complement = " complément";
    	return "dossier n°".$this->getNumDossier().$complement;
    }

    /**
     *
     */
    public function __construct()
    {
    	$this->dateReceptionAdequation = new \DateTime();
    	$this->acticall = false;
    }

    /**
     * Permet d'accèder aux constantes de manière dynamique en autre chose pour l'importation
     *
     * @param string $const
     * @return mixed
     */
    static function getConstant($const)
    {
    	return constant('self::'. $const);
    }
    
    /**
     * Cette fonction est utilisé par l'entité Assert\Callback(methods={"estCorrect"}) pour vérifier si le formulaire à correctement etait rempli
     * @param ExecutionContext $context
     */
    public function old_estCorrect(ExecutionContext $context){
//     	if (!($this->dateDebutArret == null OR $this->dateFinArret == null)) {
//     		if ($this->dateFinArret < $this->dateDebutArret) {
//     			$context->addViolationAtSubPath('dateFinArret', 'La date de fin d\'arrêt doit être supérieure à la date de début d\'arrêt.', array(), null);
//     		}
//     	}
//     	// est ce que le champ autre est bien saisie
//     	if ($this->getActivite() == "Autre" AND $this->getAutre() == "") {
//     		$context->addViolationAtSubPath('autre', 'Vous devez préciser le motif si vous avez sélectionner autre.', array(), null); // On renvoi l'erreur au contexte
//     	}
//     	// est ce que le chp date debut arret est saisie
//     	if (in_array($this->getActivite() , array("Maladie", "Congé maternité", "Congé paternité", "Accident travail")) AND $this->getDateDebutArret() == "") {
//     		$context->addViolationAtSubPath('dateDebutArret', 'Vous devez saisir une date de début d\'arrêt.', array(), null); // On renvoi l'erreur au contexte
//     	}
//     	// est ce que le chp date fin arret est saisie
//     	if (in_array($this->getActivite() , array("Maladie", "Congé maternité", "Congé paternité", "Accident travail")) AND $this->getDateFinArret() == "") {
//     		$context->addViolationAtSubPath('dateFinArret', 'Vous devez saisir une date de fin d\'arrêt.', array(), null); // On renvoi l'erreur au contexte
//     	}
    
//     	if (!($this->dateDebut == null OR $this->dateFin == null)) {
//     		// est ce que les dates debut et fin ne sont pas des week-end
//     		if ($this->getDateDebut()->format('N') == 6 OR $this->getDateDebut()->format('N') == 7) {
//     			$context->addViolationAtSubPath('dateDebut', 'Vous ne pouvez pas sélectionner un week-end pour cette date.', array(), null); // On renvoi l'erreur au contexte
//     		}
//     		// est ce que les dates debut et fin ne sont pas des week-end
//     		if ($this->getDateFin()->format('N') == 6 OR $this->getDateFin()->format('N') == 7) {
//     			$context->addViolationAtSubPath('dateFin', 'Vous ne pouvez pas sélectionner un week-end pour cette date.', array(), null); // On renvoi l'erreur au contexte
//     		}
//     		// est ce que les dates debut et fin ne sont pas des jours fériés
//     		if (in_array($this->getDateDebut()->format('Y-m-d'), $_SESSION['tabJoursFerie'])) {
//     			$context->addViolationAtSubPath('dateDebut', 'Vous ne pouvez pas sélectionner un jour férié pour cette date.', array(), null); // On renvoi l'erreur au contexte
//     		}
//     		// est ce que les dates debut et fin ne sont pas des jours fériés
//     		if (in_array($this->getDateFin()->format('Y-m-d'), $_SESSION['tabJoursFerie'])) {
//     			$context->addViolationAtSubPath('dateFin', 'Vous ne pouvez pas sélectionner un jour férié pour cette date.', array(), null); // On renvoi l'erreur au contexte
//     		}
//     		if ($this->dateFin < $this->dateDebut) {
//     			$context->addViolationAtSubPath('dateFin', 'La date de fin doit être supérieure à la date de début.', array(), null);
//     		}
//     	}
//     	if (!($this->dateDebutArret == null OR $this->dateFinArret == null)) {
//     		if ($this->dateFinArret < $this->dateDebutArret) {
//     			$context->addViolationAtSubPath('dateFinArret', 'La date de fin d\'arrêt doit être supérieure à la date de début d\'arrêt.', array(), null);
//     		}
//     	}
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set numDossier
     *
     * @param string $numDossier
     * @return Dossier
     */
    public function setNumDossier($numDossier)
    {
        $this->numDossier = $numDossier;

        return $this;
    }

    /**
     * Get numDossier
     *
     * @return string 
     */
    public function getNumDossier()
    {
        return $this->numDossier;
    }

    /**
     * Set typeDossier
     *
     * @param string $typeDossier
     * @return Dossier
     */
    public function setTypeDossier($typeDossier)
    {
        if (!in_array($typeDossier, array(self::TYPE_DOSSIER_NOUVEAU, self::TYPE_DOSSIER_COMPLEMENT))) {
            throw new \InvalidArgumentException("Type de dossier Invalide, type reçu : $typeDossier");
        }
        $this->typeDossier = $typeDossier;

        return $this;
    }

    /**
     * Get typeDossier
     *
     * @return string 
     */
    public function getTypeDossier()
    {
        return $this->typeDossier;
    }

    /**
     * Set thematique
     *
     * @param string $thematique
     * @return Dossier
     */
    public function setThematique($thematique)
    {
        $this->thematique = $thematique;

        return $this;
    }

    /**
     * Get thematique
     *
     * @return string 
     */
    public function getThematique()
    {
        return $this->thematique;
    }

    /**
     * Set dateReceptionAdequation
     *
     * @param \DateTime $dateReceptionAdequation
     * @return Dossier
     */
    public function setDateReceptionAdequation($dateReceptionAdequation)
    {
        $this->dateReceptionAdequation = $dateReceptionAdequation;

        return $this;
    }

    /**
     * Get dateReceptionAdequation
     *
     * @return \DateTime 
     */
    public function getDateReceptionAdequation()
    {
        return $this->dateReceptionAdequation;
    }

    /**
     * Set dateDebutTraitement
     *
     * @param \DateTime $dateDebutTraitement
     * @return Dossier
     */
    public function setDateDebutTraitement($dateDebutTraitement)
    {
        $this->dateDebutTraitement = $dateDebutTraitement;

        $this->calculDureeTraitement();

        return $this;
    }

    /**
     * Get dateDebutTraitement
     *
     * @return \DateTime 
     */
    public function getDateDebutTraitement()
    {
        return $this->dateDebutTraitement;
    }

    /**
     * Set dateFinTraitement
     *
     * @param \DateTime $dateFinTraitement
     * @return Dossier
     */
    public function setDateFinTraitement($dateFinTraitement)
    {
        $this->dateFinTraitement = $dateFinTraitement;

        $this->calculDureeTraitement();
        
        return $this;
    }

    /**
     * Get dateFinTraitement
     *
     * @return \DateTime 
     */
    public function getDateFinTraitement()
    {
        return $this->dateFinTraitement;
    }

    /**
     * Set dureeTraitement
     *
     * @param \DateTime $dureeTraitement
     * @return Dossier
     */
    private function setDureeTraitement($dureeTraitement)
    {
        $this->dureeTraitement = $dureeTraitement;

        return $this;
    }

    /**
     * Get dureeTraitement
     *
     * @return \DateTime 
     */
    public function getDureeTraitement()
    {
        return $this->dureeTraitement;
    }

    /**
     * Set acticall
     *
     * @param boolean $acticall
     * @return Dossier
     */
    public function setActicall($acticall)
    {
        $this->acticall = $acticall;

        return $this;
    }

    /**
     * Get acticall
     *
     * @return boolean 
     */
    public function getActicall()
    {
        return $this->acticall;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Dossier
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string 
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set statut
     *
     * @param Statut $statut
     * @return Dossier
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return string 
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set dateRefus
     *
     * @param \DateTime $dateRefus
     * @return Dossier
     */
    public function setDateRefus($dateRefus)
    {
        $this->dateRefus = $dateRefus;

        return $this;
    }

    /**
     * Get dateRefus
     *
     * @return \DateTime 
     */
    public function getDateRefus()
    {
        return $this->dateRefus;
    }

    /**
     * Set dateReexpedition
     *
     * @param \DateTime $dateReexpedition
     * @return Dossier
     */
    public function setDateReexpedition($dateReexpedition)
    {
        $this->dateReexpedition = $dateReexpedition;

        return $this;
    }

    /**
     * Get dateReexpedition
     *
     * @return \DateTime 
     */
    public function getDateReexpedition()
    {
        return $this->dateReexpedition;
    }

    /**
     * Set refRecall
     *
     * @param string $refRecall
     * @return Dossier
     */
    public function setRefRecall($refRecall)
    {
        $this->refRecall = $refRecall;

        return $this;
    }

    /**
     * Get refRecall
     *
     * @return string 
     */
    public function getRefRecall()
    {
        return $this->refRecall;
    }

    /**
     * Set archive
     *
     * @param \Siplec\CdBundle\Entity\Archive $archive
     * @return Dossier
     */
    public function setArchive(\Siplec\CdBundle\Entity\Archive $archive = null)
    {
        $this->archive = $archive;

        return $this;
    }

    /**
     * Get archive
     *
     * @return \Siplec\CdBundle\Entity\Archive 
     */
    public function getArchive()
    {
        return $this->archive;
    }

    /**
     * Set dossierPere
     *
     * @param \Siplec\CdBundle\Entity\Dossier $dossierPere
     * @return Dossier
     */
    public function setDossierPere(\Siplec\CdBundle\Entity\Dossier $dossierPere = null)
    {
        $this->dossierPere = $dossierPere;

        return $this;
    }

    /**
     * Get dossierPere
     *
     * @return \Siplec\CdBundle\Entity\Dossier 
     */
    public function getDossierPere()
    {
        return $this->dossierPere;
    }

    /**
     * Set courrier
     *
     * @param \Siplec\CdBundle\Entity\Courrier $courrier
     * @return Dossier
     */
    public function setCourrier(\Siplec\CdBundle\Entity\Courrier $courrier = null)
    {
        $this->courrier = $courrier;

        return $this;
    }

    /**
     * Get courrier
     *
     * @return \Siplec\CdBundle\Entity\Courrier 
     */
    public function getCourrier()
    {
        return $this->courrier;

    }
    
    /**
     * Calcul de la durée du traitement
     * Uniquement si la date de début et de fin de traitement existe
     */
    private function calculDureeTraitement()
    {
    	if($this->dateDebutTraitement && $this->dateFinTraitement)
    	{
    		$duree = $this->dateDebutTraitement->diff($this->dateFinTraitement);
    		$this->setDureeTraitement(new \DateTime($duree->format('0-0-0 %H:%i:%s')));
    	}

    }

    /**
     * Set utilisateurReexpedition
     *
     * @param \Siplec\UserBundle\Entity\User $utilisateurReexpedition
     * @return Dossier
     */
    public function setUtilisateurReexpedition(\Siplec\UserBundle\Entity\User $utilisateurReexpedition = null)
    {
        $this->utilisateurReexpedition = $utilisateurReexpedition;

        return $this;
    }

    /**
     * Get utilisateurReexpedition
     *
     * @return \Siplec\UserBundle\Entity\User 
     */
    public function getUtilisateurReexpedition()
    {
        return $this->utilisateurReexpedition;
    }

    /**
     * Set utilisateur
     *
     * @param \Siplec\UserBundle\Entity\User $utilisateur
     * @return Dossier
     */
    public function setUtilisateur(\Siplec\UserBundle\Entity\User $utilisateur = null)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Siplec\UserBundle\Entity\User 
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set utilisateurDossier
     *
     * @param \Siplec\UserBundle\Entity\User $utilisateurDossier
     * @return Dossier
     */
    public function setUtilisateurDossier(\Siplec\UserBundle\Entity\User $utilisateurDossier = null)
    {
        $this->utilisateurDossier = $utilisateurDossier;

        return $this;
    }

    /**
     * Get utilisateurDossier
     *
     * @return \Siplec\UserBundle\Entity\User 
     */
    public function getUtilisateurDossier()
    {
        return $this->utilisateurDossier;
    }

    /**
     * Set utilisateurRefus
     *
     * @param \Siplec\UserBundle\Entity\User $utilisateurRefus
     * @return Dossier
     */
    public function setUtilisateurRefus(\Siplec\UserBundle\Entity\User $utilisateurRefus = null)
    {
        $this->utilisateurRefus = $utilisateurRefus;

        return $this;
    }

    /**
     * Get utilisateurRefus
     *
     * @return \Siplec\UserBundle\Entity\User 
     */
    public function getUtilisateurRefus()
    {
        return $this->utilisateurRefus;
    }
}
