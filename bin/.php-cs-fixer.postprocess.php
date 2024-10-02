<?php

use PhpCsFixer\Runner\Parallel\ParallelConfig;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixer\PromotedConstructorPropertyFixer;
use PhpCsFixerCustomFixers\Fixers;

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/../schema']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(new ParallelConfig(8))
    ->setRiskyAllowed(true)
    ->registerCustomFixers(new Fixers())
    ->setRules([
        '@Symfony'                               => true,
        PromotedConstructorPropertyFixer::name() => ['promote_only_existing_properties' => true],
        MultilinePromotedPropertiesFixer::name() => ['minimum_number_of_parameters' => 2],
    ])
    ->setFinder($finder)
    ->setUsingCache(true);

