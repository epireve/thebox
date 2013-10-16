<?php
/**
 * @file       controller.php
 * @brief      Front-end that allow to create dynamic slave sites.
 * @version    1.2.7
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.1.0 11-OCT-2008: File creation
 * - V1.1.3 02-DEC-2008: Replace getString by getCmd when reading the site ID to avoid special characters and the spaces.
 *                       Some customer are using spaces in the name of a site id.
 * - V1.1.5 13-DEC-2008: Rebuid the master index when a slave site is deleted
 * - V1.1.8 26-DEC-2008: Add the ItemId in the redirection URL to allow correctly display the "delete" button.
 *                       This ItemId is used to retreive the context and therefore return the correct getParams() values.
 *                       When not present, this return the website default values and not the menu type specific values.
 *                       Also add a cancel function to redirect on the list with appropriate ItemId value.
 * - V1.2.7 29-SEP-2009: Add a redirection URL processing when the action is performed.
 *                       Also give the possibility to directly call the "Add" slave directly without the "list".
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

// ===========================================================
//             MultisitesController class
// ===========================================================
class MultisitesController extends JController
{
   //------------ display ---------------
	/**
	 * @brief Display the list of websites created by the user
	 */
	function display()
	{
	   $layout = JRequest::getString('layout', '');
	   if ( $layout == 'edit') {
	      $this->addSlave();
	   }
	   else {
   		$model	=& $this->getModel( 'Slaves');
   		$view    =& $this->getView( 'Slaves');
   		$view->setModel( $model, true );
   
   		// Add a second model that is used to compute the lists
   		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
   		$modelTemplates	=& $this->getModel( 'Templates' );
   		$view->setModel( $modelTemplates);
   
   		
   		$view->display();
	   }
	   
	}

   //------------ cancel ---------------
   /**
    * @brief Cancel redirect to the list using the "ItemId" to display the correct buttons
    */
	function cancel()
	{
		global $mainframe;
		
		$params	         = &$mainframe->getParams();
		$redirect_onSave  = $params->get('redirect_onSave');
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave, $msg);
		}
		else {
   	   $Itemid = JRequest::getInt('Itemid');
   		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
   	}
	}

   //------------ addSlave ---------------
   /**
    * @brief Add a new slave site instances.
    * This operation will create a sub-directory in 'multisites' directory.
    * The name of the sub-directory is the site ID.
    */
	function addSlave()
	{
		global $mainframe;
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);

		$msg = $view->editForm(false,null);
		if ( !empty( $msg)) {
   		$params	         = &$mainframe->getParams();
   		$redirect_onSave  = $params->get('redirect_onSave');
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      	   $Itemid = JRequest::getInt('Itemid');
      		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
      	}
		}
	}

	
   //------------ editSlave ---------------
   /**
    * @brief Edit a specific site instances.
    */
	function editSlave()
	{
		global $mainframe;
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);


		$msg = $view->editForm( true);
		if ( !empty( $msg)) {
   		$params	         = &$mainframe->getParams();
   		$redirect_onSave  = $params->get('redirect_onSave');
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      	   $Itemid = JRequest::getInt('Itemid');
      		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
      	}
		}
	}


   //------------ showDetail ---------------
   /**
    * @brief Edit a specific site instances.
    * This allow to update the list of domain attached to the site.
    */
	function showSlave()
	{
		global $mainframe;
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);


		$msg = $view->showForm();
		if ( !empty( $msg)) {
   		$params	         = &$mainframe->getParams();
   		$redirect_onSave  = $params->get('redirect_onSave');
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      	   $Itemid = JRequest::getInt('Itemid');
      		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
      	}
		}
	}

   //------------ saveSlave ---------------
   /**
    * @brief Save a slave site
    */
	function saveSlave()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$msg = $view->saveSlave();
		
		$params	         = &$mainframe->getParams();
		$redirect_onSave  = $params->get('redirect_onSave');
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave, $msg);
		}
		else {
   	   $Itemid = JRequest::getInt('Itemid');
   		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
   	}
	}

   //------------ paySlave ---------------
   /**
    * @brief Buy or Renew a website
    */
	function paySlave()
	{
		global $mainframe;
		
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$msg = $view->paySlave();

		$params	         = &$mainframe->getParams();
		$redirect_onSave  = $params->get('redirect_onSave');
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave, $msg);
		}
		else {
   	   $Itemid = JRequest::getInt('Itemid');
   		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $msg);
		}
	}

   //------------ deleteSlave ---------------
	/**
	 * Request confirmation before deletion of the site.
	 * When this is confirmed, this call doDeleteSite.
	 */
	function deleteSlave()
	{
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$view->deleteForm();
	}


   //------------ doDeleteSlave ---------------
	/**
	 * Perform the deletion of the site.
	 */
	function doDeleteSlave()
	{
	   global $mainframe, $option;
	   $Itemid = JRequest::getInt('Itemid');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$params	         = &$mainframe->getParams();
		$redirect_onSave  = $params->get('redirect_onSave');

		$id = JRequest::getVar( 'id', false, '', 'cmd' );
		if ($id === false) {
			JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave);
   		}
   		else {
   			$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid );
   		}
			return false;
		}

		$model =& $this->getModel( 'Slaves' );
		
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onBeforeDeleteSlave', array ( $id, &$model));
      
		if (!$model->canDelete()) {
			JError::raiseWarning( 500, $model->getError() );
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave);
   		}
   		else {
   			$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid );
   		}
			$rc = false;
		}
		else {
   		$err = null;
   		if (!$model->delete()) {
   			 $err = $model->getError();
   		}
   		
   		// Re-create the master index containing all the host name and associated directories
   		$model->createMasterIndex();
   		
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $err);
   		}
   		else {
      		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid, $err );
      	}
   		$rc = true;
   	}
   	
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onAfterDeleteSlave', array ( $id, &$model));
      
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave);
		}
		else {
   		$this->setRedirect( 'index.php?task=display&option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid );
   	}
   	return $rc;
	}

   // -------------- ajaxGetTemplateDescr ------------------------------
   // request : id = template identifier
   function ajaxGetTemplateDescr()
   {
		// Check for request forgeries
		JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		// Load the template based on its id
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model =& $this->getModel( 'Templates' );
		$template = $model->getCurrentRecord();
		if (!$template) {
   		jexit( '<error>' . JText::_( 'TEMPLATE_NOT_FOUND') . '</error>');
		}
		$result = 'templateInfo'
		        . '|' . $template->id
		        . '|' . $template->title
		        . '|' . $template->description
		        . '|' . $template->validity
		        . '|' . $template->validity_unit
		        ;
		jexit( $result);
   }

} // End Class
