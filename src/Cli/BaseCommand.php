<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Cli;

use Mihaeu\PhpDependencies\Analyser\StaticAnalyser;
use Mihaeu\PhpDependencies\Analyser\Parser;
use Mihaeu\PhpDependencies\Dependencies\DependencyFilter;
use Mihaeu\PhpDependencies\Dependencies\DependencyMap;
use Mihaeu\PhpDependencies\OS\PhpFileFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCommand extends Command
{
    /** @var PhpFileFinder */
    protected $phpFileFinder;

    /** @var Parser */
    protected $parser;

    /** @var StaticAnalyser */
    protected $analyser;

    /** @var DependencyFilter */
    protected $dependencyFilter;

    /** @var string */
    protected $defaultFormat;

    /** @var string[] */
    protected $allowedFormats;

    /**
     * @param string $name
     * @param PhpFileFinder $phpFileFinder
     * @param Parser $parser
     * @param StaticAnalyser $analyser
     * @param DependencyFilter $dependencyFilter
     */
    public function __construct(
        string $name,
        PhpFileFinder $phpFileFinder,
        Parser $parser,
        StaticAnalyser $analyser,
        DependencyFilter $dependencyFilter
    ) {
        parent::__construct($name);

        $this->phpFileFinder = $phpFileFinder;
        $this->parser = $parser;
        $this->analyser = $analyser;
        $this->dependencyFilter = $dependencyFilter;
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'source',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Location of your PHP source files.'
            )
            ->addOption(
                'internals',
                null,
                InputOption::VALUE_NONE,
                'Check for dependencies from internal PHP Classes like SplFileInfo.'
            )
            ->addOption(
                'depth',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Output dependencies as packages instead of single classes.',
                0
            )
            ->addOption(
                'underscore-namespaces',
                'u',
                InputOption::VALUE_NONE,
                'Parse underscores in Class names as namespaces.'
            )
            ->addOption(
                'filter-namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Analyse only classes where both to and from are in this namespace.'
            )
            ->addOption(
                'filter-from',
                'f',
                InputOption::VALUE_REQUIRED,
                'Analyse only dependencies which originate from this namespace.'
            )
            ->addOption(
                'no-classes',
                null,
                InputOption::VALUE_NONE,
                'Remove all classes and analyse only namespaces.'
            )
            ->addOption(
                'exclude-regex',
                'e',
                InputOption::VALUE_REQUIRED,
                'Exclude all dependencies which match the (PREG) regular expression.'
            )
            ->addOption(
                'dynamic',
                null,
                InputOption::VALUE_REQUIRED,
                'Adds dependency information from dynamically analysed function traces, for more information check out https://dephpend.com'
            )
        ;
    }

    /**
     * @param string $destination
     *
     * @throws \Exception
     */
    protected function ensureOutputFormatIsValid(string $destination)
    {
        if (!in_array(preg_replace('/.+\.(\w+)$/', '$1', $destination), $this->allowedFormats, true)) {
            throw new \InvalidArgumentException('Output format is not allowed ('.implode(', ', $this->allowedFormats).')');
        }
    }

    /**
     * @param string[] $sources
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureSourcesAreReadable(array $sources)
    {
        foreach ($sources as $source) {
            if (!is_readable($source)) {
                throw new \InvalidArgumentException('File/Directory does not exist or is not readable.');
            }
        }
    }

    /**
     * @param string[] $sources
     *
     * @return DependencyMap
     *
     * @throws \LogicException
     */
    protected function detectDependencies(array $sources) : DependencyMap
    {
        return $this->analyser->analyse(
            $this->parser->parse(
                $this->phpFileFinder->getAllPhpFilesFromSources($sources)
            )
        );
    }

    /**
     * @param string $destination
     *
     * @throws \Exception
     */
    protected function ensureDestinationIsWritable(string $destination)
    {
        if (!is_writable(dirname($destination))) {
            throw new \InvalidArgumentException('Destination is not writable.');
        }
    }
}
