<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* FIELD: MAD GRAPH */

class MadGraph extends NF_Abstracts_Input {
    protected $_name = 'MadGraph';
    protected $_type = 'madgraph';

    protected $_nicename = 'MadGraph';

    protected $_section = 'layout';

    protected $_icon = 'chart-bar';

    protected $_templates = 'madgraph';

	protected $_wrap_template = 'wrap-no-label';

    protected $_test_value = '';


  protected $_settings_all_fields = array(
       'default','classes','barsimple_color','barsimple_bgcolor','barsimple_textcolor'
    );


    public function __construct() {
        parent::__construct();

        $this->_nicename = __( 'MAD :: Progress', 'nf-mad' );

	    $this->_settings[ 'default' ][ 'group' ] = 'primary';
        $this->_settings[ 'default' ][ 'width' ] = 'full';
		$this->_settings[ 'default' ][ 'type' ] = 'select';
		$this->_settings[ 'default' ][ 'options' ] = array(
                  array( 'label' => __( 'Only percentage', 'nf-mad' ), 'value' => 'onlypercentage' ),
                  array( 'label' => __( 'Bar - simple', 'nf-mad' ), 'value' => 'barsimple' ),
				          array( 'label' => __( 'Bar - simple animated', 'nf-mad' ), 'value' => 'barsimpleanimated' ),
                  array( 'label' => __( 'Bar - 3d', 'nf-mad' ), 'value' => 'bar3d' ),
                  array( 'label' => __( 'Bar - 3d animated', 'nf-mad' ), 'value' => 'bar3danimated' ),
         );
    $this->_settings[ 'default' ][ 'use_merge_tags' ] = FALSE;
		$this->_settings[ 'default' ][ 'label' ] = __( 'Style of progress', 'nf-mad' );
		$this->_settings[ 'default' ][ 'value' ] = 'onlypercentage';


	    $this->_settings[ 'barsimple_color' ][ 'group' ] = 'primary';
		$this->_settings[ 'barsimple_color' ][ 'name' ] = 'color';
        $this->_settings[ 'barsimple_color' ][ 'width' ] = 'one-half';
		$this->_settings[ 'barsimple_color' ][ 'type' ] = 'color';
		$this->_settings[ 'barsimple_color' ][ 'value' ] = '';
        $this->_settings[ 'barsimple_color' ][ 'use_merge_tags' ] = FALSE;
		$this->_settings[ 'barsimple_color' ][ 'label' ] = __( 'Color of bar', 'nf-mad' );

	    $this->_settings[ 'barsimple_bgcolor' ][ 'group' ] = 'primary';
		$this->_settings[ 'barsimple_bgcolor' ][ 'name' ] = 'bgcolor';
        $this->_settings[ 'barsimple_bgcolor' ][ 'width' ] = 'one-half';
		$this->_settings[ 'barsimple_bgcolor' ][ 'type' ] = 'color';
		$this->_settings[ 'barsimple_bgcolor' ][ 'value' ] = '';
        $this->_settings[ 'barsimple_bgcolor' ][ 'use_merge_tags' ] = FALSE;
		$this->_settings[ 'barsimple_bgcolor' ][ 'label' ] = __( 'Backgroundcolor bar', 'nf-mad' );

	    $this->_settings[ 'barsimple_textcolor' ][ 'group' ] = 'primary';
		$this->_settings[ 'barsimple_textcolor' ][ 'name' ] = 'txtcolor';
        $this->_settings[ 'barsimple_textcolor' ][ 'width' ] = 'one-half';
		$this->_settings[ 'barsimple_textcolor' ][ 'type' ] = 'color';
		$this->_settings[ 'barsimple_textcolor' ][ 'value' ] = '';
        $this->_settings[ 'barsimple_textcolor' ][ 'use_merge_tags' ] = FALSE;
		$this->_settings[ 'barsimple_textcolor' ][ 'label' ] = __( 'Textcolor', 'nf-mad' );


    }


}
?>
