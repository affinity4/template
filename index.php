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
$tag = '~<!-- ?@block (.*) ?-->~';
var_dump(sprintf('%1$s%2$s(.*)%3$s%1$s',
    substr($tag, 0, 1),
    substr($tag, 1, -1),
    substr($tag, 1, -1)
));