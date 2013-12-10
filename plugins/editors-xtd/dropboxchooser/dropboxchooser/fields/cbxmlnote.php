<?php
/*------------------------------------------------------------------------
 * cbxmlnote.php - 
 * ------------------------------------------------------------------------
 * @package	Joomla
 * @subpackage	Content
 * @copyright	Copyright (C)2010-2013 codeboxr.com. All rights reserved.
 * @license	GNU/GPL, http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Support Forum  http://codeboxr.com/product/dropbox-chooser-for-joomla-editor
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 */
class JFormFieldCbxmlnote extends JFormField
{
    /**
     * Color picker form field type compatible with Joomla 1.6. Displays an Adobe type color picker panel, and returns a six-digit hex value, eg #cc99ff
     */
    protected $type = 'Cbxmlnote';

    /**
     */
    protected function getInput()
    {        
        
        if(strpos($this->value, '||')){
            $val = explode('||',$this->value);
            $color = $val[0];
            $value = $val[1];
            
        } else {
            
            $color = NULL;
            $value = $this->value;
            
        }
        
        //var_dump($this);
        
        $link =  $this->element['link'];
           
        //var_dump($link);
        
        switch ($color) {
            case 'blue': 
                $color = '#000066';
                break;

            case 'green': 
                $color = '#00E000';
                break;

            case 'red': 
                $color = '#E00000';
                break;

            default:
                $color = '#000000';
        }
        
        if($link != '')  $value = '<a target="_blank" href='.$link.'>'.$value.'</a>';

        return '<span style="float: left;margin: 5px 5px 5px 0;width: auto;color:'.$color.'; font-weight:bold;"><strong>Note: </strong>'. $value .'</span>';
    }
}
