<?php
// src/App/Entity/Eleve.php
namespace App\Helper;

class Helper
{
    /**
     * Valider une date de naissance
     * @param $date
     * @param string $format
     * @return bool
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
