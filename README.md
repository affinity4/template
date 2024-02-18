# Template

Full-featured template engine with optional syntax which is easy to learn. Can use plain PHP also.

## Features
 - HTML Comment syntax
 - Can use plain PHP in templates if needed
 - Add new syntax if needed.

## Installation

Affinity4/Template is available via composer:

```bash
composer require affinity4/template
```

or

```json
{
    "require": {
        "affinity4/template": "^1.1"
    }
}
```

## Syntax
Output a variables:

```html
<h1><!-- :title --></h1>
```

Set a variable:

```html
<!-- :showTitle = true -->
```

To get an array item by key, such as `$post['title']`:

```html
<!-- :post.title -->
```

If statement:

```html
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

```html
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

```html
<!-- :i = 1 -->
<!-- @while :i <= count(:items) -->
    Number: <!-- :i --><br />
<!-- :i++ -->
<!-- @/while -->

```

For loop:

```html
<!-- @for :i = 1; :i <= 3; :i++ -->
    Number: <!-- :i --><br />
<!-- @/for -->
```

## Layouts and Blocks

You can extend master layouts the same way as you would in any other template engine such as Twig or Blade.

Create a master layout with sections to be overridden in each view file:

File: `views/layout/master.php`

```html
<!DOCKTYPE html>
<html>
<head>
    <title><!-- @block title -->This can be overridden<!-- @/block -->: Site description</title>
    
    <link href="/assets/css/main.css" rel="stylesheet">
    <!-- @block css -->
    <!-- Each view can add custom CSS here -->
    <!-- @/block -->
    
    <script src="/assets/js/jquery.js"></script>
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

```html
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

```php
use Affinity4\Template;

$template = new Template\Engine(new Template\Syntax);

$tempalte->render('views/home.php', ['page_title' => 'Home']);
```

## Usage
To render a template:

```php
use Affinity4\Template;

$template = new Template\Engine(new Template\Syntax);

$template->render('views/home.php', ['title' => 'Home Page']);
```

If you want to add new syntax you can use the `addToken` method after initializing the template engine.

```php
use Affinity4\Template;

$template = new Template\Engine(new Template\Syntax);

$template->addRule('/\{\{ ([\w\d+]) \}\}/', '<?= $$1 ?>');

$template->render('views/home.php', ['title' => 'Home Page']);
```

You can also pass a callable as the second argument to the `addToken` method to use `preg_replace_callback` instead for the replacement.

```php
use Affinity4\Template;

$template = new Template\Engine(new Template\Syntax);

$template->addRule('/\{\{ ([\w\d]+) \}\}/', function ($statement) {
    return '<?php echo $' . $statement[1] . ' ?>'; 
});

$template->render('views/home.php', ['title' => 'Home Page']);
``` 

## Overriding Syntax

One thing which is quite original about Affinity4 Template is that it allows you to replace the Syntax class with your won simple class to create a template language of your own. 

It easy to add all the features currently in Affinity4 Template by simply extending the Affinity4\Template\Tokenizer class and implementing the Affinity4\Template\SyntaxInterface. From there you need only add rules for variables, loops etc. and extend and block syntax. If you do not add extend or block rules that functionality will simply not be available.
 
Here is an example of creating a Blade style syntax of your own

```php
<?php
namespace Your\Template\Syntax\Blade2;

use Affinity4\Template;

class Blade2 extends Affinity4\Template\Tokenizer implements Affinity4\Template\SyntaxInterface
{
    public function __construct()
    {
        $this->addRule('/\{\{ ?(\$.*) ?\}\}/', '<?php echo htmlspecialchars($1, ENT_QUOTES, 'UTF-8'); ?>');
        
        $this->addRule('/@if ?\(\(.*))/', '<?php if ($1) : ?>');
        $this->addRule('/@elseif ?\(\(.*))/', '<?php elseif ($1) : ?>');
        $this->addRule('/@else/', '<?php else : ?>');
        $this->addRule('/@endif/', '<?php endif; ?>');
        
        $this->addRule('/@foreach ?\(\(.*))/', '<?php foreach ($1) : ?>');
        $this->addRule('/@endforeach/', '<?php foreach ($1) : ?>');
        
        // For, while etc...
        
        $this->addExtendsRule('/@extends\((.*)\)/');
        $this->addSectionRule('/@section\('(.*)'\)/', '/@endsection/');
    }
}

```

You then simply use dependency injection when calling the Template Engine class

```php
require_once __DIR__ . '/vendor/autoload.php';
 
use Affinity4\Template\Engine;
use Your\Template\Syntax\Blade2;

$blade2 = new Engine(new Blade2);
$blade2->render('views/home.blade', ['title' => 'Blade 2']);
```

The your layout template (views/layout/master.blade) can be:

```html
<!doctype html>
<html>
<head>
    <title>@section('title') Default to be overriden @endsection</title>
</head>
<body>
    <h1>
    @section('title')
        Title here...
    @endsection
    </h1>
</body>
</html>
```

In views/home.blade...

```html
@extends('layouts/master.blade')

@section('title')
  {{ $title }}
@endsection
```

## Tests

Run tests:

```bash
vendor/bin/phpunit
```

## Licence
(c) 2017 Luke Watts (Affinity4.ie)

This software is licensed under the MIT license. For the
full copyright and license information, please view the
LICENSE file that was distributed with this source code.
