<?php
// flexible CS rules
$finder = PhpCsFixer\Finder::create()
    ->files()
    ->in('src')
    ->in('tests')
    ->name('*.php')
;

return PhpCsFixer\Config::create()
        ->setRules([
            '@PSR1' => true,
            '@PSR2' => true,
            'blank_line_before_return' => true,
            'no_empty_comment' => true,
            'no_useless_return' => true,
            'cast_spaces' => true,
            'single_quote' => true,
            'ordered_imports' => true,
            'no_unused_imports' => true,
            'concat_space' => ['spacing' => 'one'],
            'array_syntax' => ['syntax' => 'short'],
        ])
        ->setFinder($finder)
    ;
