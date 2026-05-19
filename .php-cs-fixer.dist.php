<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('runtime')
    ->exclude('web/assets');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'braces_position' => [
            'allow_single_line_anonymous_functions' => false,
            'allow_single_line_empty_anonymous_classes' => true,
            'anonymous_classes_opening_brace' => 'same_line',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'same_line',
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'same_line',
        ],
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => true,
        'single_quote' => true,
        'no_extra_blank_lines' => true,
        'blank_line_before_statement' => ['statements' => ['return']],
    ])
    ->setFinder($finder);
