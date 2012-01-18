<?php
/**
 * 
 * Login
 * 
 * A simple login module to implement a chain of command login system.
 * 
 * PHP version 5
 * 
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; either version 2 of the License, or 
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the 
 * Free Software Foundation, Inc., 
 * 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 * 
 * @category  Chisimba
 * @package   helloforms
 * @author    Derek Keats derek@dkeats.com
 * @copyright 2007 AVOIR
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt The GNU General Public License
 * @version   CVS: $Id: controller.php,v 1.4 2007-11-25 09:13:27 dkeats Exp $
 * @link      http://avoir.uwc.ac.za
 */
 
// security check - must be included in all scripts
if (!
/**
 * The $GLOBALS is an array used to control access to certain constants.
 * Here it is used to check if the file is opening in engine, if not it
 * stops the file from running.
 * 
 * @global entry point $GLOBALS['kewl_entry_point_run']
 * @name   $kewl_entry_point_run
 *         
 */
$GLOBALS['kewl_entry_point_run'])
{
        die("You cannot view this page directly");
}
// end security check

/**
* 
* Controller class for Chisimba for the module login
*
* @author Derek Keats
* @package login
*
*/
class login extends controller
{
    
    /**
    * 
    * @var string $objConfig String object property for holding the 
    * configuration object
    * @access public;
    * 
    */
    public $objConfig;
    
    /**
    * 
    * @var string $objLanguage String object property for holding the 
    * language object
    * @access public
    * 
    */
    public $objLanguage;
    /**
    *
    * @var string $objLog String object property for holding the 
    * logger object for logging user activity
    * @access public
    * 
    */
    public $objLog;

    /**
    * 
    * Intialiser for the login controller
    * @access public
    * 
    */
    public function init()
    {
        $this->loginSecurity = $this->getObject('loginsecurity', 'login');

        // Create an instance of the nonce object for checking retries
        $this->objNonce = $this->getObject('nonce', 'login');



        $this->objUser = $this->getObject('user', 'security');
        $this->objLanguage = $this->getObject('language', 'language');
        // Create the configuration object
        $this->objConfig = $this->getObject('config', 'config');
        //Get the activity logger class
        $this->objLog=$this->newObject('logactivity', 'logger');
        //Log this module call
        $this->objLog->log();
    }
    
    
    /**
     * 
     * The standard dispatch method for the login module.
     * The dispatch method uses methods determined from the action 
     * parameter of the  querystring and executes the appropriate method, 
     * returning its appropriate template. This template contains the code 
     * which renders the module output.
     * 
     */
    public function dispatch()
    {
        //Get action from query string and set default to view
        $action=$this->getParam('action', 'view');
        /*
        * Convert the action into a method (alternative to 
        * using case selections)
        */
        $method = $this->__getMethod($action);
        // Set the layout template to compatible one
        $this->setLayoutTemplate('layout_template.php');
        /*
        * Return the template determined by the method resulting 
        * from action
        */
        return $this->$method();
    }
    
    
    /*------------- BEGIN: Set of methods to replace case selection ------------*/

    /**
    * 
    * Method corresponding to the view action. It shows the default
    * dynamic canvas template, showing you how to create block based
    * view templates
    * @access private
    * 
    */
    private function __view()
    {
        // All the action is in the blocks
        return "demo_tpl.php";
    }
    
    /**
    * 
    * Method corresponding to the ajaxlogin action. This is the business
    * of the login. We are not yet using chain of command.
     * 
    * @access private
    * 
    */
    private function __ajaxlogin()
    {
        $nonce = $this->loginSecurity->getVariable('nonce', FALSE);
        if ($nonce) {
            // Check the nonce to see if it exists
            $tries = $this->objNonce->getTries($nonce);
            if ($tries) {
                if ($tries <= 3) {
                    $username = $this->loginSecurity->getUsername();
                    $password = $this->loginSecurity->getPassword();
                    $remember = $this->loginSecurity->getVariable('remember', "off");
                    if ($this->objUser->authenticateUser($username, $password, $remember)) {
                        $this->objNonce->deleteNonce($nonce);
                        echo 'yes';
                    } else {
                        echo "no";
                    }
                } else {
                    // Disable the nonce
                    $this->objNonce->disableNonce($nonce);
                    echo 'loginsdisabled';
                }
            } else {
                echo 'nononceindb';
            }
        } else {
            echo 'noncemissing';
        }
        exit(0);
    }

    /**
    * 
    * Generate a captcha
    * @access public
    * @return VOID
    * 
    */
    public function __generatecaptcha()
    {
        $captcha = $this->newObject('captcha', 'login');
        echo $captcha->show();
        die();
    }

    /**
    *
    * verify a captcha result
     * 
    * @access public
    * @return VOID
    *
    */
    public function __verifycaptcha()
    {
        $captcha = $this->getObject('captcha', 'login');
        echo $captcha->verifyCaptcha();
        die();
    }

    /**
     *
     * Send the login box to an ajax request
     *
     * @access public
     * @return VOID
     *
     */
    public function __loginboxajax()
    {
        $loginBox = $this->getObject('showloginbox', 'login');
        echo $loginBox->renderLoginBox();
        die();
    }

    public function __getcapajax()
    {
        $loginBox = $this->getObject('showloginbox', 'login');
        echo $loginBox->renderProveHuman();
        die();
    }
 
    
    
    /**
    * 
    * Method to return an error when the action is not a valid 
    * action method
    * 
    * @access private
    * @return string The dump template populated with the error message
    * 
    */
    private function __actionError()
    {
        $this->setVar('str', "<h3>"
          . $this->objLanguage->languageText("phrase_unrecognizedaction")
          .": " . $action . "</h3>");
        return 'dump_tpl.php';
    }
    
    /**
    * 
    * Method to check if a given action is a valid method
    * of this class preceded by double underscore (__). If it __action 
    * is not a valid method it returns FALSE, if it is a valid method
    * of this class it returns TRUE.
    * 
    * @access private
    * @param string $action The action parameter passed byref
    * @return boolean TRUE|FALSE
    * 
    */
    function __validAction(& $action)
    {
        if (method_exists($this, "__".$action)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
    * 
    * Method to convert the action parameter into the name of 
    * a method of this class.
    * 
    * @access private
    * @param string $action The action parameter passed byref
    * @return stromg the name of the method
    * 
    */
    function __getMethod(& $action)
    {
        if ($this->__validAction($action)) {
            return "__" . $action;
        } else {
            return "__actionError";
        }
    }
    
    /*------------- END: Set of methods to replace case selection ------------*/
    


    /**
    *
    * This is a method to determine if the user has to 
    * be logged in or not. Note that this is an example, 
    * and if you use it view will be visible to non-logged in 
    * users. Delete it if you do not want to allow annonymous access.
    * It overides that in the parent class
    *
    * @return boolean TRUE|FALSE
    *
    */
    public function requiresLogin()
    {
        $action=$this->getParam('action', NULL);
        switch ($action)
        {
            case 'view':
            case 'ajaxlogin':
            case 'generatecaptcha':
            case 'ajaxgetloginblock':
            case 'verifycaptcha':
            case 'loginboxajax':
            case 'getcapajax':
            case NULL;
                return FALSE;
                break;
            default:
                return TRUE;
                break;
        }
     }
}
?>