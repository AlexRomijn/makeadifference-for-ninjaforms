<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class NF_Action_ConfirmMail
 */
class NF_Action_MakeADifference extends NF_Abstracts_Action {
   /**
     * @var string
     */
    protected $_name  = 'nfmakeadifference';

    /**
     * @var array
     */
    protected $_tags = array();

    /**
     * @var string
     */
    protected $_timing = 'normal';

    /**
     * @var int
     */
    protected $_priority = '20';

    /**
     * Constructor
     */
    public function __construct()    {
        parent::__construct();

		// show the name of the action
        $this->_nicename = __( 'MAD :: Petition', 'ninja-forms' );

		// import settings configuration
        $settings = require_once(realpath(plugin_dir_path(__FILE__))."/"."ActionConfirmmailSettings.php");
        $this->_settings = array_merge( $this->_settings, $settings );

    }

    /*
    * PUBLIC METHODS
    */

  	/**
     * Function to process the action, and send confirmation mail
     *
     * @param $action_settings, $form_id, $data
     * @return $data
     */
    public function process( $action_settings, $form_id, $data )    {


        return $data;
    }

}

?>
