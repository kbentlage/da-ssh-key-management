<?php
/**
 * Created by PhpStorm.
 * User: Kevin Bentlage
 * Date: 12/10/2016
 * Time: 21:13
 */

namespace DirectAdmin\SshKeyManagement\Controllers;

class SshKeyController
{
    private $_authorizedKeysPath = '';
    private $_sshKeys            = array();

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
        $this->_authorizedKeysPath = '/home/' . getenv('USERNAME') . '/.ssh/authorized_keys';

        if (file_exists($this->_authorizedKeysPath))
        {
            if ($authorizedKeysContent = file_get_contents($this->_authorizedKeysPath))
            {
                $authorizedKeysContent = trim($authorizedKeysContent);
                $keys                  = explode(PHP_EOL, $authorizedKeysContent);

                if ($keys)
                {
                    $i = 1;
                    foreach ($keys as $key)
                    {
                        $fingerprint = $this->_getKeyFingerprint($key);
                        $description = $this->_getKeyDescription($key);

                        $this->_sshKeys[$i] = array(
                            'key'         => $key,
                            'fingerprint' => $fingerprint,
                            'description' => $description,
                        );

                        $i++;
                    }
                }
            }
        }
    }

    /**
     * Get Keys
     *
     * @return array|null
     */
    public function getKeys()
    {
        if ($this->_sshKeys)
        {
            return $this->_sshKeys;
        }

        return NULL;
    }

    /**
     * Add Key
     *
     * @param $key
     *
     * @return bool
     */
    public function addKey($key)
    {
        $key = trim($key);

        if($this->_isValidKey($key))
        {
            if($this->_isUniqueKey($key))
            {
                if ($this->_addKeyData($key))
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
     * Delete Key
     *
     * @param $key
     *
     * @return bool
     */
    public function deleteKey($key)
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
     * Add Key Data
     *
     * @param $key
     *
     * @return bool
     */
    private function _addKeyData($key)
    {
        $this->_sshKeys[] = array(
            'key'         => $key,
            'fingerprint' => '',
            'description' => '',
        );

        return TRUE;
    }

    /**
     * Delete Key Data
     *
     * @param $key
     *
     * @return bool
     */
    private function _deleteKeyData($key)
    {
        if (isset($this->_sshKeys[$key]))
        {
            unset($this->_sshKeys[$key]);

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
        $authorizedKeysContent = '';

        if($this->_sshKeys)
        {
            foreach ($this->_sshKeys as $key)
            {
                $authorizedKeysContent .= $key['key'] . PHP_EOL;
            }

            // determine pathinfo
            $pathInfo = pathinfo($this->_authorizedKeysPath);

            // check if .ssh directory exists
            if (!is_dir($pathInfo['dirname']))
            {
                mkdir($pathInfo['dirname'], 0700);
            }

            // save json to file
            if (file_put_contents($this->_authorizedKeysPath, $authorizedKeysContent))
            {
                // fix permissions
                if (chmod($this->_authorizedKeysPath, 0600))
                {
                    return TRUE;
                }
            }
        }
        // no keys
        else
        {
            // no keys, but file exists. Remove it.
            if(file_exists($this->_authorizedKeysPath))
            {
                unlink($this->_authorizedKeysPath);

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Is Valid Key
     *
     * @param $value
     *
     * @return bool
     */
    private function _isValidKey($value)
    {
        $key_parts = explode(' ', $value, 3);
        if (count($key_parts) < 2)
        {
            return FALSE;
        }
        if (count($key_parts) > 3)
        {
            return FALSE;
        }
        $algorithm = $key_parts[0];
        $key       = $key_parts[1];
        if (!in_array($algorithm, array('ssh-rsa', 'ssh-dss')))
        {
            return FALSE;
        }
        $key_base64_decoded = base64_decode($key, TRUE);
        if ($key_base64_decoded == FALSE)
        {
            return FALSE;
        }
        $check = base64_decode(substr($key, 0, 16));
        $check = preg_replace("/[^\w\-]/", "", $check);
        if ((string)$check !== (string)$algorithm)
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Is Unique Key
     *
     * @param $key
     *
     * @return bool
     */
    public function _isUniqueKey($key)
    {
        if($this->_sshKeys)
        {
            foreach($this->_sshKeys as $sshKey)
            {
                if($key == $sshKey['key'])
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Get Key Fingerprint
     *
     * @param $key
     *
     * @return string
     */
    public function _getKeyFingerprint($key)
    {
        $key   = trim($key);
        $parts = explode(' ', $key, 3);

        // calculate fingerprint
        $fingerprint = join(':', str_split(md5(base64_decode($parts[1])), 2));

        return $fingerprint;
    }

    /**
     * Get Key Description
     *
     * @param $key
     *
     * @return string
     */
    public function _getKeyDescription($key)
    {
        $key   = trim($key);
        $parts = explode(' ', $key, 3);

        if (isset($parts[2]) && $parts[2])
        {
            return trim($parts[2]);
        }

        return '';
    }
}
