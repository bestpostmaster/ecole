<?php

// src/App/Entity/Eleve.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    public function __construct(string $nom, string $prenom, string $dateNaiss)
    {
            $this->nom = $nom;
            $this->prenom = $prenom;
            $this->dateNaiss = $dateNaiss;
    }
	
	// Les Getters ------------------------------------
	
    public function getId(): ?int
    {
        return $this->id;
    }
	
	public function getNom(): ?string
    {
        return $this->nom;
    }
	
	public function getPrenom(): ?string
    {
        return $this->prenom;
    }
	
	public function getdateNaiss(): ?string
    {
        return $this->dateNaiss;
    }
	
	// Les Setters ------------------------------------
    public function setId(int $val)
    {
        $this->id = $val;
    }

	public function setNom(string $val)
    {
        $this->nom = $val;
    }
	
	public function setPrenom(string $val)
    {
        $this->prenom = $val;
    }
	
	public function setdateNaiss(string $val)
    {
        $this->dateNaiss = $val;
    }

    public function validateEleve()
    {
        $nom = $this->getNom();
        $prenom = $this->getPrenom();
        $dateNaiss = $this -> getdateNaiss();

        $messageEmptyFields = 'Tous les champs(nom, prenom, dateNaiss) sont obligatoirs!';
        if(!isset($nom) || !isset($prenom) || !isset($dateNaiss))
            throw new BadRequestHttpException($messageEmptyFields);

        if ($nom=='' || $prenom=='' || $dateNaiss=='')
            throw new BadRequestHttpException($messageEmptyFields);

        if (!$this -> validateDate($dateNaiss, 'd/m/Y'))
            throw new BadRequestHttpException('Format de la date invalide. Utilisez le format : JJ/MM/AAAA');

        if (!$this -> passedDate($dateNaiss))
            throw new BadRequestHttpException('Date de naissance dans le futur !!! ');
    }

    /**
     * Valider une date de naissance
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate(string $date, $format = 'Y-m-d H:i:s'): ?bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function passedDate(string $date)
    {
        $dateNaiss = new \DateTime($date);
        $current_date = new \DateTime();
        return ($dateNaiss < $current_date);
    }

}
