<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/includes',
        __DIR__ . '/admincp',
        __DIR__ . '/modules',
        __DIR__ . '/api',
    ])
    ->exclude(['vendor', 'cache', 'logs'])
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0'                           => true,
        '@PHP84Migration'                      => true,
        '@PHP84Migration:risky'                => true,

        // Array style
        'array_syntax'                         => ['syntax' => 'short'],
        'no_trailing_comma_in_singleline'      => true,
        'trailing_comma_in_multiline'          => ['elements' => ['arrays', 'parameters', 'arguments']],

        // Imports
        'ordered_imports'                      => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                    => true,
        'global_namespace_import'              => ['import_classes' => false],

        // Operators & spacing
        'binary_operator_spaces'               => ['default' => 'align_single_space_minimal'],
        'concat_space'                         => ['spacing' => 'one'],
        'not_operator_with_successor_space'    => true,
        'object_operator_without_whitespace'   => true,
        'standardize_not_equals'               => true,

        // PHP modernisation
        'random_api_migration'                 => true,   // rand/mt_rand → random_int
        'modernize_strpos'                     => true,   // strstr → strpos
        'no_alias_functions'                   => true,
        'native_function_casing'               => true,
        'cast_spaces'                          => ['space' => 'single'],

        // Control flow
        'no_useless_else'                      => true,
        'no_useless_return'                    => true,
        'return_assignment'                    => true,
        'simplified_if_return'                 => true,

        // Comments
        'single_line_comment_style'            => ['comment_types' => ['hash']],
        'no_empty_comment'                     => true,

        // Misc
        'declare_strict_types'                 => true,
        'strict_comparison'                    => true,
        'void_return'                          => true,
        'phpdoc_trim'                          => true,
        'phpdoc_no_empty_return'               => true,
    ])
    ->setFinder($finder);

