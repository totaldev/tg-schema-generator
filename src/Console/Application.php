<?php

declare(strict_types=1);

namespace PHPTdGram\SchemaGenerator\Console;

use PHPTdGram\SchemaGenerator\CodeGenerator;
use PHPTdGram\SchemaGenerator\SchemaParser;
use Symfony\Component\Console\Application as SfApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Application extends Command
{
    private string $version = '1.0';
    private bool   $running = false;

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        if ($this->running) {
            return parent::run($input, $output);
        }

        $application = new SfApplication($this->getName() ?: 'UNKNOWN', $this->version);

        $this->setName($_SERVER['argv'][0]);
        $application->add($this);
        $application->setDefaultCommand($this->getName(), true);

        $this->running = true;

        try {
            $ret = $application->run($input, $output);
        } finally {
            $this->running = false;
        }

        return $ret ?? 1;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'tl_file',
            InputArgument::OPTIONAL,
            'Telegram TL file to generate classes from'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser  = new SchemaParser($output, $input->getArgument('tl_file'));
        $classes = $parser->parse();

        $generator = new CodeGenerator('PHPTdGram\Schema', __DIR__ . '/../../schema/src');
        $generator->generate($classes);

        return 0;
    }
}
