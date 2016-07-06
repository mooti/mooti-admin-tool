<?php
namespace Mooti\Platform\Command\Project;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Mooti\Framework\Framework;
use Mooti\Platform\Config\PlatformConfig;
use Mooti\Platform\Config\MootiConfig;
use Mooti\Framework\Util\FileSystem;
use Mooti\Framework\Util\Git;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Mooti\Framework\Exception\FileSystemException;

class UpdateAllRepositoriesCommand extends Command
{
    use Framework;

    protected function configure()
    {
        $this->setName('repository:update-all');
        $this->setDescription('Update all git repositories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $platformConfig = $this->createNew(PlatformConfig::class);
        $platformConfig->open();

        $fileSystem = $this->createNew(FileSystem::class);
        $curDir = $fileSystem->getCurrentWorkingDirectory();
        $repositoriesPath = $curDir.'/repositories';

        
        $git = $this->createNew(Git::class);

        $config = $platformConfig->getConfigData();

        $mootiConfig = $this->createNew(MootiConfig::class);

        $templateDir = __DIR__.'/../../../templates';
        $allowedServerTypes = ['php-standard'];

        foreach ($config['repositories'] as $repository) {
            $repositoryPath = $repositoriesPath.'/'.$repository['name'];
            if (!$fileSystem->fileExists($repositoryPath)) {
                $git->cloneRepo($repository['url'], $repositoryPath);
            }
            $fileSystem->changeDirectory($repositoryPath);
            $git->pull();

            $mootiConfig->setDirPath($repositoryPath);
            $mootiConfig->open();
            $mootiConfigArray = $mootiConfig->getConfigData();

            $scriptsToRun = [];
            if (isset($mootiConfigArray['scripts'])) {
                $scriptsToRun = $mootiConfigArray['scripts'];
            }

            foreach ($scriptsToRun as $script) {
                $command = 'cd '.$repositoryPath.' && '.$script;
                $this->runCommand($command, $output);
            }

            if (isset($mootiConfigArray['server'])) {
                $serverType = $mootiConfigArray['server']['type'];
                if (in_array($serverType, $allowedServerTypes, true) == false) {
                    throw new DataValidationException($serverType.' is not a valid server type');
                }

                $templatePath = $templateDir.'/apache/'.$serverType.'.tpl';
                $templateContents = $fileSystem->fileGetContents($templatePath);
                $serverName = $mootiConfigArray['name'].'.'.$config['config']['domain'];
                $webRoot = $mootiConfigArray['server']['web_root'];
                $repositoryWebRoot = $repository['name'] . (empty($webRoot) == false?'/'.$webRoot:'');
                $data = [
                    '{{server_name}}'         => $serverName,
                    '{{repository_web_root}}' => $repositoryWebRoot,
                    '{{index_file}}'          => $mootiConfigArray['server']['index_file']
                ];
                $apacheConfigContents = str_replace(array_keys($data), array_values($data), $templateContents);
                $apacheConfigPath = $curDir.'/apache/sites-available/'.$serverName.'.conf';
                
                try {
                    $oldApacheConfigContents = $fileSystem->fileGetContents($apacheConfigPath);
                } catch (FileSystemException $e) {
                    $oldApacheConfigContents = '';
                }                    

                if ($apacheConfigContents != $oldApacheConfigContents) {
                    $fileSystem->filePutContents($apacheConfigPath, $apacheConfigContents);

                    $apacheSitesAvailablePath = '/etc/apache2/sites-available/'.$serverName.'.conf';
                    if (!$fileSystem->fileExists($apacheSitesAvailablePath)) {
                        $command = 'sudo ln -s '.$apacheConfigPath.' '.$apacheSitesAvailablePath;
                        $this->runCommand($command, $output);
                        $command = 'sudo a2ensite '.$serverName;
                        $this->runCommand($command, $output);
                    }
                    
                    $output->writeln('Apache config has changed. Restarting...');
                    $command = ' sudo service apache2 restart';
                    $this->runCommand($command, $output);
                }
            }
        }

        $fileSystem->changeDirectory($curDir);

        $output->writeln('done');
    }

    public function runCommand($command, OutputInterface $output)
    {
        $output->writeln('Run: '.$command);
        $process = $this->createNew(Process::class, $command);
        $process->setTimeout(3600);
        $process->mustRun(function ($type, $buffer) use ($output) {
            $output->writeln(trim($buffer));
        });
    }
}
