<?php
class SafeMage_Plugin_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool|int
     */
    public function checkWritablePluginsDir()
    {
        $file = SafeMage_Plugin_File::getDir() . 'test.txt';
        $io = new Varien_Io_File();
        $result = $io->write($file, 'test');
        if ($result) {
            $result = $io->rm($file);
        }
        $io->close();
        return $result;
    }

    /**
     * @param $source string ('blocks', 'helpers', 'models')
     * @return array
     */
    public function getPluginsList($source)
    {
        if (!$source) {
            return array();
        }

        $config = Mage::getConfig()->getNode('global/' . $source);
        $pluginsList = array();

        foreach($config->children() as $module) {
            foreach($module->children() as $section) {
                if ($section->getName() == 'plugin') {
                    foreach($section->children() as $class) {
                        foreach ($class as $plugin) {
                            $data = $plugin->asArray();
                            if (isset($data['method']) && $data['method'] &&
                                isset($data['type']) &&
                                (
                                    $data['type'] == SafeMage_Plugin::TYPE_BEFORE ||
                                    $data['type'] == SafeMage_Plugin::TYPE_AROUND ||
                                    $data['type'] == SafeMage_Plugin::TYPE_AFTER
                                ) && isset($data['run']) && $data['run']
                                && (!isset($data['disabled']) || !$data['disabled'])
                            ) {
                                $sortOrder = 0;
                                if (isset($data['sort_order'])) {
                                    $sortOrder = intval($data['sort_order']);
                                }
                                $pluginsList[$module->getName() . '/' . $class->getName()][$data['method']][$data['type']][] =
                                    array(
                                        'name' => $plugin->getName(),
                                        'run' => $data['run'],
                                        'sort_order' => $sortOrder
                                    );
                            }
                        }
                    }
                }

            }
        }

        foreach($pluginsList as &$methods) {
            foreach($methods as &$types) {
                uasort($types, array($this, '_sortByType'));
                foreach($types as &$plugins) {
                    usort($plugins, array($this, '_sortByOrder'));
                }
            }
        }

        return $pluginsList;
    }

    protected function _sortByType($data1, $data2)
    {
        if (isset($data1[SafeMage_Plugin::TYPE_AROUND]) && isset($data2[SafeMage_Plugin::TYPE_BEFORE])) {
            return true;
        }

        if (isset($data1[SafeMage_Plugin::TYPE_AFTER])
            && (isset($data2[SafeMage_Plugin::TYPE_BEFORE]) || isset($data2[SafeMage_Plugin::TYPE_AROUND]))
        ) {
            return true;
        }

        return false;
    }

    protected function _sortByOrder($data1, $data2)
    {
        return ($data1['sort_order'] > $data2['sort_order'] ? true : false);
    }
}
