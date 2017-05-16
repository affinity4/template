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
 * @author Luke Watts <luke@affinity4.ie>
 * @since  1.0.0
 *
 * @package Affinity4\Template
 */
class Engine extends Syntax
{
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
    
    /**
     * Set the stream.
     *
     * NOTE: Mostly used for unit testing the stream.
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.0
     *
     * @param mixed $stream
     *
     * @return void
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
     * @return string
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
     * @return void
     */
    public function compile($stream)
    {
        $this->setStream($stream);
        
        foreach ($this->getRules() as $rule) {
            $this->stream = ($rule['callback'])
                ? preg_replace_callback($rule['pattern'], $rule['replacement'], $this->getStream())
                : preg_replace($rule['pattern'], $rule['replacement'], $this->getStream());
        }
    }
    
    /**
     * Set the current view path.
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param string $view_path
     *
     * @return void
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
     * @return string
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
     * @param string $view_dir
     *
     * @return void
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
     * @return string
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
     *
     * @return void
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

    /**
     * Check if the current view extends a layout
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return bool
     */
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
     *
     * @return void
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
     * Sets the blocks for the current view
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @param $file_name
     *
     * @return void
     */
    public function setBlocks($file_name, $layout = true)
    {
        $file       = fopen($file_name, 'r');
    
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
    
                if (preg_match('~<!-- ?@/block ?-->~', $line)) { $in_block = false;
                }
            }
        
            $i++;
        }
        fclose($file);
    }
    
    /**
     * Get the array of blocks
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Compile blocks into the final output
     *
     * @author Luke Watts <luke@affinity4.ie>
     * @since  1.0.2
     *
     * @return void
     */
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
                $block_name = trim($matches[1]);
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
     * @param array  $params
     *
     * @return void
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
