<?php
// src/App/Entity/Helper.php
namespace App\Helpers;

class Helper
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
}
