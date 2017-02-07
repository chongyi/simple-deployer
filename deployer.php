<?php
/**
 * deployer.php
 *
 * Creator:    chongyi
 * Created at: 2017/02/07 09:23
 */

require 'vendor/autoload.php';

$app = new \Symfony\Component\Console\Application();
$app->add(new \SimpleDeployer\Commands\DeployCommand());
$app->run();