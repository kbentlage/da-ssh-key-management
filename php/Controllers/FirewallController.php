<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14/03/2018
 * Time: 21:02
 */

namespace DirectAdmin\SshKeyManagement\Controllers;


class FirewallController
{
    private $_hostsFilePath = '';
    private $_hosts = array();

    /**
     * Construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        $this->_hostsFilePath = '/home/' . getenv('USERNAME') . '/.ssh-hosts.json';

        if (file_exists($this->_hostsFilePath)) {
            if ($hostsContent = file_get_contents($this->_hostsFilePath)) {
                if ($hosts = @json_decode($hostsContent, TRUE)) {
                    if ($hosts) {
                        $i = 1;
                        foreach ($hosts as $host) {
                            $this->_hosts[$i] = $host;
                            $i++;
                        }
                    }
                }
            }
        }
    }

    /**
     * Get Access Hosts
     *
     * @return array
     */
    public function getHosts()
    {
        if ($this->_hosts)
        {
            $hosts = array();

            foreach($this->_hosts as $host)
            {
                if(!$host['deleted'])
                {
                    $hosts[] = $host;
                }
            }

            return $hosts;
        }

        return NULL;
    }

    /**
     * Add Host
     *
     * @param $address
     * @param $description
     * @return bool
     */
    public function addHost($address, $description)
    {
        $address = trim($address);
        $description = trim($description);

        if ($this->_isValidHost($address)) {
            if ($this->_isUniqueHost($address)) {
                if ($this->_addHostData($address, $description)) {
                    if ($this->_saveData()) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * Delete Host
     *
     * @param $key
     *
     * @return bool
     */
    public function deleteHost($key)
    {
        if ($this->_deleteHostData($key)) {
            if ($this->_saveData()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Run Cronjob
     *
     * @return void
     */
    public function runCronjob()
    {
        $users = scandir('/home');

        foreach($users as $user)
        {
            if(is_dir('/home/'.$user))
            {
                if(file_exists('/home/'.$user.'/.ssh-hosts.json'))
                {
                    $hostsJson = file_get_contents('/home/'.$user.'/.ssh-hosts.json');

                    if($hosts = @json_decode($hostsJson, TRUE))
                    {
                        $updated = FALSE;

                        foreach($hosts as $key => $host)
                        {
                            if(!$host['processed'])
                            {
                                // delete IP
                                if($host['deleted'])
                                {
                                    $deleteRule = 'tcp|in|d=22|s='.$host['address'].' # da-ssh-key-management - '.$user;

                                    shell_exec('sed -i "/'.$deleteRule.'/d" /etc/csf/csf.allow');
                                    shell_exec('csf -r');

                                    unse($hosts[$key]);

                                    $updated = TRUE;
                                }
                                // add IP
                                else
                                {
                                    $allowRule = 'tcp|in|d=22|s='.$host['address'].' # da-ssh-key-management - '.$user.' - '.$host['description'].' - '.date('d-m-Y H:i').PHP_EOL;

                                    if(file_put_contents('/etc/csf/csf.allow', $allowRule, FILE_APPEND))
                                    {
                                        shell_exec('csf -r');

                                        $hosts[$key]['processed'] = TRUE;

                                        $updated = TRUE;
                                    }
                                }
                            }
                        }

                        if($updated)
                        {
                            $hostsJson = json_encode($hosts);

                            file_put_contents('/home/'.$user.'/.ssh-hosts.json', $hostsJson);
                        }
                    }
                }
            }
        }
    }

    /**
     * Add Host Data
     *
     * @param $address
     * @param $description
     *
     * @return bool
     */
    private function _addHostData($address, $description)
    {
        $this->_hosts[] = array(
            'address'     => $address,
            'description' => $description,
            'processed'   => FALSE,
            'deleted'     => FALSE,
        );

        return TRUE;
    }

    /**
     * Delete Host Data
     *
     * @param $key
     *
     * @return bool
     */
    private function _deleteHostData($key)
    {
        if (isset($this->_hosts[$key]))
        {
            $this->_hosts[$key]['deleted'] = TRUE;
            $this->_hosts[$key]['processed'] = FALSE;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Save
     *
     * @return bool
     */
    private function _saveData()
    {
        if($this->_hosts)
        {
            $hostsJson = json_encode($this->_hosts);

            // save json to file
            if (file_put_contents($this->_hostsFilePath, $hostsJson))
            {
                return TRUE;
            }
        }
        // no hosts
        else
        {
            // no keys, but file exists. Remove it.
            if(file_exists($this->_hostsFilePath))
            {
                unlink($this->_hostsFilePath);

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Is Valid Host
     *
     * @param $address
     *
     * @return bool
     */
    private function _isValidHost($address)
    {
        if(filter_var($address, FILTER_VALIDATE_IP))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Is Unique Host
     *
     * @param $address
     *
     * @return bool
     */
    public function _isUniqueHost($address)
    {
        if($this->_hosts)
        {
            foreach($this->_hosts as $host)
            {
                if($host['address'] == $address)
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }
}