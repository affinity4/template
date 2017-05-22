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
 *
 * @since   1.0.0
 *
 * @package Affinity4\Template
 */
class Engine
{
    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @var \Affinity4\Template\SyntaxInterface
     */
    private $syntax;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.0
     *
     * @var
     */
    private $stream;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @var
     */
    private $view_path;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @var
     */
    private $view_dir;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @var
     */
    private $layout;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @var
     */
    private $blocks = [];

    public function __construct(SyntaxInterface $syntax)
    {
        $this->syntax = $syntax;
    }

    /**
     * Set the stream.
     *
     * NOTE: Mostly used for unit testing the stream.
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
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
     *
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
     *
     * @since  1.0.0
     *
     * @param $stream
     *
     * @return string
     */
    public function compile($stream)
    {
        $this->setStream($stream);

        foreach ($this->syntax->getRules() as $rule) {
            $this->stream = ($rule['callback'])
                ? preg_replace_callback($rule['pattern'], $rule['replacement'], $this->getStream())
                : preg_replace($rule['pattern'], $rule['replacement'], $this->getStream());
        }

        return $this->getStream();
    }

    /**
     * Set the current view path.
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @param string $view_path
     */
    public function setViewPath($view_path)
    {
        $this->view_path = $view_path;
    }

    /**
     * Get the current view path
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
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
     *
     * @since  1.0.2
     *
     * @param string $view_dir
     */
    public function setViewDir($view_dir)
    {
        $this->view_dir = $view_dir;
    }

    /**
     * Get the current view directory
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
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
     *
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
     *
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
     *
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
     *
     * @since  1.0.2
     *
     * @param $file
     */
    public function layout($file)
    {
        $this->setStream(file_get_contents($file));
        $this->setBlocks($file, false); // Add child blocks

        if (preg_match($this->syntax->getLayoutRule(), $this->getStream(), $matches)) {
            $this->setLayout($this->getViewDir() . '/' . trim($matches[1]));
            $this->setBlocks($this->getLayout()); // Add Parent blocks
        }
    }

    /**
     * Sets the blocks for the current view
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @param $file_name
     * @param $layout bool
     *
     * @return void
     */
    public function setBlocks($file_name, $layout = true)
    {
        $file = fopen($file_name, 'r');

        $in_block = false;
        $type = ($layout) ? 'master' : 'slave';
        $block_name = '';
        $i = 0;
        while (!feof($file)) {
            $line = fgets($file);

            if (preg_match(sprintf(
                '%1$s%2$s(.*)%3$s%1$s',
                substr($this->syntax->getBlockRuleByKey('opening_tag'), 0, 1),
                substr($this->syntax->getBlockRuleByKey('opening_tag'), 1, -1),
                substr($this->syntax->getBlockRuleByKey('closing_tag'), 1, -1
                )), $line, $matches)) {
                $block_name = trim($matches[1]);
                $this->blocks[$type][$block_name] = $matches[2];
            } else {
                if (preg_match($this->syntax->getBlockRuleByKey('opening_tag'), $line, $matches)) {
                    $in_block = true;
                    $block_name = trim($matches[1]);
                    $this->blocks[$type][$block_name] = '';
                }

                if ($in_block) {
                    if (!preg_match($this->syntax->getBlockRuleByKey('opening_tag'), $line) && !preg_match($this->syntax->getBlockRuleByKey('closing_tag'), $line)) {
                        $this->blocks[$type][$block_name] .= $line;
                    }
                }

                if (preg_match($this->syntax->getBlockRuleByKey('closing_tag'), $line)) {
                    $in_block = false;
                }
            }

            ++$i;
        }
        fclose($file);
    }

    /**
     * Get the array of blocks
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Compile layout and blocks into the final output
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.2
     *
     * @return string
     */
    public function compileLayout($current_layout, $current_blocks)
    {
        $file = fopen($current_layout, 'r+');

        $in_block = false;
        $block_name = '';
        $i = 0;
        $new_content = '';
        while (!feof($file)) {
            $line = fgets($file);

            if (preg_match(sprintf(
                    '%1$s%2$s(.*)%3$s%1$s',
                    substr($this->syntax->getBlockRuleByKey('opening_tag'), 0, 1),
                    substr($this->syntax->getBlockRuleByKey('opening_tag'), 1, -1),
                    substr($this->syntax->getBlockRuleByKey('closing_tag'), 1, -1)
                ), $line, $matches)) {
                $block_name = trim($matches[1]);
                $line = (array_key_exists($block_name, $current_blocks['slave'])) ? $current_blocks['slave'][$block_name] : $current_blocks['master'][$block_name];
            } else {
                if (preg_match($this->syntax->getBlockRuleByKey('opening_tag'), $line, $matches)) {
                    $in_block = true;
                    $block_name = trim($matches[1]);
                    $line = '';
                }

                if ($in_block) {
                    if (!preg_match($this->syntax->getBlockRuleByKey('opening_tag'), $line) && !preg_match($this->syntax->getBlockRuleByKey('closing_tag'), $line)) {
                        if (array_key_exists($block_name, $current_blocks['slave'])) {
                            $line = '';
                        }
                    }
                }

                if (preg_match($this->syntax->getBlockRuleByKey('closing_tag'), $line)) {
                    $in_block = false;
                    $line = (array_key_exists($block_name, $current_blocks['slave'])) ? $current_blocks['slave'][$block_name] : $current_blocks['master'][$block_name];
                }
            }

            $new_content .= $line;
            ++$i;
        }
        fclose($file);

        return $new_content;
    }

    /**
     * Render the view with parameters
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.0.0
     *
     * @param string $view
     * @param array  $params
     */
    public function render($view, $params = [])
    {
        if (!empty($params)) {
            extract($params);
        }

        $viewArray = explode('/', $view);
        $this->setViewPath(implode('/', $viewArray));
        $this->setViewDir(preg_replace('~^(.*)/(.*)$~', '$1', $this->getViewPath()));

        vfsStream::setup($this->getViewPath());

        $file = vfsStream::url($view . '.php');

        $this->layout($view);
        if ($this->hasLayout()) {
            $this->setStream($this->compileLayout($this->getLayout(), $this->getBlocks()));
        }

        file_put_contents($file, $this->compile($this->getStream()));

        ob_start();
        include $file;
        ob_end_flush();
    }
}
