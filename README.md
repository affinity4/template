# Template

[![Build Status](https://travis-ci.org/affinity4/template.svg?branch=master)](https://travis-ci.org/affinity4/template)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/affinity4/template/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/affinity4/template/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/affinity4/template/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/affinity4/template/?branch=master)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/089c34a2-1ffd-4b0c-a327-ec192d8f9a06/big.png)](https://insight.sensiolabs.com/projects/089c34a2-1ffd-4b0c-a327-ec192d8f9a06)

Full-featured template engine with optional syntax which is easy to learn. Can use plain PHP also.

## Features
 - HTML Comment syntax
 - Can use plain PHP in templates if needed
 - Add new syntax if needed.

## Installation
Affinity4/Template is available via composer:

`composer require affinity4/template`

or

```
{
    "require": {
        "affinity4/template": "^1.0"
    }
}
```

## Syntax
Output a variables:

```
<h1><!-- :title --></h1>
```

Set a variable:

```
<!-- :showTitle = true -->
```

To get an array item by key, such as `$post['title']`:

```
<!-- :post.title -->
```

If statement:

```
<!-- @if :showTitle is true and :something is false or :somethingElse -->
    <h1><!-- :title --></h1>
<!-- @/if -->

<!-- @if :showTitle -->
    <h1><!-- :title --></h1>
<!-- @elseif :something and :showTitle -->
    <h1><!-- :title --></h1>
    <h2>Something</h1>
<!-- @else -->
    <h1>Default Title</h1>
<!-- @/if -->
```

Foreach loop:

```
<ul>
<!-- @each :item in :items -->
    <li><!-- :item --></li>
<!-- @/each -->
</ul>

<!-- @each :id, :post in :posts -->
<article>
<h1><!-- :post.title --></h1>
<div><!-- :post.content --></div>
</article>
<!-- @/each -->

```
__NOTE:__ Can be `@foreach` also.

While loop:

```
<!-- :i = 1 -->
<!-- @while :i <= count(:items) -->
    Number: <!-- :i --><br />
<!-- :i++ -->
<!-- @/while -->

```

For loop:

```
<!-- @for :i = 1; :i <= 3; :i++ -->
    Number: <!-- :i --><br />
<!-- @/for -->
```

## Layouts and Blocks

You can extend master layouts the same way as you would in any other template engine such as Twig or Blade.

Create a master layout with sections to be overridden in each view file:

File: `views/layout/master.php`

```
<!DOCKTYPE html>
<html>
<head>
    <title><!-- @block title -->This can be overridden<!-- @/block -->: Site description</title>
    
    <link href="/assets/css/main.css" rel="stylesheet">
    <!-- @block css -->
    <!-- Each view can add custom CSS here -->
    <!-- @/block -->
    
    <script src="/assets/js/jquery.js">
    <!-- @block js-head -->
    <!-- Each view can add custom JS here -->
    <!-- @/block -->
</head>
<body>
    <main>    
        <h1><!-- @block page-title -->Default Page<!-- @/block --></h1>
        
        <!-- @block content -->
        <p>Page content goes here...</p>
        <!-- @/block -->
    </main>

    <!-- @block js-footer -->
    <!-- Each view can add custom JS here -->
    <!-- @/block -->
</body>
</html>
```

Then in you view:

File: `views/home.php`

```
<!-- @extends layout/master.php -->

<!-- @block title --><!-- :page_title --><!-- @/block -->

<!-- @block css -->
<link href="/assets/css/home.css" rel="stylesheet">
<!-- @/block -->

<!-- @block page-title --><!-- :page_title --><!-- @/block -->

<!-- @block content -->
<p>This is the homepage</p>
<!-- @/block -->
```

Then simply render the view:

File: `index.php`

```
$template = new Affinity4\Template\Engine;

$tempalte->render('views/home.php', ['page_title' => 'Home']);
```

## Usage
To render a template:

```
use Affinity4\Template\Engine;

$template = new Engine;

$template->render('views/home.php', ['title' => 'Home Page']);
```

If you want to add new syntax you can use the `addToken` method after initializing the template engine.

``` 
use Affinity4\Template\Engine;

$template = new Engine;

$template->addToken('/\{\{ ([\w\d+]) \}\}/', '<?= $$1 ?>');

$template->render('views/home.php', ['title' => 'Home Page']);
```

You can also pass a callable as the second argument to the `addToken` method to use `preg_replace_callback` instead for the replacement.

``` 
use Affinity4\Template\Engine;

$template = new Engine;

$template->addToken('/\{\{ ([\w\d]+) \}\}/', function ($statement) {
    return '<?php echo $' . $statement[1] . ' ?>'; 
});

$template->render('views/home.php', ['title' => 'Home Page']);
``` 

## Tests

Run tests:

```
vendor/bin/phpunit
```

## Licence
(c) 2017 Luke Watts (Affinity4.ie)

This software is licensed under the MIT license. For the
full copyright and license information, please view the
LICENSE file that was distributed with this source code.
