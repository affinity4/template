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

use org\bovigo\vfs\vfsStream;

/**
 * Engine Class
 *
 * @author  Luke Watts <luke@affinity4.ie>
 * @since   1.0.0
 *
 * @package Affinity4\Template
 */
class Engine
{
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @var
     */
    protected $tokens;
    
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @var
     */
    private $stream;
    
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @var
     */
    private $view_path;
    
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @var
     */
    private $view_dir;
    
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @var
     */
    private $layout;
    
    /**
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @var
     */
    private $blocks = [];
    
    public function __construct()
    {
        /*
         * Syntax: <!-- :var = 1 -->
         * PHP: <?php $var = 1; ?>
         */
        $this->addToken('/<!-- ?:([\w\d]+)( = .*) ?-->/', '<?php $$1$2; ?>');
        
        /*
         * Syntax: <!-- :some_var_02 -->
         * PHP: <?= $some_var_02 ?>
         */
        $this->addToken('/<!-- :([\w\d]+) -->/', '<?= $$1 ?>');
        $this->addToken('/<!--:([\w\d]+) ?-->/', '<?= $$1 ?>');
    
        /*
         * Syntax: <!-- :post.title -->
         * PHP: <?= $post['title'] ?>
         */
        $this->addToken('/<!-- :([\w\d]+)\.(.*) -->/', '<?= $$1[\'$2\'] ?>');
        $this->addToken('/<!--:([\w\d]+)\.(.*) ?-->/', '<?= $$1[\'$2\'] ?>');
        
        /*
         * Syntax: <!-- @foreach :item in :items  --> or <!-- @each :item in :items -->
         * PHP: <?php foreach ($items as $item) : ?>
         */
        $this->addToken('/<!-- ?@foreach :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$2 as $$1) : ?>');
        $this->addToken('/<!-- ?@each :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$2 as $$1) : ?>');
    
        /*
         * Syntax: <!-- @foreach :key, :value in :items  --> or <!-- @each :key, :value in :items -->
         * PHP: <?php foreach ($items as $key => $value) : ?>
         */
        $this->addToken('/<!-- ?@foreach :([\w\d]+), :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$3 as $$1 => $$2) : ?>');
        $this->addToken('/<!-- ?@each :([\w\d]+), :([\w\d]+) in :([\w\d]+) ?-->/', '<?php foreach ($$3 as $$1 => $$2) : ?>');
    
        /*
         * Syntax: <!-- @if :something is true -->
         * To: <!-- @if :something === true -->
         */
        $this->addToken('/<!-- ?@if ((.*) is (.*)) ?-->/', function($text) {
            return '<!-- @if ' . str_replace(' is ', ' === ', $text[1]) . ' -->';
        });
        
        /*
         * Syntax: <!-- @if :something === true and :somethingElse !== true  -->
         * To: <!-- @if :something === true && :somethingElse !== true -->
         */
        $this->addToken('/<!-- ?@if ((.*) and (.*)) ?-->/', function($text) {
            return '<!-- @if ' . str_replace(' and ', ' && ', $text[1]) . ' -->';
        });
    
        /*
         * Syntax: <!-- @if :something === true or :somethingElse === true  -->
         * To: <!-- @if :something === true || :somethingElse === true -->
         */
        $this->addToken('/<!-- ?@if ((.*) or (.*)) ?-->/', function($text) {
            return '<!-- @if ' . str_replace(' or ', ' || ', $text[1]) . ' -->';
        });
    
        /*
         * Syntax: <!-- @if :showList is true and :something is false or :somethingElse -->
         * PHP: <?php if ($showList === true && $something === false || $somethingElse) : ?>
         */
        $this->addToken('/<!-- ?@if ((.*):([\w\d]+)(.*)) ?-->/', function($var) {
            return '<?php if (' . preg_replace('/:([\w\d])/', '$$1', $var[1]) . ') : ?>';
        });
    
        /*
         * Syntax: <!-- @elseif :something is true -->
         * To: <!-- @elseif :something === true -->
         */
        $this->addToken('/<!-- ?@elseif ((.*) is (.*)) ?-->/', function($text) {
            return '<!-- @elseif ' . str_replace(' is ', ' === ', $text[1]) . ' -->';
        });
    
        /*
         * Syntax: <!-- @elseif :something === true and :somethingElse !== true  -->
         * To: <!-- @elseif :something === true && :somethingElse !== true -->
         */
        $this->addToken('/<!-- ?@elseif ((.*) and (.*)) ?-->/', function($text) {
            return '<!-- @elseif ' . str_replace(' and ', ' && ', $text[1]) . ' -->';
        });
    
        /*
         * Syntax: <!-- @elseif :something === true or :somethingElse === true  -->
         * To: <!-- @elseif :something === true || :somethingElse === true -->
         */
        $this->addToken('/<!-- ?@elseif ((.*) or (.*)) ?-->/', function($text) {
            return '<!-- @elseif ' . str_replace(' or ', ' || ', $text[1]) . ' -->';
        });
    
        /*
         * Syntax: <!-- @elseif :showList is true and :something is false or :somethingElse -->
         * PHP: <?php elseif ($showList === true && $something === false || $somethingElse) : ?>
         */
        $this->addToken('/<!-- ?@elseif ((.*):([\w\d]+)(.*)) ?-->/', function($var) {
            return '<?php elseif (' . preg_replace('/:([\w\d])/', '$$1', $var[1]) . ') : ?>';
        });
    
        /*
         * Syntax: <!-- @else -->
         * PHP: <?php else : ?>
         */
        $this->addToken('/<!-- ?@else ?-->/', '<?php else : ?>');
    
        /*
         * Syntax: <!-- :i++ -->
         * PHP: <?php $i++ ?>
         */
        $this->addToken('/<!-- :([\w\d]+)(\+\+|--) ?-->/', '<?php $$1$2 ?>');
        $this->addToken('/<!--:([\w\d]+)(\+\+|--) ?-->/', '<?php $$1$2 ?>');
    
        /*
         * Syntax: <!-- @while :i <= count(:items) -->
         * PHP: <?php while ($i <= count($items)) : ?>
         */
        $this->addToken('/<!-- ?@while (.*) ?-->/', function($statement) {
            $stream = preg_replace('/:([\w\d]+)/', '$$1', $statement[1]);
    
            return '<?php while (' . $stream . ') : ?>';
        });
    
        /*
         * Syntax: <!-- @for :i = 1; :i <= 10; :i++ -->
         * PHP: <?php for ($i = 1; $i <= 10; :i++) : ?>
         */
        $this->addToken('/<!-- ?@for (.*) ?-->/', function($statement) {
            return '<?php for (' . preg_replace('/:([\w\d]+)/', '$$1', $statement[1]) . ') : ?>';
        });
    
        /*
         * Syntax: <!-- @endeach --> or <!-- @/each -->
         * PHP: <?php endforeach ?>
         */
        $this->addToken('/<!-- ?@endeach ?-->/', '<?php endforeach ?>');
        $this->addToken('~<!-- ?@/each ?-->~', '<?php endforeach ?>');
    
        /*
         * Syntax: <!-- @/if -->||<!-- @/foreach -->||<!-- @/for -->||<!-- @/while -->
         * PHP: <?php endif ?>||<?pgp endforeach ?>||<?php endfor ?>||<?php endwhile ?>
         */
        $this->addToken('~<!-- ?@/(if|foreach|for|while) ?-->~', '<?php end$1 ?>');
    
        /*
         * Syntax: <!-- @endif -->||<!-- @endforeach -->||<!-- @endfor -->||<!-- @endwhile -->
         * PHP: <?php endif ?>||<?pgp endforeach ?>||<?php endfor ?>||<?php endwhile ?>
         */
        $this->addToken('/<!-- ?@end(if|foreach|for|while) ?-->/', '<?php end$1 ?>');
    
        /*
         * Syntax: <!-- @endblock -->||<!-- @/block -->
         * PHP: ''
         */
        $this->addToken('/<!-- ?@endblock ?-->/', '');
        $this->addToken('~<!-- ?@/block ?-->~', '');
        
    }
    
    /**
     * Add a token to be applied when compiling
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @param $pattern
     * @param $replacement
     */
    public function addToken($pattern, $replacement)
    {
        if (is_callable($replacement)) {
            $this->tokens[] = ['pattern' => $pattern, 'replacement' => $replacement, 'callback' => true];
        } else {
            $this->tokens[] = ['pattern' => $pattern, 'replacement' => $replacement, 'callback' => false];
        }
    }
    
    public function getTokens()
    {
        return $this->tokens;
    }
    
    /**
     * Set the stream.
     *
     * NOTE: Mostly used for unit testing the stream.
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @param mixed $stream
     */
    public function setStream($stream)
    {
        $this->stream = $stream;
    }
    
    /**
     * Return the stream
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @return mixed
     */
    public function getStream()
    {
        return $this->stream;
    }
    
    /**
     * Compile template syntax to PHP
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @param $stream
     *
     * @return mixed
     */
    public function compile($stream)
    {
        $this->setStream($stream);
        
        foreach ($this->tokens as $token) {
            $this->stream = ($token['callback']) ? preg_replace_callback($token['pattern'], $token['replacement'], $this->getStream()) : preg_replace($token['pattern'], $token['replacement'], $this->getStream());
        }
    }
    
    /**
     * Set the current view path.
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param mixed $view_path
     */
    public function setViewPath($view_path)
    {
        $this->view_path = $view_path;
    }
    
    /**
     * Get the current view path
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return mixed
     */
    public function getViewPath()
    {
        return $this->view_path;
    }
    
    /**
     * Sets the current view directory
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param mixed $view_dir
     */
    public function setViewDir($view_dir)
    {
        $this->view_dir = $view_dir;
    }
    
    /**
     * Get the current view directory
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return mixed
     */
    public function getViewDir()
    {
        return $this->view_dir;
    }
    
    /**
     * Set the current layout to be used with the current view
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param mixed $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
    
    /**
     * Get the current layout to use with the current view
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return mixed
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    public function hasLayout()
    {
        return ($this->getLayout() !== null) ? true : false;
    }
    
    /**
     * Sets layout by mergin all blocks into one file and replacing
     * parent blocks with child blocks where necessary.
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param $file
     */
    public function layout($file)
    {
        $this->setStream(file_get_contents($file));
        $this->setBlocks($file, false); // Add child blocks
    
        if (preg_match('/<!-- ?@extends (.*) ?-->/', $this->getStream(), $matches)) {
            $this->setLayout($this->getViewDir() . '/' . trim($matches[1]));
            $this->setBlocks($this->getLayout()); // Add Parent blocks
        }
    
    
    }
    
    /**
     * @param $file_name
     */
    public function setBlocks($file_name, $layout = true)
    {
        $file      = fopen($file_name, 'r');
    
        $in_block   = false;
        $type = ($layout) ? 'master' : 'slave';
        $block_name = '';
        $i          = 0;
        while (!feof($file)) {
            $line = fgets($file);
            
            if (preg_match('~<!-- ?@block (.*) ?-->(.*)<!-- ?@/block ?-->~', $line, $matches)) {
                $block_name = trim($matches[1]);
                $this->blocks[$type][$block_name] = $matches[2];
            } else {
                if (preg_match('/<!-- ?@block (.*) ?-->/', $line, $matches)) {
                    $in_block                              = true;
                    $block_name                            = trim($matches[1]);
                    $this->blocks[$type][$block_name] = '';
                }
    
                if ($in_block) {
                    if (!preg_match('/<!-- ?@block (.*) ?-->/', $line) && !preg_match('~<!-- ?@/block ?-->~', $line)) {
                        $this->blocks[$type][$block_name] .= $line;
                    }
                }
    
                if (preg_match('~<!-- ?@/block ?-->~', $line)) $in_block = false;
            }
        
            $i++;
        }
        fclose($file);
    }
    
    /**
     * @return mixed
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
    
    public function compileBlocks()
    {
        $this->setStream(file_get_contents($this->getLayout()));
        
        $file = fopen($this->getLayout(), 'r+');
    
        $in_block   = false;
        $block_name = '';
        $i          = 0;
        $new_content = '';
        while (!feof($file)) {
            $line = fgets($file);
        
            if (preg_match('~<!-- ?@block (.*) ?-->(.*)<!-- ?@/block ?-->~', $line, $matches)) {
                $block_name                       = trim($matches[1]);
                $line = (array_key_exists($block_name, $this->getBlocks()['slave'])) ? $this->getBlocks()['slave'][$block_name] : $this->getBlocks()['master'][$block_name];
            } else {
                if (preg_match('/<!-- ?@block (.*) ?-->/', $line, $matches)) {
                    $in_block = true;
                    $block_name = trim($matches[1]);
                    $line = '';
                }
    
                if ($in_block) {
                    if (!preg_match('/<!-- ?@block (.*) ?-->/', $line) && !preg_match('~<!-- ?@/block ?-->~', $line)) {
                        if (array_key_exists($block_name, $this->getBlocks()['slave'])) {
                            $line = '';
                        }
                    }
                }
    
                if (preg_match('~<!-- ?@/block ?-->~', $line)) {
                    $in_block = false;
                    $line = (array_key_exists($block_name, $this->getBlocks()['slave'])) ? $this->getBlocks()['slave'][$block_name] : $this->getBlocks()['master'][$block_name];
                }
            }
            
            $new_content .= $line;
            $i++;
        }
        fclose($file);
        
        $this->setStream($new_content);
    }
    
    /**
     * Render the view with paramaters
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @param string $view
     * @param array $params
     */
    public function render($view, $params = [])
    {
        if (!empty($params)) extract($params);
        
        $viewArray = explode('/', $view);
        $this->setViewPath(implode('/', $viewArray));
        $this->setViewDir(preg_replace('~^(.*)/(.*)$~', '$1', $this->getViewPath()));
        
        vfsStream::setup($this->getViewPath());
        
        $file     = vfsStream::url($view . '.php');
        
        $this->layout($view);
        if ($this->hasLayout()) $this->compileBlocks();
        
        $this->compile($this->getStream());
        
        file_put_contents($file, $this->getStream());
        
        ob_start();
        include $file;
        ob_end_flush();
    }
}
