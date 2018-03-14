#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/03/2018
 * Time: 22:04
 */

require_once dirname(__DIR__) . '/bootstrap.php';

$firewallController = new \DirectAdmin\SshKeyManagement\Controllers\FirewallController();

$firewallController->runCronjob();