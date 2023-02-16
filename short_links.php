<?php

@unlink(__DIR__.'short_links.db');
$db = new PDO("sqlite:".__DIR__."/short_links.db");
$db->exec(
    "CREATE TABLE links(`id` VARCHAR PRIMARY KEY, `courses` VARCHAR)"
);
