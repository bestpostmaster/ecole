<?php
// src/App/Entity/Helper.php
namespace App\Helpers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Helper extends AbstractController
{
    /**
     * Valider une date de naissance
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate(string $date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    /**
     * Récupérer le repository de Doctrine
     */
    public function getRepo ($repoName)
    {
        $repository = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository($repoName);

        return $repository;
    }
}
