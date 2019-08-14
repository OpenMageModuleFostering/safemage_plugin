<?php
class SafeMage_Plugin_Block_Adminhtml_System_Plugin_List extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();
        $html = '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
        $html .= '<td class="value" colspan="3">';
        $html .= $this->_getElementHtml($element);
        $html.= '</td>';

        return $this->_decorateRowHtml($element, $html);
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $source = '';
        switch ($element->getId()) {
            case 'system_plugin_block_plugins':
                $source = 'blocks';
                break;
            case 'system_plugin_helper_plugins':
                $source = 'helpers';
                break;
            case 'system_plugin_model_plugins':
                $source = 'models';
                break;
        }

        if (!$source) {
            return '';
        }

        $pluginsList = Mage::helper('safemage_plugin')->getPluginsList($source);

        if ($pluginsList) {
            $html = '';
            foreach($pluginsList as $class => $methods) {

                foreach($methods as $method => $types) {
                    $html .= '<div style="padding-bottom: 6px">';
                    $html .= '<strong>' . $class . '::' . $method . '</strong>';
                    $html .= '<table width="365" bordercolor="#D6D6D6" border="1" cellpadding="1" cellspacing="1"'.
                        ' style="border-collapse: collapse">';
                    $html .= '<tr>';
                    $html .= '<td align="center" width="43"><small>' . $this->__('Type') . '</small></td>';
                    $html .= '<td align="center" width="27"><small>' . $this->__('Sort') . '</small></td>';
                    $html .= '<td align="center"><small>' . $this->__('Name / Run') . '</small></td>';
                    $html .= '</tr>';
                    foreach($types as $type => $plugins) {
                        $html .= '<tr>';
                        $html .= '<td align="center" rowspan="' . count($plugins) .
                            '" style="vertical-align: middle; font-size: 13px;">' . $type . '</td>';
                        foreach($plugins as $index => $data) {
                            if ($index > 0) {
                                $html .= '<tr>';
                            }
                            $html .= '<td align="center" style="vertical-align: middle">' .
                                $data['sort_order'] . '</td>';
                            $html .= '<td class="nowrap" style="padding: 0 2px">' .
                                $data['name'] . '<br>' . $data['run'] . '</td>';

                            if ($index > 0) {
                                $html .= '</tr>';
                            }
                        }
                        $html .= '</tr>';
                    }

                    $html .= '</table>';
                    $html .= '</div>';
                }
            }
        } else {
            $html = $this->__('None');
        }

        return '<div id="' . $element->getHtmlId() . '">'. $html . '</div>';
    }

    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}
