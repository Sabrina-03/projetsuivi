<?php
function getNext12Months() {
    $months = [];
    $date = new DateTime();

    for ($i = 0; $i < 12; $i++) {
        $months[] = $date->format('Y-m');
        $date->modify('+1 month');
    }

    return $months;
}

function joursOuvresDuMois($year, $month) {
    // Liste des jours fériés fixes
    $joursFeries = [
        "$year-01-01", // Jour de l'An
        "$year-05-01", // Fête du Travail
        "$year-05-08", // Victoire 1945
        "$year-07-14", // Fête Nationale
        "$year-08-15", // Assomption
        "$year-11-01", // Toussaint
        "$year-11-11", // Armistice
        "$year-12-25", // Noël
    ];

    // Jours fériés mobiles (calculés depuis Pâques)
    $easter = easter_date($year);
    $easterDate = date('Y-m-d', $easter);
    $joursFeries[] = date('Y-m-d', strtotime($easterDate . ' +1 day'));  // Lundi de Pâques
    $joursFeries[] = date('Y-m-d', strtotime($easterDate . ' +39 days')); // Ascension
    $joursFeries[] = date('Y-m-d', strtotime($easterDate . ' +50 days')); // Lundi de Pentecôte

    $ouvrés = 0;
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $timestamp = strtotime($dateStr);
        $weekday = date('N', $timestamp); // 1 = lundi, 7 = dimanche

        if ($weekday < 6 && !in_array($dateStr, $joursFeries)) {
            $ouvrés++;
        }
    }

    return $ouvrés;

}
?>
