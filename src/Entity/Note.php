<?php
// api/src/Entity/Note.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="notes")
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 */
 
class Note // The class name will be used to name exposed resources
{
	/**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $valeur;
	
    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $matiere;

    public function __construct($valeur, $matiere)
    {
		$this ->valeur = $valeur;
		$this ->matiere = $matiere;
    }
    
	// Les Getters -----------------------------------------
	public function getId(): ?int
    {
        return $this->id;
    }

	public function getValeur(): ?float
    {
        return $this->valeur;
    }
	
	public function getMatiere(): ?string
    {
        return $this->matiere;
    }

	// Les Setters -----------------------------------------
	public function setId(int $val)
    {
        $this->id = $val;
    }

	public function setValeur(string $val)
    {
        $this->valeur = $val;
    }
	
	public function setMatiere(string $val)
    {
        $this->matiere = $val;
    }
}
