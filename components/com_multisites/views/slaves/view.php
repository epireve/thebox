<?php
/**
 * @file       view.php
 * @brief      Front-end view that allow to create dynamic slave sites.
 * @version    1.2.0 RC5
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
 * - V1.1.8 26-DEC-2008: Add the ItemId into the delete form in aim to correctly display the "delete" button in the list.
 * - V1.2.0 21-JUL-2009: Fix bug when process slave site creation that report an error.
                         On error, call the appropriate onDeploy_Err() plugin function instead of onDeploy_OK().
                         This avoid for example to redirect the user to a check-out when its websiste quota is exceeded.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'slaves_helper.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'helper.php');
require_once( JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');

jimport('joomla.filesystem.path');
require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/libraries/joomla/application/component/view2win.php'));


// ===========================================================
//            MultisitesViewSlaves class
// ===========================================================
class MultisitesViewSlaves extends JView2Win
{
   // Private members
   var $_formName   = 'Slave';
   var $_controllerName = 'slave';

   //------------ display ---------------
   /**
    * @brief Display the list of sites
    */
	function display($tpl=null)
	{
		global $mainframe;

		// If the user is not login
		$user		=& JFactory::getUser();
		if ( $user->get('guest')) {
		   echo JText::_( 'YOU MUST LOGIN FIRST');
		   return;
		}

		$this->_layout = 'list';

		// Get parameters defined in the menu
		$params	      = &$mainframe->getParams();
		$title         = $params->get('title');
		$show_del_btn  = $params->get('show_del_btn');
		$eshop_events  = $params->get('eshop_events');


		// Set toolbar items for the page
		$formName   = $this->_formName;
		$controllerName = $this->_controllerName;
		if ( empty( $title)) {
		   $title = JText::_( "SLAVE_LIST_TITLE" );
		}
		JToolBarHelper::title( $title, 'config.png' );
		if ( $show_del_btn) {
   		JToolBarHelper::customX( "delete$formName", 'delete.png', 'delete_f2.png', JText::_( 'SLAVE_LIST_BTN_DELETE'), true );
		}
		JToolBarHelper::editListX( "edit$formName");
		JToolBarHelper::addNew( "add$formName" );
//		JToolBarHelper::help( 'screen.' .$controllerName. 'manager', true);

      $this->renderToolBar();
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('SLAVE_LIST_TITLE'));

		// retreive the filters and parameters that limit the query
		$filters = &$this->_getFilters();
		$this->assignRef('filters', $filters);
		
		// Create the filters
		$model = &$this->getModel();
		$model->setFilters( $filters);
		
		// Check if this is a super administrator or an administrator
		$user = JFactory::getUser();
	   $isSuperAdmin = false;
      if ($user->authorize( 'com_multisites', 'edit')) {
   	   $isSuperAdmin = true;
      }
		$this->assign('isSuperAdmin',   $isSuperAdmin);

		
		// Retreive the transaction records
		$rows = &$this->get('UserSlaveSites');
		$this->assignRef('rows', $rows);

		
		// ---- Get Payment Script
		$payment_code = '';
		$onDeploy_OK_code = '';
		$onDeploy_Err_code = '';
		if ( $eshop_events) {
   		// ---- Get Payment Script
   		$payment_script   = $params->get('payment_script');
   		if ( !empty( $payment_script)) {
   		   $payment_code = base64_encode( $payment_script);
   		}

   		// ---- Get onDeploy_OK script
   		$script           = $params->get('onDeploy_OK');
   		if ( !empty( $script)) {
   		   $onDeploy_OK_code = base64_encode( $script);
   		}
   		// ---- Get onDeploy_ERR script
   		$script           = $params->get('onDeploy_ERR');
   		if ( !empty( $script)) {
   		   $onDeploy_Err_code = base64_encode( $script);
   		}
		}
		$this->assignRef('eshop_events',       $eshop_events);
		$this->assignRef('payment_code',       $payment_code);
		$this->assignRef('onDeploy_OK_code',   $onDeploy_OK_code);
		$this->assignRef('onDeploy_Err_code',  $onDeploy_Err_code);

	   $Itemid = JRequest::getInt('Itemid');
		$this->assign('Itemid',   $Itemid);

		$lists		= &$this->_getViewLists( $filters, true, true);
		$pagination	= &$this->_getPagination( $filters, $this->get('CountAll'));


		// Assign view variable with will be used by the template
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('lists',        $lists);
		$this->assignRef('limitstart',   $limitstart);

		JHTML::_('behavior.tooltip');

		// Display the template
		parent::display($tpl);
	}


   //------------ _getFilters ---------------
   /**
    * @brief Return all the filter values posted by the "display" form (the list) and also store the values into the registry for later use.
    * The filter values are used by the model to filter, sort and limit the records that must be displayed in the list.
    */
	function &_getFilters()
	{
		global $mainframe, $option;
	   $filters = array();

		// $option				   = JRequest::getCmd( 'option' );
		$client                 = JRequest::getWord( 'filter_contracts', 'contracts' );
		// Retreive search filter
		$search				      = $mainframe->getUserStateFromRequest( "$option.$client.search",			   'search',			   '',			'string' );
		$filters['search']	   = JString::strtolower( $search );

		// Retreive filter combo values
		$filters['owner_id']    = $mainframe->getUserStateFromRequest( "$option.$client.filter_owner_id",  'filter_owner_id',	'[unselected]','string' );
		$filters['status']	   = $mainframe->getUserStateFromRequest( "$option.$client.filter_status",    'filter_status',	   '[unselected]',			'string' );

		// Load the menu object and parameters
		$filter_groupName       = JRequest::getString('filter_groupName', null);
		if ( !empty( $filter_groupName)) {
		   $filters['groupName'] = $filter_groupName;
	   }
	   else {
   		$params	               = &$mainframe->getParams();
   		$filters['groupName']   = $params->get('groupname');
	   }

		// Retreive selected sort column and direction
		$filters['order']		   = $mainframe->getUserStateFromRequest( "$option.$client.filter_order",		'filter_order',		'',	      'cmd' );
		$filters['order_Dir']	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		// Retreive the limit for display
		$filters['limit']		   = $mainframe->getUserStateFromRequest( 'global.list.limit',                'limit',             $mainframe->getCfg('list_limit'), 'int' );
		$filters['limitstart']	= $mainframe->getUserStateFromRequest( $option.'.limitstart',              'limitstart',        0, 'int' );

		return $filters;
	}

   //------------ _getViewLists ---------------
	function &_getViewLists( &$filters, $facultative_status=false, $onChangeStatus=false)
	{
	   $lists = array();
	   
		// Filter combo
		$lists['owner_id']   = MultisitesHelperSlaves::getUsersList( $this->rows, $filters['owner_id']);
		$lists['status']	   = MultisitesHelper::getAllStatusList(  'filter_status', $filters['status'], $facultative_status, $onChangeStatus);

		// table ordering
		$lists['order_Dir']	= $filters['order_Dir'];
		$lists['order']		= $filters['order'];

		// search filter
		$lists['search']     = $filters['search'];

		return $lists;
	}


   //------------ _getFormViewLists ---------------
	function &_getFormViewLists( &$filters)
	{
	   $lists = array();
	   
		// Filter combo
	   $model      =& $this->getModel( 'Templates');
	   $templates  =& $model->getTemplates();
	   $groupName = null;
	   if ( isset( $filters['groupName'])) {
	      $groupName = $filters['groupName'];
	   }
	   
		$lists['templates']	= MultisitesHelper::getTemplatesList(  $templates, $this->row->fromTemplateID, true, $groupName);

		// table ordering
		$lists['order_Dir']	= $filters['order_Dir'];
		$lists['order']		= $filters['order'];

		// search filter
		$lists['search']     = $filters['search'];

		return $lists;
	}

   //------------ deleteForm ---------------
   /**
    * @brief Request the user to confirm the deletion.
    * This display the current site information that ask for a confirmation.
    */
	function deleteForm($tpl=null)
	{
		global $mainframe;

		// If the user is not login
		$user		=& JFactory::getUser();
		if ( $user->get('guest')) {
		   return JText::_( 'YOU MUST LOGIN FIRST');
		}

		$this->_layout = 'delete';

		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title(  JText::_( 'SLAVE_DELETE_TITLE' ), 'config.png' );
		JToolBarHelper::custom( 'doDeleteSlave', 'delete.png', 'delete_f2.png', JText::_( 'SLAVE_DELETE_BTN_DELETE'), false );
		JToolBarHelper::cancel();
		// JToolBarHelper::help( 'screen.slave.delete', true );

      $this->renderToolBar();

		// view data
		$site       = &$this->get('CurrentRecord');

		$document = & JFactory::getDocument();
		$document->setTitle('Confirm Delete website: ' . $site->sitename );

	   $Itemid = JRequest::getInt('Itemid');
		$this->assign('Itemid',   $Itemid);
		
		// Assign value to the view
		$this->assignAds();
		$this->assignRef('site', $site);

		parent::display($tpl);
	}

   //------------ editForm ---------------
   /**
    * @brief Add or Edit a site
    * @param edit True  means edit the current record.
    *             False means add a new record.
    */
	function editForm($edit,$isReadOnly=false,$tpl=null)
	{
		global $mainframe;
		
		// If the user is not login
		$user		=& JFactory::getUser();
		if ( $user->get('guest')) {
		   return JText::_( 'YOU MUST LOGIN FIRST');
		}

		$this->_layout = 'edit';
		
		if($edit) {
			$table = &$this->get('CurrentRecord');
			// If the user match or this is the Super Administrator or Administrator
			if ( ($table->owner_id == $user->id)
			  || $user->authorize( 'com_multisites', 'edit')) {
			   // Accept to edit/show this record
			}
			else {
   		   return JText::_( 'SLAVE_EDIT_ERR_NOACCESS');
			}
		}
		else {
			$table = &$this->get('NewRecord');
		}
		$this->assignRef('row', $table);
		
		/*
		 * Set toolbar items for the page
		 */
		$formName   = $this->_formName;

		$isNew = ($table->id == '');
		if ( $isNew) {
		   $text = JText::_('SLAVE_EDIT_TITLE_NEW');
		}
		else {
		   if ( $isReadOnly) {
   		   $text = '<font color="red">' . JText::_('SLAVE_EDIT_TITLE_SHOW') . '</font>';
		   }
		   else {
   		   $text = JText::_('SLAVE_EDIT_TITLE_EDIT');
		   }
		}
		JToolBarHelper::title( JText::_( "SLAVE_EDIT_TITLE" ).': <small><small>[ '. $text.' ]</small></small>', 'config.png' );
		// If NOT read only => Edit or new
		if ( !$isReadOnly) {
		   // Give the possibility to save the record
	  	   JToolBarHelper::custom( 'saveSlave', 'save.png', 'save_f2.png', JText::_( 'SLAVE_EDIT_BTN_SAVE'), false );
		   if ( !empty( $this->row->expiration)) {
   	  	   JToolBarHelper::custom( 'paySlave', 'apply.png', 'apply_f2.png', JText::_( 'SLAVE_EDIT_BTN_BUY'), false );
		   }
   		JToolBarHelper::cancel();
		}
		else {
   		JToolBarHelper::back();
		}
		// JToolBarHelper::help( 'screen.' .$controllerName. '.new' );
		
		$filters = &$this->_getFilters();
		$lists	= &$this->_getFormViewLists( $filters);

		$template = new Jms2WinTemplate();
		$template->load( $table->fromTemplateID);
		$this->assignRef('template',  $template);


		// If a Payment Script was not defined from the "list" webpage
		$eshop_events = JRequest::getBool('eshop_events', null);
		if ( empty($eshop_events)) {
		   // Check if there is a eShop_events flag is present in the "edit" webpage
   		$params	      = &$mainframe->getParams();
   		$eshop_events  = $params->get('eshop_events');
		}
		$this->assignRef('eshop_events',    $eshop_events);

		// If a Payment Script was not defined from the "list" webpage
		$payment_code = JRequest::getString('payment_code');
		if ( empty($payment_code)) {
		   // Check if there is a payment script attached to the "edit" webpage
   		$params	         = &$mainframe->getParams();
   		$payment_script   = $params->get('payment_script');
   		$payment_code = '';
   		if ( !empty( $payment_script)) {
   		   $payment_code = base64_encode( $payment_script);
   		}
		}
		$this->assignRef('payment_code',    $payment_code);

		// If a Payment Script was not defined from the "list" webpage
		$onDeploy_OK_code = JRequest::getString('onDeploy_OK_code');
		if ( empty($onDeploy_OK_code)) {
		   // Check if there is a payment script attached to the "edit" webpage
   		$params	         = &$mainframe->getParams();
   		$script           = $params->get('onDeploy_OK');
   		$onDeploy_OK_code = '';
   		if ( !empty( $script)) {
   		   $onDeploy_OK_code = base64_encode( $script);
   		}
		}
		$this->assignRef('onDeploy_OK_code', $onDeploy_OK_code);

		// If a Payment Script was not defined from the "list" webpage
		$onDeploy_Err_code = JRequest::getString('onDeploy_Err_code');
		if ( empty($onDeploy_Err_code)) {
		   // Check if there is a payment script attached to the "edit" webpage
   		$params	            = &$mainframe->getParams();
   		$script              = $params->get('onDeploy_Err');
   		$onDeploy_Err_code   = '';
   		if ( !empty( $script)) {
   		   $onDeploy_Err_code = base64_encode( $script);
   		}
		}
		$this->assignRef('onDeploy_Err_code', $onDeploy_Err_code);

	   $Itemid = JRequest::getInt('Itemid');
		$this->assign('Itemid',   $Itemid);
		
      $this->renderToolBar();
		$this->assignAds();
		$this->assignRef('lists',           $lists);
		$this->assign('isnew',              $isNew);
		$this->assign('isReadOnly',         $isReadOnly);

		JHTML::_('behavior.tooltip');

		parent::display($tpl);
		return null;
	}
   //------------ showForm ---------------
   /**
    * @brief show the form detail
    */
	function showForm($tpl=null)
	{
	   return $this->editForm( true, true, $tpl);
	   
	}

   //------------ _getPaymentRef ---------------
   /**
    * @brief Eval the payment_script code when present.
    */
	function _getPaymentRef( $enteredvalues, $template, $renew=false)
	{
	   global $mainframe;

		// If Billable Website
		$eshop_events = JRequest::getBool('eshop_events');
		if ( !$eshop_events) {
		   return null;
		}

		// Call Plugin MultiSites and Trigger GetPaymentReference()
		$d = array();
		$d['Itemid']         = $this->Itemid;
		$model               = $this->getModel();
		$d['site_id']        = $model->getSiteID( $enteredvalues);
		$d['sku']            = $template->sku;
		$d['validity']       = $template->validity;
		$d['validityUnit']   = $template->validity_unit;

      $payment_ref = null;
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('getPaymentReference', array ( & $d, & $enteredvalues, &$model, & $template, $renew));
      if ( !empty( $results)) {
         // Retreive the last result that must contain the payment reference (the result of the last plugin).
	      $payment_ref = $results[count( $results)-1];
      }

		$payment_code = JRequest::getString('payment_code');
		if ( empty($payment_code)) {
		   // Return the Plugin Payment Reference (If a plugin was present)
		   return $payment_ref;
		}
		
		$payment_script = base64_decode( $payment_code);
		if ( empty( $payment_script)) {
		   // Return the Plugin Payment Reference (If a plugin was present)
		   return $payment_ref;
		}
		

		// Convert array into local variables
		foreach( $d as $key => $value) {
		   $$key = $value;
		}
		
		// ========================
		// From here, the payment_ref can be modified by the PHP code present in the menu
		
		// If it contain a PHP code
		if ( strpos( $payment_script,'<?php') !== false) {
         $result = eval('?>' . $payment_script . '<?php ');
         if( $result === false ) {
            $msg = 'ERROR when processing the payment script.';
      		$mainframe->enqueueMessage($msg, 'notice');
      	   return $msg;
         }
         // If there is a return called inside the "eval" function
         if ( !empty( $result)) {
            $payment_ref = $result;
         }
         // Check if the variable payment_ref is present
   	   if ( !empty( $payment_ref)) {
   	      return $payment_ref;
   	   }
         // If we arrive here, this means that :
         // - the script does not contain a return 
         // - or not assign the payement_ref variable
         // - or a plugin that not filled the payment_ref
         $msg = JText::_( 'SLAVE_SAVE_PAYMENT_REF_EMPTY');
   		$mainframe->enqueueMessage($msg, 'notice');
   	   return $msg;
	   }
	   
	   // If the script does not contain a '<?php', this mean this is a constant
	   return $payment_script;
	}

   //------------ _onDeploy_Err ---------------
	function _onDeploy_Err( $enteredvalues)
	{
	   global $mainframe;

		// If Billable Website
		$eshop_events = JRequest::getBool('eshop_events');
		if ( !$eshop_events) {
		   return null;
		}

		$Itemid     = JRequest::getString( 'Itemid');
		$script_b64 = JRequest::getString('onDeploy_Err_code');
		if ( !empty( $script_b64)) {
   		$script = base64_decode( $script_b64);
   		if ( !empty( $script)) {
      		// If it contain a PHP code
      		if ( strpos( $script,'<?php') !== false) {
               $result = eval('?>' . $script . '<?php ');
               if( $result === false ) {
                  $msg = 'ERROR when processing On Deploy Error script.';
            		$mainframe->enqueueMessage($msg, 'notice');
               }
               // If there is a return called inside the "eval" function
               else if ( !empty( $result)) {
                  $msg = 'Unexpected result returned by the On Deploy Error script.';
            		$mainframe->enqueueMessage($msg, 'notice');
               }
      	   }
   		}
		}
		
		$d = array();
		$d['Itemid']         = $Itemid;
		$model               = $this->getModel();
		$d['site_id']        = $model->getSiteID( $enteredvalues);
		
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onDeploy_Err', array ( & $d, & $enteredvalues, &$model));
      if ( !empty( $results)) {
         // Retreive the last result that must contain the payment reference (the result of the last plugin).
	      $payment_ref = $results[count( $results)-1];
      }
	}

   //------------ _onDeploy_OK ---------------
	function _onDeploy_OK( $enteredvalues)
	{
	   global $mainframe;

		// If Billable Website
		$eshop_events = JRequest::getBool('eshop_events');
		if ( !$eshop_events) {
		   return null;
		}
		
		$Itemid     = JRequest::getString( 'Itemid');
		$script_b64 = JRequest::getString('onDeploy_OK_code');
		if ( !empty( $script_b64)) {
   		$script = base64_decode( $script_b64);
   		if ( !empty( $script)) {
      		// If it contain a PHP code
      		if ( strpos( $script,'<?php') !== false) {
               $result = eval('?>' . $script . '<?php ');
               if( $result === false ) {
                  $msg = 'ERROR when processing On Deploy OK script.';
            		$mainframe->enqueueMessage($msg, 'notice');
               }
               // If there is a return called inside the "eval" function
               else if ( !empty( $result)) {
                  $msg = 'Unexpected result returned by the On Deploy OK script.';
            		$mainframe->enqueueMessage($msg, 'notice');
               }
      		}
   	   }
		}

		$d = array();
		$d['Itemid']         = $Itemid;
		$model               = $this->getModel();
		$d['site_id']        = $model->getSiteID( $enteredvalues);

		
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onDeploy_OK', array ( & $d, & $enteredvalues, &$model));
      if ( !empty( $results)) {
         // Retreive the last result that must contain the payment reference (the result of the last plugin).
	      $payment_ref = $results[count( $results)-1];
      }
	}

   //------------ saveSlave ---------------
   /**
    * @brief This create a directory with the site id and deploy the multisites files.
    */
	function saveSlave($tpl=null)
	{
		global $mainframe, $option;
		
		$Itemid  = JRequest::getInt('Itemid');
		$this->assign('Itemid', $Itemid);

		$enteredvalues = array();
		$enteredvalues['id']             = JRequest::getCmd('site_id', null);
		$enteredvalues['fromTemplateID'] = JRequest::getString('fromTemplateID', null);
		$enteredvalues['site_prefix']    = JRequest::getCmd('site_prefix', null);
		$enteredvalues['site_alias']     = JRequest::getCmd('site_alias', null);
		$enteredvalues['toSiteName']     = JRequest::getString('toSiteName', null);
		$enteredvalues['newAdminEmail']  = JRequest::getString('newAdminEmail', null);
		$enteredvalues['newAdminPsw']    = JRequest::getString('newAdminPsw', null);
		$enteredvalues['siteComment']    = isset( $_REQUEST[ 'siteComment']) ? stripslashes( $_REQUEST[ 'siteComment']) : '';
		$user =& JFactory::getUser();
		$enteredvalues['owner_id']       = $user->id;
		
		$template = new Jms2WinTemplate();
		$template->load( $enteredvalues['fromTemplateID']);
		
		// When there is no payment reference, assume that the service is free
		$payment_ref = $this->_getPaymentRef( $enteredvalues, $template);
		if ( empty( $payment_ref)) {
   		$enteredvalues['status']      = 'Confirmed';
		}
		// Wait for payment
		else {
   		$enteredvalues['status']      = 'Pending';
   		$enteredvalues['payment_ref'] = $payment_ref;
		}

		$enteredvalues['isnew']          = (JRequest::getInt('isnew', 0)==1) ? true : false;

	   // Prepare processing
	   // If windows, the symbolic links does not exist
      jimport( 'joomla.utilities.utility.php');
      if ( JUtility::isWinOS()) {
	      // This means that 'host' variable contain in the http header will be used to select the appropriate configuration
   	   $site_dir  = JPATH_ROOT;   // Use the Master site directory
	   }
	   else {
	      // For Unix, directly use the place where the wrappers are deployed
   	   // $site_dir  = &$this->get('SiteDir');
   	   $site_dir  = JPATH_ROOT;   // Use the Master site directory
	   }

	   // deploy the site using the site id
	   $model = $this->getModel();
		if ( !$model->deploySite( $enteredvalues, true)) {
		   $msg = $model->getError();
			$this->_onDeploy_Err( $enteredvalues);
			// JError::raiseWarning( 500, $msg);
			return $msg;
		}
		// Re-create the master index containing all the host name and associated directories
		$model->createMasterIndex();
		
		// Assign the values
		$this->assignRef('id',           $enteredvalues['id']);
		$this->assignRef('site_dir',     $site_dir);
		$this->assignRef('domains',      $enteredvalues['domains']);
		$this->assignRef('site_prefix',  $enteredvalues['site_prefix']);
		$this->assignRef('site_alias',   $enteredvalues['site_alias']);
		$this->assign('isnew',           $enteredvalues['isnew']);

		$msgid = ($this->isnew) ? 'SITE_DEPLOYED' : 'SITE_UPDATED';
		$domainStr = implode(",", $this->domains);
		$msg = JText::sprintf( $msgid, $this->site_prefix, $this->site_alias);
		$mainframe->enqueueMessage($msg, 'message');

		$this->_onDeploy_OK( $enteredvalues);

		return '';
	}	


   //------------ paySlave ---------------
   /**
    * @brief Buy or Renew payment for an existing website
    */
	function paySlave($tpl=null)
	{
		global $mainframe, $option;
		
		$Itemid  = JRequest::getInt('Itemid');
		$this->assign('Itemid', $Itemid);

		$site_id = JRequest::getString('site_id', null);
		if ( empty( $site_id)) {
		   return JText::_( 'Unable to buy! The site identifier is missing.');
		}
		$site = new Site();
		$enteredvalues = $site->loadArray( $site_id);
		if ( empty( $enteredvalues)) {
		   return JText::_( 'The site detail information can not be retreived');
		}

		$template = new Jms2WinTemplate();
		$template->load( $enteredvalues['fromTemplateID']);
		
		// When there is no payment reference, assume that the service is free
		$payment_ref = $this->_getPaymentRef( $enteredvalues, $template, true);
		if ( empty( $payment_ref)) {
   		return JText::_( 'The computed payment reference is missing');
		}
		// Wait for payment
		else {
   		$enteredvalues['payment_ref'] = $payment_ref;
		}

		$this->_onDeploy_OK( $enteredvalues);

		// Should never arrive here because Deploy OK should redirect somewhere
		return JText::_( 'Buy procedure completed');
	}	


   //------------ getLinkToolTipsTitle ---------------
	function getLinkToolTipsTitle( $row)
	{
	   $title = JText::_( 'Edit the website' )
	          . '::'
	          ;
      if ( !empty( $row->site_prefix))    $title .= '<b>' . JText::_( 'Prefix') . ' : </b>'  . $row->site_prefix . "<br/>\n";
      if ( !empty( $row->site_alias))     $title .= '<b>' . JText::_( 'Alias')  . ' : </b>'   . $row->site_alias . "<br/>\n";
      if ( !empty( $row->status))         $title .= '<b>' . JText::_( 'Status') . ' : </b>'  . JText::_( $row->status) . "<br/>\n";
      if ( !empty( $row->payment_ref))    $title .= '<b>' . JText::_( 'Payment Reference') . ' : </b>' . $row->payment_ref . "<br/>\n";
	   if ( !empty( $row->siteComment))    $title .= $row->siteComment . "<br/>\n";

	   return $title;
	}
	
   //------------ getTemplateTitle ---------------
	function getTemplateTitle( $template_id)
	{
		static $templates;
		if ( empty( $templates)) {
   	   $model      =& $this->getModel( 'Templates');
   	   $templates   =& $model->getTemplates();
   	   if ( !empty( $templates[$template_id]['title'])) {
   	      return $templates[$template_id]['title'];
   	   }
		}
		
		return $template_id;
	}

} // End class
