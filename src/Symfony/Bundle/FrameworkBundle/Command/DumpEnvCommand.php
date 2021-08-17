<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * A console command to compiles the contents of the .env* files into a PHP-optimized file called .env.local.php.
 *
 * @internal
 */
class DumpEnvCommand extends Command
{
    protected static $defaultName = 'env:dump';
    protected static $defaultDescription = 'Compiles .env files to .env.local.php';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Compiles .env files to .env.local.php.')
            ->setDefinition([
                new InputArgument('env', InputArgument::OPTIONAL, 'The application environment to dump .env files for - e.g. "prod".'),
            ])
            ->addOption('empty', null, InputOption::VALUE_NONE, 'Ignore the content of .env files')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command compiles the contents of the .env* files into a PHP-optimized file called .env.local.php.

    <info>%command.full_name%</info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Application $application */
        $application = $this->getApplication();
        $kernel = $application->getKernel();

        if ($env = $input->getArgument('env')) {
            $_SERVER['APP_ENV'] = $env;
        }

        $path = $kernel->getProjectDir().'/.env';

        if (!$env || !$input->getOption('empty')) {
            $vars = $this->loadEnv($path, $env);
            $env = $vars['APP_ENV'];
        }

        if ($input->getOption('empty')) {
            $vars = ['APP_ENV' => $env];
        }

        $vars = var_export($vars, true);
        $vars = <<<EOF
<?php

// This file was generated by running "php bin/console env:dump $env"

return $vars;

EOF;
        file_put_contents($path.'.local.php', $vars, \LOCK_EX);

        $output->writeln('Successfully dumped .env files in <info>.env.local.php</>');

        return 0;
    }

    private function loadEnv(string $path, ?string $env): array
    {
        $globalsBackup = [$_SERVER, $_ENV];
        unset($_SERVER['APP_ENV']);
        $_ENV = ['APP_ENV' => $env];
        $_SERVER['SYMFONY_DOTENV_VARS'] = implode(',', array_keys($_SERVER));
        putenv('SYMFONY_DOTENV_VARS='.$_SERVER['SYMFONY_DOTENV_VARS']);

        try {
            $dotenv = new Dotenv();

            if (!$env && file_exists($p = "$path.local")) {
                $env = $_ENV['APP_ENV'] = $dotenv->parse(file_get_contents($p), $p)['APP_ENV'] ?? null;
            }

            if (!$env) {
                throw new \RuntimeException('Please provide the name of the environment either by passing it as command line argument or by defining the "APP_ENV" variable in the ".env.local" file.');
            }

            $dotenv->loadEnv($path);
            $env = $_ENV;
        } finally {
            [$_SERVER, $_ENV] = $globalsBackup;
        }

        return $env;
    }
}
