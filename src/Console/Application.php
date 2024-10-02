<?php

declare(strict_types=1);

namespace totaldev\SchemaGenerator\Console;

use Symfony\Component\Console\Application as SfApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use totaldev\SchemaGenerator\CodeGenerator;
use totaldev\SchemaGenerator\SchemaParser;

/**
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Application extends Command
{
    private bool $running = false;
    private string $version = '1.0';

    public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
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
        $this->addArgument(
            'target',
            InputArgument::OPTIONAL,
            'Target directory for generate classes.',
            __DIR__ . '/../../schema/src'
        );
        $this->addArgument(
            'namespace',
            InputArgument::OPTIONAL,
            'Namespaces for classes',
            'Totaldev\TgSchema'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parser = new SchemaParser($output, $input->getArgument('tl_file'));
        $classes = $parser->parse();

        $generator = new CodeGenerator($input->getArgument('namespace'), $input->getArgument('target'));
        $generator->generate($classes);

        $config = dirname(__DIR__, 2) . '/bin/.php-cs-fixer.primary.php';
        exec("./vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --config=$config", $output);
        echo implode(PHP_EOL, $output) . PHP_EOL;

        $config = dirname(__DIR__, 2) . '/bin/.php-cs-fixer.postprocess.php';
        exec("./vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --config=$config", $output);
        echo implode(PHP_EOL, $output) . PHP_EOL;

        return 0;
    }
}
