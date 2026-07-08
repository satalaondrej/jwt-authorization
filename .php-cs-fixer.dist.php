<?php
declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
	->in([
		'src',
		'tests',
	])
	->append([
		'.php-cs-fixer.dist.php',
		'bin/generate-jwt',
		'bin/generate-key',
	])
;

return (new PhpCsFixer\Config())
	->setCacheFile(__DIR__.'/cache/.php-cs-fixer.cache')
	->setIndent("\t")
	->setLineEnding("\n")
	->setRules([
		'@Symfony' => true,
		'indentation_type' => true,
		'blank_line_after_opening_tag' => false, // disable to not add new line before declare(strict_types=1);
		'yoda_style' => false, // disable changing order when comparing stuff
	])
	->setFinder($finder);
