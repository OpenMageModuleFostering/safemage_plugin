<?php
class SafeMage_Plugin_Block_Adminhtml_System_Plugin extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _getFooterHtml($element)
    {
        $tooltipsExist = false;
        $html = '</tbody></table>';
        $html .= '<div class="comment" align="right" style="font-size: 15px; width: 660px">' .
            'Visit ' .
            '<a href="http://www.safemage.com/function-plugins-for-magento-1.html" target="_blank">' .
                'Function Plugins'.
            '</a> for additional information.'.
            '<br>Powered by ' .
            '<a href="http://www.safemage.com/" target="_blank">' .
            '<img src="http://www.safemage.com/cache/plugin/logo.png" height="17" alt="SafeMage" title=" " />' .
            '</a>' .
            '</div>';
        $html .= '</fieldset>' . $this->_getExtraJs($element, $tooltipsExist);

        if ($element->getIsNested()) {
            $html .= '</div></td></tr>';
        } else {
            $html .= '</div>';
        }
        return $html;
    }
}
