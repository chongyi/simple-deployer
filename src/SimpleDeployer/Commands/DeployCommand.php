<?php
/**
 * DeployCommand.php
 *
 * Creator:         chongyi
 * Create Datetime: 2017/2/6 17:58
 */

namespace SimpleDeployer\Commands;


use Ssh\Authentication\PublicKeyFile;
use Ssh\Configuration;
use Ssh\Session;
use Ssh\SshConfigFileConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('sdep:deploy')->addArgument('configure', null, 'Select a configure file.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configure = $input->getArgument('configure');

        if (!is_file($configureFile = "config/$configure.php")) {
            $output->writeln("<red>Error: Cannot find configure file {$configureFile}</red>");
            exit(1);
        }

        require $configureFile;

        if (!isset($username)) {
            exit(2);
        }

        if (!isset($host)) {
            exit(2);
        }

        $sshConfigure = new Configuration($host);
        $sshConfigure->setIdentity(SshConfigFileConfiguration::DEFAULT_SSH_IDENTITY);

        $homePath = isset($_SERVER['HOMEPATH']) ? $_SERVER['HOMEPATH'] : $_SERVER['HOME'];

        if (!($identity = realpath(str_replace('~', $homePath, $sshConfigure->getIdentity())))) {
            $output->writeln("Error: Cannot get private key file \"$identity\".");
            exit(3);
        }

        if (!($identityPub = realpath(str_replace('~', $homePath, $sshConfigure->getIdentity() . '.pub')))) {
            $output->writeln("Error: Cannot get public key file \"$identityPub\".");
            exit(3);
        }

        $sshAuthentication
            = new PublicKeyFile($username, $sshConfigure->getIdentity() . '.pub', $sshConfigure->getIdentity());


        $sshSession = new Session($sshConfigure, $sshAuthentication);
        $exec       = $sshSession->getExec();

        $prefix = isset($env) ? "export PATH=\"$env" . ';$PATH" ; ' : '';

        $exec->run($prefix . "mkdir -p $path/$name");
        $exec->run($prefix . "git clone $repo $path/$name/repo");
    }

}