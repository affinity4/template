<?php
/**
 * This file is part of Affinity4\Template.
 *
 * (c) 2017 Luke Watts <luke@affinity4.ie>
 *
 * This software is licensed under the MIT license. For the
 * full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Affinity4\Template;

/**
 * Syntax Class
 *
 * @author  Luke Watts <luke@affinity4.ie>
 * @since   1.1.0
 *
 * @package Affinity4\Template
 */
class Syntax
{
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.1.0
     *
     * @var
     */
    protected $rules;

    /**
     * Syntax Constructor
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.1.0
     */
    public function __construct()
    {
        /*
         * Syntax: <!-- :var = 1 -->
         * PHP: <?php $var = 1; ?>
         */
        $this->addRule('/<!-- ?:([\w\d]+)( = .*) ?-->/', '<?php $$1$2; ?>');

        /*
         * Syntax: <!-- :some_var_02 -->
         * PHP: <?= $some_var_02 ?>
         */
        $this->addRule('/<!-- :([\w\d]+) -->/', '<?= $$1 ?>');
        $this->addRule('/<!--:([\w\d]+) ?-->/', '<?= $$1 ?>');

        /*
         * Syntax: <!-- :post.title -->
         * PHP: <?= $post['title'] ?>
         */
        $this->addRule('/<!-- :([\w\d]+)\.(.*) -->/', '<?= $$1[\'$2\'] ?>');
        $this->addRule('/<!--:([\w\d]+)\.(.*) ?-->/', '<?= $$1[\'$2\'] ?>');

        /*
         * Syntax: <!-- @foreach :item in :items  --> or <!-- @each :item in :items -->
         * PHP: <?php foreach ($items as $item) : ?>
         */
        $this->addRule('/<!-- ?@foreach :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$2 as $$1) : ?>');
        $this->addRule('/<!-- ?@each :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$2 as $$1) : ?>');

        /*
         * Syntax: <!-- @foreach :key, :value in :items  --> or <!-- @each :key, :value in :items -->
         * PHP: <?php foreach ($items as $key => $value) : ?>
         */
        $this->addRule('/<!-- ?@foreach :([\w\d]+), :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$3 as $$1 => $$2) : ?>');
        $this->addRule('/<!-- ?@each :([\w\d]+), :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$3 as $$1 => $$2) : ?>');

        /*
         * Syntax: <!-- @if :something is true -->
         * To: <!-- @if :something === true -->
         */
        $this->addRule('/<!-- ?@if ((.*) is (.*)) ?-->/', function ($text) {
            return '<!-- @if ' . str_replace(' is ', ' === ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @if :something === true and :somethingElse !== true  -->
         * To: <!-- @if :something === true && :somethingElse !== true -->
         */
        $this->addRule('/<!-- ?@if ((.*) and (.*)) ?-->/', function ($text) {
            return '<!-- @if ' . str_replace(' and ', ' && ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @if :something === true or :somethingElse === true  -->
         * To: <!-- @if :something === true || :somethingElse === true -->
         */
        $this->addRule('/<!-- ?@if ((.*) or (.*)) ?-->/', function ($text) {
            return '<!-- @if ' . str_replace(' or ', ' || ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @if :showList is true and :something is false or :somethingElse -->
         * PHP: <?php if ($showList === true && $something === false || $somethingElse) : ?>
         */
        $this->addRule('/<!-- ?@if ((.*):([\w\d]+)(.*)) ?-->/', function ($var) {
            return '<?php if (' . preg_replace('/:([\w\d])/', '$$1', $var[1]) . ') : ?>';
        });

        /*
         * Syntax: <!-- @elseif :something is true -->
         * To: <!-- @elseif :something === true -->
         */
        $this->addRule('/<!-- ?@elseif ((.*) is (.*)) ?-->/', function ($text) {
            return '<!-- @elseif ' . str_replace(' is ', ' === ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @elseif :something === true and :somethingElse !== true  -->
         * To: <!-- @elseif :something === true && :somethingElse !== true -->
         */
        $this->addRule('/<!-- ?@elseif ((.*) and (.*)) ?-->/', function ($text) {
            return '<!-- @elseif ' . str_replace(' and ', ' && ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @elseif :something === true or :somethingElse === true  -->
         * To: <!-- @elseif :something === true || :somethingElse === true -->
         */
        $this->addRule('/<!-- ?@elseif ((.*) or (.*)) ?-->/', function ($text) {
            return '<!-- @elseif ' . str_replace(' or ', ' || ', $text[1]) . ' -->';
        });

        /*
         * Syntax: <!-- @elseif :showList is true and :something is false or :somethingElse -->
         * PHP: <?php elseif ($showList === true && $something === false || $somethingElse) : ?>
         */
        $this->addRule('/<!-- ?@elseif ((.*):([\w\d]+)(.*)) ?-->/', function ($var) {
            return '<?php elseif (' . preg_replace('/:([\w\d])/', '$$1', $var[1]) . ') : ?>';
        });

        /*
         * Syntax: <!-- @else -->
         * PHP: <?php else : ?>
         */
        $this->addRule('/<!-- ?@else ?-->/', '<?php else : ?>');

        /*
         * Syntax: <!-- :i++ -->
         * PHP: <?php $i++ ?>
         */
        $this->addRule('/<!-- :([\w\d]+)(\+\+|--) ?-->/', '<?php $$1$2 ?>');
        $this->addRule('/<!--:([\w\d]+)(\+\+|--) ?-->/', '<?php $$1$2 ?>');

        /*
         * Syntax: <!-- @while :i <= count(:items) -->
         * PHP: <?php while ($i <= count($items)) : ?>
         */
        $this->addRule('/<!-- ?@while (.*) ?-->/', function ($statement) {
            $stream = preg_replace('/:([\w\d]+)/', '$$1', $statement[1]);

            return '<?php while (' . $stream . ') : ?>';
        });

        /*
         * Syntax: <!-- @for :i = 1; :i <= 10; :i++ -->
         * PHP: <?php for ($i = 1; $i <= 10; :i++) : ?>
         */
        $this->addRule('/<!-- ?@for (.*) ?-->/', function ($statement) {
            return '<?php for (' . preg_replace('/:([\w\d]+)/', '$$1', $statement[1]) . ') : ?>';
        });

        /*
         * Syntax: <!-- @endeach --> or <!-- @/each -->
         * PHP: <?php endforeach ?>
         */
        $this->addRule('/<!-- ?@endeach ?-->/', '<?php endforeach ?>');
        $this->addRule('~<!-- ?@/each ?-->~', '<?php endforeach ?>');

        /*
         * Syntax: <!-- @/if -->||<!-- @/foreach -->||<!-- @/for -->||<!-- @/while -->
         * PHP: <?php endif ?>||<?pgp endforeach ?>||<?php endfor ?>||<?php endwhile ?>
         */
        $this->addRule('~<!-- ?@/(if|foreach|for|while) ?-->~', '<?php end$1 ?>');

        /*
         * Syntax: <!-- @endif -->||<!-- @endforeach -->||<!-- @endfor -->||<!-- @endwhile -->
         * PHP: <?php endif ?>||<?pgp endforeach ?>||<?php endfor ?>||<?php endwhile ?>
         */
        $this->addRule('/<!-- ?@end(if|foreach|for|while) ?-->/', '<?php end$1 ?>');

        /*
         * Syntax: <!-- @endblock -->||<!-- @/block -->
         * PHP: ''
         */
        $this->addRule('/<!-- ?@endblock ?-->/', '');
        $this->addRule('~<!-- ?@/block ?-->~', '');
    }

    /**
     * Add a token to be applied when compiling
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.1.0
     *
     * @param $pattern
     * @param $replacement
     */
    public function addRule($pattern, $replacement)
    {
        $this->rules[] = (is_callable($replacement))
            ? ['pattern' => $pattern, 'replacement' => $replacement, 'callback' => true]
            : ['pattern' => $pattern, 'replacement' => $replacement, 'callback' => false];
    }

    /**
     * Get array of rules
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.1.0
     *
     * @return array
     */
    public function getRules()
    {
        return (array) $this->rules;
    }
}
