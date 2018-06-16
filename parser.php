<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use JonnyW\PhantomJs\Client;
use PHPHtmlParser\Dom;

$client = Client::getInstance();
$messageFactory = $client->getMessageFactory();
$request = $messageFactory->createRequest('https://www.myscore.ru/', 'GET');
$response = $messageFactory->createResponse();
$client->send($request, $response);

$entries = [];
$dom = new Dom();
$dom->load($response->getContent());
/** @var \PHPHtmlParser\Dom\Collection $domEntries */
$domEntries = $dom->find('.stage-live');
foreach ($domEntries as $domEntry) {
    if (!$domEntry instanceof Dom\HtmlNode) {
        continue;
    }

    $entry = [];
    $entry['time'] = fetchTextByClass($domEntry, 'cell_ad');
    $entry['timer'] = fetchTextByClass($domEntry, 'cell_aa');
    $entry['team-home'] = fetchTextByClass($domEntry, 'cell_ab');
    $entry['score'] = fetchTextByClass($domEntry, 'cell_sa');
    $entry['team-away'] = fetchTextByClass($domEntry, 'cell_ac');
    $entries[] = $entry;
}

function fetchTextByClass(Dom\HtmlNode $domEntry, string $className): ?string
{
    /** @var \PHPHtmlParser\Dom\Collection $items */
    $items = $domEntry->find('.' . $className);

    return trim(implode('',
        array_map(
            function (Dom\InnerNode $node) {
                return str_replace('&nbsp;', ' ', strip_tags($node->outerHtml()));
            },
            $items->toArray()
        )
    ));
}

foreach ($entries as $entry) {
    echo sprintf(
        'time: %s, timer: %s, team-home: %s, score: %s, team-away: %s',
        $entry['time'],
        $entry['timer'],
        $entry['team-home'],
        $entry['score'],
        $entry['team-away']
    ), PHP_EOL;
}
