<?php
namespace Penderie\DefaultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class EntitySiplec {
	/**
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

/**
 * Courrier
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Penderie\DefaultBundle\Entity\CourrierRepository")
 */
class Courrier extends EntitySiplec
{
    const TYPE_LETTRE_SUIVI = 'Suivi';
    const TYPE_LETTRE_AR = 'A/R';
    
    const TYPE_ENVOI_NOUVEAU = 'Nouveau';
    const TYPE_ENVOI_COMPLEMENT = 'Complément';
    const TYPE_ENVOI_REFUS_AUTOMATIQUE = 'Refus automatique';
    
    const STATUT_ENVOYE = 'Envoyé';
    const STATUT_RECU = 'Reçu';
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateReceptionSiplec", type="datetime")
     */
    private $dateReceptionSiplec;

    /**
     * @var string
     *
     * @ORM\Column(name="client", type="string", length=255)
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(name="numDossier", type="string", length=14)
     */
    private $numDossier;

    /**
     * @var string
     *
     * @ORM\Column(name="numEnvoi", type="string", length=16)
     */
    private $numEnvoi;

    /**
     * @var string
     *
     * @ORM\Column(name="typeLettre", type="string", columnDefinition="ENUM('Suivi', 'A/R') NOT NULL") 
     */
    private $typeLettre;

    /**
     * @var string
     *
     * @ORM\Column(name="typeEnvoi", type="string", columnDefinition="ENUM('Nouveau', 'Complément', 'Refus automatique') NOT NULL") 
     */
    private $typeEnvoi;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEnvoi", type="datetime")
     */
    private $dateEnvoi;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="statut", type="string", columnDefinition="ENUM('Envoyé', 'Reçu') NOT NULL") 
     */
    private $statut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateReceptionAdequation", type="datetime", nullable=true)
     */
    private $dateReceptionAdequation;

    /**
     * @var Dossier
     *
     * @ORM\OneToOne(targetEntity="Dossier", inversedBy="courrier")
     * @ORM\JoinColumn(name="dossier_id", referencedColumnName="id")
     */
    private $dossier;


    /**
     *
     */
    public function __toString()
    {
    	return "courrier n° ".$this->getNumEnvoi();
    }

    /**
     *
     */
    public function __construct()
    {
    	$this->dateReceptionSiplec = new \DateTime();
    	$this->statut = self::STATUT_ENVOYE;
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
     * Set dateReceptionSiplec
     *
     * @param \DateTime $dateReceptionSiplec
     * @return Courrier
     */
    public function setDateReceptionSiplec($dateReceptionSiplec)
    {
        $this->dateReceptionSiplec = $dateReceptionSiplec;

        return $this;
    }

    /**
     * Get dateReceptionSiplec
     *
     * @return \DateTime 
     */
    public function getDateReceptionSiplec()
    {
        return $this->dateReceptionSiplec;
    }

    /**
     * Set client
     *
     * @param string $client
     * @return Courrier
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return string 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set numDossier
     *
     * @param string $numDossier
     * @return Courrier
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
     * Set numEnvoi
     *
     * @param string $numEnvoi
     * @return Courrier
     */
    public function setNumEnvoi($numEnvoi)
    {
        $this->numEnvoi = $numEnvoi;

        return $this;
    }

    /**
     * Get numEnvoi
     *
     * @return string 
     */
    public function getNumEnvoi()
    {
        return $this->numEnvoi;
    }

    /**
     * Set typeLettre
     *
     * @param string $typeLettre
     * @return Courrier
     */
    public function setTypeLettre($typeLettre)
    {
        if (!in_array($typeLettre, array(self::TYPE_LETTRE_AR, self::TYPE_LETTRE_SUIVI))) {
            throw new \InvalidArgumentException("Type de lettre Invalide");
        }
        $this->typeLettre = $typeLettre;

        return $this;
    }

    /**
     * Get typeLettre
     *
     * @return string 
     */
    public function getTypeLettre()
    {
        return $this->typeLettre;
    }

    /**
     * Set typeEnvoi
     *
     * @param string $typeEnvoi
     * @return Courrier
     */
    public function setTypeEnvoi($typeEnvoi)
    {
        if (!in_array($typeEnvoi, array(self::TYPE_ENVOI_COMPLEMENT, self::TYPE_ENVOI_NOUVEAU, self::TYPE_ENVOI_REFUS_AUTOMATIQUE))) {
            throw new \InvalidArgumentException("Type d'envoi Invalide");
        }
        $this->typeEnvoi = $typeEnvoi;

        return $this;
    }

    /**
     * Get typeEnvoi
     *
     * @return string 
     */
    public function getTypeEnvoi()
    {
        return $this->typeEnvoi;
    }

    /**
     * Set dateEnvoi
     *
     * @param \DateTime $dateEnvoi
     * @return Courrier
     */
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * Get dateEnvoi
     *
     * @return \DateTime 
     */
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     * @return Courrier
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
     * @param string $statut
     * @return Courrier
     */
    public function setStatut($statut)
    {
        if (!in_array($statut, array(self::STATUT_ENVOYE, self::STATUT_RECU))) {
            throw new \InvalidArgumentException("Statut Invalide");
        }
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
     * Set dateReceptionAdequation
     *
     * @param \DateTime $dateReceptionAdequation
     * @return Courrier
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
     * Set dossier
     *
     * @param string $dossier
     * @return Courrier
     */
    public function setDossier($dossier)
    {
        $this->dossier = $dossier;

        return $this;
    }

    /**
     * Get dossier
     *
     * @return string 
     */
    public function getDossier()
    {
        return $this->dossier;
    }
}
