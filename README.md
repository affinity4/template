# Template

[![Build Status](https://travis-ci.org/affinity4/template.svg?branch=master)](https://travis-ci.org/affinity4/template)

Simple template engine with optional syntax which is easy to learn. Can use plain PHP also.

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