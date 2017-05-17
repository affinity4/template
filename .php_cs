<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->exclude(['vendor', 'tests'])
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                                       => true,
        '@Symfony'                                    => true,
        'protected_to_private'                        => false,
        'concat_space'                                => ['spacing' => 'one'],
        'linebreak_after_opening_tag'                 => true,
        'trailing_comma_in_multiline_array'           => false,
        'phpdoc_no_package'                           => false,
        'phpdoc_summary'                              => false,
        'new_with_braces'                             => false,
        'no_leading_namespace_whitespace'             => true,
        'no_blank_lines_before_namespace'             => true,
        'single_blank_line_before_namespace'          => false,
        'trim_array_spaces'                           => false,
        'binary_operator_spaces'                      => ['align_double_arrow' => true],
        'phpdoc_order'                                => true,
        'array_syntax'                                => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
