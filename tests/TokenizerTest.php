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
namespace Affinity4\Template\Tests;

use Affinity4\Template\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    private $tokenizer;

    public function setUp(): void
    {
        $this->tokenizer = new Tokenizer;
    }

    public function testAddRuleAndGetRules()
    {
        $expected = $this->tokenizer->getRules();
        $expected[] = [
            'pattern'     => '/<!-- {{ var }} -->/',
            'replacement' => '<?= $var ?>',
            'callback'    => false
        ];

        $n = count($expected) - 1;
        $this->tokenizer->addRule($expected[$n]['pattern'], $expected[$n]['replacement'], $expected[$n]['callback']);

        $this->assertArraySubset($expected, $this->tokenizer->getRules());
    }
}
