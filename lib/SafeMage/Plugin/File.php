<?php
/*
NOTICE OF LICENSE

This source file is subject to the SafeMageEULA that is bundled with this package in the file LICENSE.txt.

It is also available at this URL: http://www.safemage.com/LICENSE_EULA.txt

Copyright (c)  SafeMage (http://www.safemage.com/)
*/

class SafeMage_Plugin_File
{
    const DIR = 'plugins';

    /**
     * @return string
     */
    public static function getDir()
    {
        return Mage::getBaseDir('cache') . DS . self::DIR . DS ;
    }

    /**
     * @param $pluginClassName string
     * @param $code string
     * @throws Exception
     */
    public function create($pluginClassName, $code)
    {
        if (!is_dir(self::getDir())) {
            if (!mkdir(self::getDir())) {
                throw new Exception('Failed to create directory: ' . self::getDir());
            }

            if (!chmod(self::getDir(), 0777)) {
                throw new Exception('Failed to chmod() directory: ' . self::getDir());
            }
        }

        $file = self::getDir() . $pluginClassName . '.php';

        if (!file_put_contents($file, $code)) {
            throw new Exception('Failed to save file: ' . $file);
        }

        if (!chmod($file, 0777)) {
            throw new Exception('Failed to chmod() file: ' . $file);
        }
    }

    /**
     * @param $pluginClassName string
     * @return bool
     */
    public function isExist($pluginClassName)
    {
        $file = self::getDir() . $pluginClassName . '.php';
        return file_exists($file);
    }
}
