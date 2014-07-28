<?php
namespace Penderie\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="Utilisateur")
 * @ORM\Entity(repositoryClass="Penderie\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    protected $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255)
     * @ Assert\NotBlank(message="Entrez le prénom.")
     * @ Assert\MinLength(limit="3", message="Le prénom doit avoir plus de 3 caractères.")
     * @ Assert\MaxLength(limit="255", message="Le prénom doit avoir moins de 255 caractères.")
     */
    protected $prenom;
    
    public function __construct()
    {
        parent::__construct();
        $this->nom = "";
        $this->prenom = "";
    }
    
    
    public function __toString()
    {
        return parent::__toString()/*.$this->getNom()." - ".$this->getPrenom()*/;
    }
    

    /**
     * Set nom
     *
     * @param string $nom
     * @return Dossier
     */
    public function setNom($nom)
    {
    	$this->nom = $nom;
    
    	return $this;
    }
    
    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nom
     *
     * @param string $prenom
     * @return Dossier
     */
    public function setPrenom($prenom)
    {
    	$this->prenom = $prenom;
    
    	return $this;
    }
    
    /**
     * Get nom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
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
}
