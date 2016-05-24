<?php
namespace Mooti\Platform\Command\Project;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Mooti\Framework\Framework;
use Mooti\Platform\Config\PlatformConfig;
use Mooti\Framework\Util\FileSystem;
use Symfony\Component\Console\Input\InputArgument;

class AddRepositoryCommand extends Command
{
    use Framework;

    protected function configure()
    {
        $this->setName('repository:add');
        $this->setDescription('Add a git repository');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The repository name'
        );
        $this->addArgument(
            'url',
            InputArgument::REQUIRED,
            'The repository url'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $platformConfig = $this->createNew(PlatformConfig::class);
        $platformConfig->open();

        $platformConfig->addRepository($input->getArgument('name'), $input->getArgument('url'));
        $platformConfig->save();

        $output->writeln('done');
    }
}
