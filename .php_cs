<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                              => true,
        '@Symfony'                           => true,
        'array_syntax'                       => ['syntax' => 'short'],
        'binary_operator_spaces'             => ['align_double_arrow' => true],
        'concat_space'                       => ['spacing' => 'one'],
        'linebreak_after_opening_tag'        => true,
        'new_with_braces'                    => false,
        'no_leading_namespace_whitespace'    => true,
        'no_blank_lines_before_namespace'    => true,
        'phpdoc_annotation_without_dot'      => true,
        'phpdoc_no_empty_return'             => false,
        'phpdoc_no_package'                  => false,
        'phpdoc_order'                       => true,
        'phpdoc_summary'                     => false,
        'protected_to_private'               => false,
        'single_blank_line_before_namespace' => false,
        'trailing_comma_in_multiline_array'  => false,
        'trim_array_spaces'                  => false,
    ])
    ->setFinder($finder);
