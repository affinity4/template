<?php
/**
 * This file is part of Affinity4.
 *
 * (c) 2017 Luke Watts <luke@affinity4.ie>
 *
 * This software is licensed under the MIT license. For the
 * full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Affinity4\Template\Syntax;

use Affinity4\Template\Tokenizer;

class Twig extends Tokenizer implements SyntaxInterface
{
    public function __construct()
    {
        /*
         * Twig comments
         *
         * Syntax: {# This is a comment #}
         */
        $this->addRule("/{# (.*) #}/", "<?php // $1 ?>");

        $this->addExtendsRule('~{% extends "?(.*)? %}"|{% extends \'?(.*)\'? %}~');
        $this->addBlockRule('~{% ?block (.*) ?%}~', '{% ?endblock ?%}');

    }
}