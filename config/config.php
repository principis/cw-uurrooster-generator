<?php

const AULAS = [
    'C300 00.03' => 'C300 00.03 (Aula A)',
    'C300 00.08' => 'C300 00.08 (Aula B)',
    'C300 00.09' => 'C300 00.09 (Aula C)',
    'C300 00.81' => 'C300 00.81 (Aula D)',
    'C300 00.77' => 'C300 00.77 (Aula E)',
    '200C 01.27' => '200C 01.27 (Aula A - Entrance QDV)',
    '200C 01.17' => '200C 01.17 (Aula B - Entrance Bus)',
    '200C 01.05' => '200C 01.05 (Aula C - Entrance QDV)',
    '200C 01.06' => '200C 01.06 (Aula D - Entrance Bus)',
    '200A 00.26' => '200A 00.26 (SOL N)',
    '200A 00.25' => '200A 00.25 (SOL Z)',
    '200A 00.225' => '200A 00.225 (Aula Erik Duval)',
];

function currentYear(): int
{
    return (date('m') < 9) ? (int)date('Y') - 1 : (int)date('Y');
}

function sources(): array {

    $current = currentYear() - 2000;
    $next = $current + 1;
    return [
        "https://people.cs.kuleuven.be/~btw/roosters$current$next/cws_semester_1.html",
        "https://people.cs.kuleuven.be/~btw/roosters$current$next/cws_semester_2.html",
    ];
}