<?php

/*
 * This file is part of cw-uurrooster-generator.
 * Copyright (C) 2022 Arthur Bols
 *
 * cw-uurrooster-generator is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * cw-uurrooster-generator is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with cw-uurrooster-generator.  If not, see <https://www.gnu.org/licenses/>.
 */

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