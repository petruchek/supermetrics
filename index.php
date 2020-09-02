<?php
/**
 * Simple demo file that:
    0. loads config (@constant API_CLIENT_ID)
    1. calls API Talker class (passing API_CLIENT_ID; getting $posts)
    2. calls Analyzer class (passing $posts; getting $stats)
    3. converts statistics $stats to JSON and sends them to STDOUT
 * php version 7.2.1
 *
 * @category Demo
 * @package  Petruchek_Supermetrics_Assignment
 * @author   Val Petruchek <petruchek@gmail.com>
 * @license  https://www.opensource.org/licenses/mit-license.html  MIT License
 * @link     https://github.com/petruchek/supermetrics-assignment
 */

namespace petruchek\supermetrics;

require_once __DIR__.DIRECTORY_SEPARATOR."autoload.php";
require_once __DIR__.DIRECTORY_SEPARATOR."config.php";

if (!defined("API_CLIENT_ID") || !API_CLIENT_ID) {
    throw new \Exception("API_CLIENT_ID must be defined by now (also must be not empty).");
}

$talker = new Talker(API_CLIENT_ID);
$posts = $talker->fetch_posts();

if (!$posts) {
    throw new \Exception("No posts fetched. Broken API or invalid credentials?");
}

$stats = Analyzer::analyze($posts);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($stats);
