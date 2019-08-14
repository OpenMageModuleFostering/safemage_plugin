<?php
class SafeMage_Plugin_Model_Observer
{
    public function adminCacheRefreshType(Varien_Event_Observer $observer)
    {
        $type = $observer->getEvent()->getType();
        if ($type == 'config') {
            $this->_flushPluginCache();
        }
    }

    public function adminCacheFlushAll(Varien_Event_Observer $observer)
    {
        $this->_flushPluginCache();
    }

    public function adminCacheFlushSystem(Varien_Event_Observer $observer)
    {
        $this->_flushPluginCache();
    }

    protected function _flushPluginCache()
    {
        $io = new Varien_Io_File();
        if (!$io->rmdir(SafeMage_Plugin_File::getDir(), true)) {
            $this->_addWriteError();
        }
    }

    public function checkWritablePluginsDir($observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $currentSection = $request->getParam('section');
        if ($currentSection == 'system' && !Mage::helper('safemage_plugin')->checkWritablePluginsDir()) {
            $this->_addWriteError();
        }
    }

    private function _addWriteError()
    {
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('safemage_plugin')->__(
                'Folder does not have write permissions: %s',
                SafeMage_Plugin_File::getDir()
            )
        );
    }
}
