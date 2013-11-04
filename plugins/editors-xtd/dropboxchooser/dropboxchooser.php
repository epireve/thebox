<?php
/**
 * @version	Id: dropboxchooser.php $
 * @package	Joomla
 * @subpackage	Content
 * @copyright	Copyright (C)2010-2013 codeboxr.com. All rights reserved.
 * @license	GNU/GPL, http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @Support Forum  http://codeboxr.com/product/dropbox-chooser-for-joomla-editor
 */

//to create api key please go here https://www.dropbox.com/developers/apps/create

// no direct access
defined('_JEXEC') or die;

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/**
 * Editor Dropbox Chooser buton
 *
 * @package		Joomla.Plugin
 * @subpackage	Editors-xtd.dropboxchooser
 * @since 1.0
 */
class plgButtonDropboxChooser extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		//$this->loadLanguage();
                //var_dump($this->params);
	}

	/**
	 * Dropbox Chooser Button
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$app = JFactory::getApplication();

		$doc		= JFactory::getDocument();
		$template	= $app->getTemplate();
                
                $key            = $this->params->get('key', '');
                
                
                if($key !=  '' && !defined('dropboxchooser')){
                    define('dropboxchooser',1); //for zoo it will inject button only once
                    // button is not active in specific content components
                    $getContent = $this->_subject->getContent($name);		

                    //adding css and js files for dropbox chooser button
                    $doc->addStyleSheet( JURI::root(true). '/plugins/editors-xtd/dropboxchooser/dropboxchooser/dropboxchooser.css' );   
                    JHtml::_('behavior.framework');
                    $doc->addScript( JURI::root(true). '/plugins/editors-xtd/dropboxchooser/dropboxchooser/dropboxchooser.js' );                   
                    $doc->addCustomTag('<script type="text/javascript" src="https://www.dropbox.com/static/api/1/dropins.js" id="dropboxjs" data-app-key="'.$key.'"></script>');


                    $button = new JObject;        
                    $button->set('name', 'codeboxrdroboxchooser '.$name);        
                    $button->set('text', JText::_('Dropbox'));			
                    $button->set('link', '');
                    $button->set('rel', $name);
                    return $button;
                }
	}
}
