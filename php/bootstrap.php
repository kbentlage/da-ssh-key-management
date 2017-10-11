<?php
/**
 * Created by PhpStorm.
 * User: Kevin Bentlage
 * Date: 12/10/2016
 * Time: 21:13
 */

global $_POST, $_GET;

parse_str(getenv('QUERY_STRING'), $_GET);
parse_str(getenv('POST'), $_POST);

require_once dirname(__DIR__) . '/php/Controllers/SshKeyController.php';
?>
