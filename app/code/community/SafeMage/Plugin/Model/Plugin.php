<?php
class SafeMage_Plugin_Model_Plugin
{
    /**
     * @param Mage_Core_Model_Layout $object
     * @param array $arguments
     * @return obj
     * @throws Mage_Core_Exception
     */
    public function aroundGetBlockInstance(obj $object, array $arguments)
    {
        list($block, $attributes) = $arguments;

        if (is_string($block)) {
            if (strpos($block, '/')!==false) {
                if (!$block = Mage::getConfig()->getBlockClassName($block)) {
                    Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
                }
            }
            if (class_exists($block) || mageFindClassFile($block)) {
                $block = new $block($attributes);
            }
        }
        if (!$block instanceof Mage_Core_Block_Abstract) {
            Mage::throwException(Mage::helper('core')->__('Invalid block type: %s', $block));
        }
        return $block;
    }
}
