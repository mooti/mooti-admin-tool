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

class RemoveRepositoryCommand extends Command
{
    use Framework;

    protected function configure()
    {
        $this->setName('repository:remove');
        $this->setDescription('Remove a git repository');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'The repository name'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $platformConfig = $this->createNew(PlatformConfig::class);
        $platformConfig->open();

        $platformConfig->removeRepository($input->getArgument('name'));
        $platformConfig->save();

        $output->writeln('done');
    }
}
