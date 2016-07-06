<?php
namespace Mooti\Platform\Command\Project;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Mooti\Framework\Framework;
use Mooti\Platform\Config\PlatformConfig;
use Mooti\Framework\Util\FileSystem;
use Mooti\Framework\Exception\FileSystemException;

class InitProjectCommand extends Command
{
    use Framework;

    protected function configure()
    {
        $this->setName('project:init');
        $this->setDescription('Initilaise the project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Warning! This might wipe any existing project files in this directory. Would you like to continue? (yes/no) ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $fileSystem = $this->createNew(FileSystem::class);
        $curDir = $fileSystem->getCurrentWorkingDirectory();

        $versionFile = $curDir.'/platform/version.txt';
        $platformVersion = trim($fileSystem->fileGetContents($versionFile));

        $platformConfig = $this->createNew(PlatformConfig::class);
        $platformConfig->init();
        $platformConfig->setPlatformVersion($platformVersion);
        $platformConfig->save();
        
        $fileSystem->createDirectory($curDir.'/repositories');
        $fileSystem->createDirectory($curDir.'/apache');
        $fileSystem->createDirectory($curDir.'/apache/sites-available');

        $ignoreFiles = '/repositories/'.PHP_EOL;
        $ignoreFiles .= '/apache/'.PHP_EOL;
        $ignoreFiles .= '/platform/'.PHP_EOL;

        $fileSystem->filePutContents($curDir.'/.gitignore', $ignoreFiles);

        $output->writeln('done');
    }
}
