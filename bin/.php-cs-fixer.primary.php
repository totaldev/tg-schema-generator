<?php

use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixers;

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/../schema']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(new ParallelConfig(8))
    ->setRiskyAllowed(true)
    ->registerCustomFixers(new Fixers())
    ->setRules([
        '@Symfony'                       => true,
        '@PhpCsFixer'                    => true,
        'array_syntax'                   => ['syntax' => 'short'],
        'concat_space'                   => ['spacing' => 'one'],
        'declare_strict_types'           => false,
        'increment_style'                => ['style' => 'post'],
        'nullable_type_declaration'      => true,
        'ordered_imports'                => true,
        'ordered_types'                  => true,
        'ordered_attributes'             => true,
        'ordered_class_elements'         => ['sort_algorithm' => 'alpha', 'case_sensitive' => true],
        'ordered_interfaces'             => true,
        'return_type_declaration'        => true,
        'single_line_empty_body'         => true,
        'void_return'                    => true,
        'yoda_style'                     => true,
        PhpdocSingleLineVarFixer::name() => true,
    ])
    ->setFinder($finder)
    ->setUsingCache(true);

