<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return array(
	// The prefix configuration. If a non null value will be used, a random prefix will be generated.
	'prefix'  => 'Lazysizes\\Vendor',

	// By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
	// directory. You can however define which files should be scoped by defining a collection of Finders in the
	// following configuration key.
	//
	// For more see: https://github.com/humbug/php-scoper#finders-and-paths
	'finders' => array(
		Finder::create()
			->files()
			->ignoreVCS( true )
			// ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
			->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock|\\.xml|\\.yml|\\.ini/' )
			->exclude(
				array(
					'test',
				)
			)
			->in( array( 'vendor/kornrunner', 'vendor/composer' ) ),
		Finder::create()->append(
			array(
				'composer.json',
			)
		),
	),
);
