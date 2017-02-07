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

        $sshConfigure = new Configuration('192.168.1.110');

        if (!is_file($sshConfigure->getIdentity()) || !is_file($sshConfigure->getIdentity() . '.pub')) {
            $output->writeln("Error: Cannot public key file .");
            exit(3);
        }

        $sshAuthentication =
            new PublicKeyFile($username, $sshConfigure->getIdentity() . '.pub', $sshConfigure->getIdentity());


        $sshSession = new Session($sshConfigure, $sshAuthentication);

        $output->write($sshSession->getExec()->run('/usr/bin/env'));
    }

}