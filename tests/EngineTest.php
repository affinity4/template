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
namespace Affinity4\Template\Tests;

use org\bovigo\vfs\vfsStream;
use Affinity4\Template\Engine;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
    private $vfs;
    private $template;
    
    public function setUp()
    {
        $this->vfs = vfsStream::setup('tests');
        $this->template = new Engine;
    }
    
    public function testAddTokenAndGetTokens()
    {
        $expected = $this->template->getTokens();
        $expected[] = [
            'pattern' => '/<!-- {{ var }} -->/',
            'replacement' => '<?= $var ?>',
            'callback' => false
        ];
        
        $n = count($expected) - 1;
        $this->template->addToken($expected[$n]['pattern'], $expected[$n]['replacement'], $expected[$n]['callback']);
        
        $this->assertArraySubset($expected, $this->template->getTokens());
    }
    
    public function testSetStreamAndGetStream()
    {
        $this->template->setStream('Test');
        $this->assertEquals('Test', $this->template->getStream());
    }
    
    public function testCompile()
    {
        $this->template->setStream('<!-- :test -->');
        $this->template->compile($this->template->getStream());
        
        $this->assertEquals('<?= $test ?>', $this->template->getStream());
    }
    
    public function testRenderVariables()
    {
        $expected = <<<VAR
var
var_with_underscores
varWithCamelCase
var_withNumbers_01
VAR;

        ob_start();
        $this->template->render(
            'tests/views/echo-variables.php',
            [
                'var'                   => 'var',
                'var_with_underscores'  => 'var_with_underscores',
                'varWithCamelCase'      => 'varWithCamelCase',
                'var_withNumbers_01'    => 'var_withNumbers_01'
            ]
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderIfStatements()
    {
        $expected = <<<EXPECTED
Shown if :var equals 'var'

Else show if :var_with_underscores is not 'var_not_with_underscores'

Elseif show when :var_with_underscores is 'var_with_underscores'

EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/if-statements.php',
            [
                'var'                  => 'var',
                'var_with_underscores' => 'var_with_underscores',
                'varWithCamelCase'     => 'varWithCamelCase',
                'var_withNumbers_01'   => 'var_withNumbers_01'
            ]
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderEachLoop()
    {
        $expected = <<<EXPECTED
<ul>
<li>one</li>
<li>two</li>
<li>three</li>
</ul>
EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/each-loop.php',
            [
                'items' => ['one', 'two', 'three']
            ]
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderForeachLoop()
    {
        $expected = <<<EXPECTED
<ul>
<li>one</li>
<li>two</li>
<li>three</li>
</ul>
EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/foreach-loop.php',
            [
                'items' => ['one', 'two', 'three']
            ]
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderForeachLoopWithKeysAndValues()
    {
        $expected = <<<EXPECTED
<article>
<h1>Post title goes here...</h1>
<div>Content goes here...</div>
</article>

EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/foreach-loop-with-keys-and-values.php',
            [
                'posts' => [
                    [
                        'title' => 'Post title goes here...',
                        'content' => 'Content goes here...'
                    ]
                ]
            ]
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderForLoop()
    {
        $expected = <<<EXPECTED
<ul>
<li>1</li>
<li>2</li>
<li>3</li>
</ul>
EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/for-loop.php'
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testRenderWhileLoop()
    {
        $expected = <<<EXPECTED
<ul>
<li>1</li>
<li>2</li>
<li>3</li>
</ul>
EXPECTED;
        
        ob_start();
        $this->template->render(
            'tests/views/while-loop.php'
        );
        $output = ob_get_clean();
        $this->assertEquals($expected, $output);
    }
    
    public function testSetViewPathAndGetViewPath()
    {
        ob_start();
        $this->template->render('tests/views/extends.php');
        $output = ob_get_clean();
    
        $this->assertEquals('tests/views/extends.php', $this->template->getViewPath());
    }
    
    public function testSetLayoutAndGetLayout()
    {
        ob_start();
        $this->template->render('tests/views/extends.php');
        $output = ob_get_clean();
        
        $this->assertEquals('tests/views/layout/master.php', $this->template->getLayout());
    }
    
    public function testAddBlocks()
    {
        $expected = [
            'slave'       => [
                'content' => sprintf('Content%1$sShould override Master layout content%1$s', PHP_EOL)
            ],
            'master' => [
                'content' => 'Master layout',
                'sidebar' => 'Sidebar'
            ]
        ];
        
        ob_start();
        $this->template->render('tests/views/extends.php');
        $output = ob_get_clean();
    
        $this->assertEquals($expected, $this->template->getBlocks());
    }
    
    public function testCompileBlocks()
    {
        $expected = sprintf('Content%1$sShould override Master layout content%1$s%1$sNot in block%1$s%1$sSidebar', PHP_EOL);
    
        ob_start();
        $this->template->render('tests/views/extends.php');
        $output = ob_get_clean();
    
        $this->assertEquals($expected, $output);
    }
}
