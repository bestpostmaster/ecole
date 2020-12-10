<?php

// src/App/Entity/Eleve.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="eleves")
 * @ORM\Entity(repositoryClass="App\Repository\EleveRepository")
 */
class Eleve // The class name will be used to name exposed resources
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $nom;
	
    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $prenom;
	
    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $dateNaiss;

    public function __construct($nom, $prenom, $dateNaiss)
    {
		$this ->nom = $nom;
		$this ->prenom = $prenom;
		$this ->dateNaiss = $dateNaiss;
    }
	
	// Les Getters ------------------------------------
	
    public function getId(): ?int
    {
        return $this->id;
    }
	
	public function getNom()
    {
        return $this->nom;
    }
	
	public function getPrenom()
    {
        return $this->prenom;
    }
	
	public function getdateNaiss()
    {
        return $this->dateNaiss;
    }
	
	// Les Setters ------------------------------------
    public function setId($val)
    {
        $this->id = $val;
    }

	public function setNom($val)
    {
        $this->nom = $val;
    }
	
	public function setPrenom($val)
    {
        $this->prenom = $val;
    }
	
	public function setdateNaiss($val)
    {
        $this->dateNaiss = $val;
    }

}
