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

require __DIR__.'/../vendor/autoload.php';
include __DIR__.'/../src/header.html';
require __DIR__.'/../config/config.php';

$db = new PDO("sqlite:".__DIR__."/../roosters.db");
$stmt = $db->query("SELECT * FROM courses ORDER BY course");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$s_courses = [];

if (isset($_GET['courses']) && ctype_alnum($_GET['courses'])) {
    $linkdb = new PDO("sqlite:".__DIR__."/../short_links.db");
    $stmt = $linkdb->prepare("SELECT * FROM links WHERE id=?");
    $stmt->execute([$_GET['courses']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $s_courses = explode(':', $row['courses']);
    }
}

$current = currentYear();
$academicYear = "$current - ".($current + 1);

echo <<<EOL
<div class="bg-light p-5 mb-3 rounded">
  <h1>CW Uurrooster generator</h1>
  <p>Select your courses and submit. Copy the provided URL to your calendar.</p>
  <div class="alert alert-info" role="alert">
  <h4 class="alert-heading">Note:</h4>
  <ul>
  <li>Double courses are probably an entry for both semesters. Just select them both.</li>
  <li>To add or remove courses, you have to recreate the calendar. For Google Calendar, this means removing the calendar, and adding it again.
  <i>This cannot be changed without providing a unique id to every user of this app, which is too much work.</i></li>
  <li>This works with every calendar with support for iCal. For Apple, just add a "subscription"</li>
  <li>Do <b>NOT</b> import the ical file into your calendar. This won't update automatically, always use the provided url!</li>
  <li>The calendar will automatically switch to the new academic year on 1 September. <i>Current year: $academicYear</i></li>
  <li>Yell at me on discord if something is broken.</li>
  </ul>
  </div>
  <div>
  <form method="get">
  <div class="row">
    <label for="inputExisting" class="col-auto col-form-label">Import existing courses</label>
    <div class="col-auto">
      <input name="courses" type="text" class="form-control" placeholder="05108ed7" id="inputExisting">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary">Submit</button>
    </div>
  </div>
</form>
  </div>
</div>
EOL;

echo '<form action="ical.php" target="_blank" method="get">';
echo '<div class="list-group">';

foreach ($courses as $course) {
    $checked = in_array($course['id'], $s_courses);

    echo "<label class=\"list-group-item d-flex gap-2\">
      <input class=\"form-check-input flex-shrink-0\" type=\"checkbox\" name=\"course[]\" value=\"{$course['id']}\"".($checked ? 'checked' : '').">
      <span>
        {$course['course']}
        <span class=\"d-block text-muted\">
            <a class='text-decoration-none' target='_blank' href=\"{$course['url']}\">
                <span class=\"badge bg-secondary\"><i class=\"bi bi-link-45deg\"></i> {$course['opo']}</span>
                
            </a>
            <span class=\"badge bg-info text-dark\">{$course['group']}</span>
        </span>
      </span>
    </label>";
}
echo '</div>';

echo '<button value="submit" type="submit" class="btn btn-primary mt-3">Submit</button>';
echo '</form>';

include __DIR__.'/../src/footer.html';
