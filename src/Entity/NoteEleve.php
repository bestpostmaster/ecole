<?php
// api/src/Entity/Note.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="noteeleve")
 * @ORM\Entity(repositoryClass="App\Repository\NoteEleveRepository")
 */
 
class NoteEleve // The class name will be used to name exposed resources
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

	/**
     * @ORM\Column(type="integer")
     */
    private $id_note;
	
	/**
     * @ORM\Column(type="integer")
     */
    private $id_eleve;

    public function __construct($id_note, $id_eleve)
    {
		$this ->id_note = $id_note;
		$this ->id_eleve = $id_eleve;
    }
    
	// Les Getters -----------------------------------------
	public function getIdNote(): ?int
    {
        return $this->id_note;
    }

	public function getIdEleve(): ?int
    {
        return $this->id_eleve;
    }
	
	// Les Setters -----------------------------------------
	public function setIdNote(int $id_note)
    {
        $this->id_note = $id_note;
    }

	public function setIdEleve(int $id_eleve)
    {
        $this->id_eleve = $id_eleve;
    }
}
