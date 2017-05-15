<?php
use Affinity4\Template\Engine;

require_once __DIR__ . '/vendor/autoload.php';

class Post
{
    public $title = 'Post title goes here...';
    public $content = 'Content goes here...';
}

$template = new Engine;
$template->render(
    'tests/views/foreach-loop-with-keys-and-values.php',
    [
        'post' => (array) new Post
    ]
);