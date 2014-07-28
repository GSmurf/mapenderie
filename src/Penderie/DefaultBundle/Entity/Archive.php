<?php

namespace Penderie\DefaultBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Archive
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Penderie\DefaultBundle\Entity\ArchiveRepository")
 */
class Archive
{
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
     * @ORM\Column(name="numDossier", type="string", length=14)
     */
    private $numDossier;

    /**
     * @var boolean
     *
     * @ORM\Column(name="libre", type="boolean")
     */
    private $libre;

    /**
     * Affichage par defaut d'une archive
     *
     * @return string
     */
    public function __toString()
    {
    	return "archive n°".$this->getId();
    }

    /**
     * @param string $numDossier
     */
    public function __construct($numDossier=""){
    	$this->numDossier = $numDossier;
    	$this->libre = false;
    }
    /**
     * Set id
     *
     * @param integer $id
     * @return Archive
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set libre
     *
     * @param boolean $libre
     * @return Archive
     */
    public function setLibre($libre)
    {
        $this->libre = $libre;

        return $this;
    }

    /**
     * Get libre
     *
     * @return boolean 
     */
    public function getLibre()
    {
        return $this->libre;
    }

    /**
     * Set numDossier
     *
     * @param string $numDossier
     * @return Archive
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
}
