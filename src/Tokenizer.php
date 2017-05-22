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
 * Tokenizer Class
 *
 * @author Luke Watts <luke@affinity4.ie>
 *
 * @since  1.2.0
 *
 * @package Affinity4\Template
 */
class Tokenizer
{
    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.12.0
     *
     * @var
     */
    private $rules;

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @var
     */
    private $block = [];

    /**
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @var
     */
    private $layout_rule;

    /**
     * Add a token to be applied when compiling
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @param $pattern
     * @param $replacement
     *
     * @return void
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
     *
     * @since  1.2.0
     *
     * @return array
     */
    public function getRules()
    {
        return (array) $this->rules;
    }

    /**
     * Set the layout (aka extends) rule
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @param mixed $layout_rule
     */
    public function setLayoutRule($layout_rule)
    {
        $this->layout_rule = $layout_rule;
    }

    /**
     * Get the layout (aka extends) rule
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @return mixed
     */
    public function getLayoutRule()
    {
        return $this->layout_rule;
    }

    /**
     * Set the Block (aka section) rule (aka extends)
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @param string $opening_tag
     * @param string $closing_tag
     *
     * @return void
     */
    public function setBlockRule($opening_tag, $closing_tag)
    {
        $this->block['opening_tag'] = $opening_tag;
        $this->block['closing_tag'] = $closing_tag;
    }

    /**
     * Get block rule by key
     *
     * @author Luke Watts <luke@affinity4.ie>
     *
     * @since  1.2.0
     *
     * @param $key string
     *
     * @return string
     */
    public function getBlockRuleByKey($key)
    {
        return $this->block[$key];
    }
}
