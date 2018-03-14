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
    private $_hostsFilePath   = '';
    private $_hosts           = array();

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

        if (file_exists($this->_hostsFilePath))
        {
            if ($hostsContent = file_get_contents($this->_hostsFilePath))
            {
                if($hosts = @json_decode($hostsContent))
                {
                    if ($hosts)
                    {
                        $this->_hosts = $hosts;
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
        if($this->_hosts)
        {
            return $this->_hosts;
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
        $address     = trim($address);
        $description = trim($description);

        if($this->_isValidHost($address))
        {
            if($this->_isUniqueHost($address))
            {
                if ($this->_addHostData($address, $description))
                {
                    if ($this->_saveData())
                    {
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
        if ($this->_deleteKeyData($key))
        {
            if ($this->_saveData())
            {
                return TRUE;
            }
        }

        return FALSE;
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
            unset($this->_hosts[$key]);

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