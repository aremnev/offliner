<?php
use \Thumbtack\IndexerBundle\Models\IndexerProcessor;
use \Thumbtack\IndexerBundle\Entity\Page;

$response = '<h1 style="text-align: center"> This page have status:';
/** @var Page $page */
switch ($page->getStatus()) {
    default:
        $response .= ' not in database</h1>';
        break;
    case IndexerProcessor::STATUS_AWAITING;
        $response .= ' in index query</h1>';
        break;
    case IndexerProcessor::STATUS_PROGRESS:
        $response .= ' indexing in process</h1>';
        break;
    case IndexerProcessor::STATUS_READY:
        $response = '<div style="background:#fff;border:1px solid #999;margin:-1px -1px 0;padding:0;">
            <div style="background:#eee;border:1px solid #fefefe;color:#000;font:13px arial,sans-serif;font-weight:normal;margin:3px;padding:5px;text-align:left">
                This is saved version by tt-indexer service. Original content may be changed (Last indexed:' . $page->getDate()->format('Y-m-d') . ')</div>
        </div>' . '<div style="position:relative">' . $page->getHtml() . '</div>';
        break;
}
echo $response;