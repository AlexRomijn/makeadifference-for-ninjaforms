<?php
/*
Plugin Name: Make A Difference for Ninja Forms
Plugin URI: http://www.treehugger.nl
Description: Want to make a difference in the world? Install this addon for Ninja Forms. It comes with the tools to setup protest emails or petitions.
Version: 1.0
Author: Alex Romijn
Author URI: http://www.treehugger.nl
License: #
tags: ninja forms, form builder, petition, email protest, email actions
*/

// don't load directly
if (!defined('ABSPATH')) die('-1');
// field template
add_filter( 'ninja_forms_field_template_file_paths', function ( $paths ) {
  $paths[] = plugin_dir_path( __FILE__ );
  $paths[] = plugin_dir_path( __FILE__ ) . "fields/";
  return $paths;
},10,1);
/**
 * Main class for setting up new Action
 *
 *
 * @copyright  2017 Treehugger
 */
final class NF_mad {

	/**
	 * Class constructor
	 *
	 */
    function __construct() {

		add_action( 'nf_init',function() {


			// when using confirmation plugin, check for status of submission confirmation, then change values
			if( class_exists( 'NF_confirm_mail' ) ) {
				add_action('nf_confirmmail_confirmed_after_success',function($formid,$actions,$subs) {
					$allactions = Ninja_Forms()->form($formid)->get_actions();
					foreach($allactions as $act) {
						global $wpdb;
						$type = $act->get_setting('type');
						if ($type == 'nfmakeadifference') {
							$cur = intval($act->get_setting( 'submissionscounted'));
							$newval = $cur+1;
							$newval = $newval;
							$act->update_setting( 'submissionscounted',$newval)->save();
						}
					}
				},10,3);
			}

			add_action( 'ninja_forms_after_submission', function($form_data ) {
				$formid=$form_data['form_id'];

				$actions = Ninja_Forms()->form($formid)->get_actions();
				$actions2 = $actions;


				foreach($actions as $action) {
					$type = $action->get_setting('type');
					if ($type == 'nfmakeadifference') {
						if ($type_of_protest == 'protest') { // emailprotest, so save every mail
							$oldval = get_option( 'form_'.$formid.'_protest_confirmed_subs');
							$newval = (int) $oldval + 1;
							update_option( 'form_'.$formid.'_protest_confirmed_subs',$newval );
						} else { // for petitions we need to wait for confirmation, unless confirmation plugin isn't installed
							if( class_exists( 'NF_confirm_mail' ) ) {
								// yes, confirmation plugin is installed, now check if is added as action for this form
								$hasConfirmationAction=0;
								foreach($actions2 as $action2) {
									if ($type == 'Confirmmailwithlink') {
										$hasConfirmationAction=1;
										return;
									}
								}
								if($hasConfirmationAction==0) { // still no action, then add one
									$oldval = get_option( 'form_'.$formid.'_protest_confirmed_subs');
									$newval = (int) $oldval + 1;
									update_option( 'form_'.$formid.'_protest_confirmed_subs',$newval );
								}

							} else {
								$oldval = get_option( 'form_'.$formid.'_protest_confirmed_subs');
								$newval = (int) $oldval + 1;
								update_option( 'form_'.$formid.'_protest_confirmed_subs',$newval );
							}
						}
						return;
					}
				}


			});
		} );

		// add field
		add_filter('ninja_forms_register_fields', array($this, 'addField'),10,1);

    // Register CSS and JS
    add_action( 'ninja_forms_enqueue_scripts', array( $this, 'loadCssAndJs' ), 10,0 );
    add_action( 'wp_enqueue_scripts', array( $this, 'loadCssAndJsShortcode' ), 10,0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadCssAndJsAdmin' ), 10,0 );//ninja_forms_enqueue_scripts

		add_action( 'init', array( $this, 'initialize' ) );

    // on admin init
    add_action("admin_init", array( $this,"download_csv"));
    add_action("admin_init", array( $this,"calculate_newtotal"));

		// cron
		//add_action('wp', array($this,'nfmad_cron_activation'));
		//register_deactivation_hook (__FILE__, array($this,'nfmad_cron_deactivate'));
		//add_action ('nf-confirmmail-reminder', array($this,'nfmad_cron_reminder_check'));

		// register action
		add_action('ninja_forms_register_actions', array($this,'register_actions'));

		// add form percentage to form
		add_filter( 'ninja_forms_before_form_display', function($form_id) {

			$form = Ninja_Forms()->form( $form_id )->get();
			$form_protest_confirmed_subs = $this->getActionSetting($form_id,'submissionscounted' );
			$form_protest_confirmed_subs += $this->getActionSetting($form_id,'submissionscountedoffline');
			$form_protest_goals = $this->getActionSetting($form_id, 'goal' );

			if ((!$form_protest_confirmed_subs) || $form_protest_confirmed_subs=='') $form_protest_confirmed_subs=0;
			if ((!$form_protest_goals) || $form_protest_goals=='') $form_protest_goals=1000;

			echo '
				<script>
					var MadGraphsettings'.$form_id.'={form_protest_confirmed_subs:'.$form_protest_confirmed_subs.',form_protest_goals:'.$form_protest_goals.',form_protest_perc:'.round(($form_protest_confirmed_subs/$form_protest_goals)*100).',form_protest_perc_of:"'.__(' of ','nf-mad').'"};
				</script>
			';

		}, 10, 1 );

		// shortcode:
		add_shortcode('madgraph',array($this,'madgraph_embed'));
		add_shortcode('madgraphgoal',array($this,'madgraph_formgoal'));
		add_shortcode('madgraphsignatures',array($this,'madgraph_formsignatures'));


		// detect visual composer / wpbakery support
		if( class_exists( 'Vc_Manager' ) ) {
			add_action( 'vc_before_init', function() {
				 vc_map( array(
					  "name" => __( "Make A Difference", "nf-mad" ),
					  "base" => "madgraph",
					  "class" => "",
            "icon" => plugin_dir_url(__FILE__) . "/assets/icon.png",
            "show_settings_on_create" => true,
					  "category" => __( "Make A Difference", "nf-mad"),
					  "params" => array(
						 array(
						  "type" => "textfield",
						  "class" => "",
						  "heading" => __( "Form id#", "nf-mad" ),
						  "param_name" => "formid",
						  "value" => '',
						  "description" => __( "Ninja Forms form id.", "nf-mad" )
						 ),
						 array(
						  "type" => "dropdown",
						  "class" => "",
						  "heading" => __( "Style of the graph", "nf-mad" ),
						  "param_name" => "type",
						  "value" => array('Only percentage'=>'onlypercentage','Bar - Simple'=>'barsimple','Bar - Simple Animated'=>'barsimpleanimated','Bar - 3D'=>'bar3d'),
						  "description" => __( "The style of the output", "nf-mad" )
						 ),
						 array(
						  "type" => "colorpicker",
						  "class" => "",
						  "heading" => __( "Color of percentage", "nf-mad" ),
						  "param_name" => "color",
						  "value" => '',
						  "description" => __( "The color of bar or pie part", "nf-mad" )
						 ),
						 array(
						  "type" => "colorpicker",
						  "class" => "",
						  "heading" => __( "Backgroundcolor", "nf-mad" ),
						  "param_name" => "bgcolor",
						  "value" => '',
						  "description" => __( "Backgroundcolor of graph", "nf-mad" )
						 ),
						 array(
						  "type" => "colorpicker",
						  "class" => "",
						  "heading" => __( "Textcolor", "nf-mad" ),
						  "param_name" => "color",
						  "value" => '',
						  "description" => __( "Textcolor of text inside graph", "nf-mad" )
						 ),
					  )
				 ) );
			});
		}
	}
	/**
	 * Initialize of class
	 *
	 * @param none
	 */
	function initialize() {

		// setup addon
		add_action( 'admin_menu', array( $this, 'setup_plugin' ) );

	}

	/**
	 * setup the plugin
	 *
	 * @param none
	 *
	 * @return none
	 */
	function setup_plugin() {
		// add subpage to Ninja Forms for handeling
			add_submenu_page(
				'ninja-forms',
				__( 'Make A Difference', 'nf-mad' ),
				__( 'Make A Difference', 'nf-mad' ),
				'manage_options',
				'nfmadoptions',
				array($this,'mad_settings')
			);
	}

	/**
	 * Display callback for the submenu page.
	 */
	function mad_settings() {
		?>
		<div class="wrap">
			<h1><i class="dashicons dashicons-megaphone" style="font-size: 35px; margin-right: 40px;"></i><?php _e( 'Make A Difference for Ninja Forms - Settings', 'nf-mad' ); ?></h1>
			<p><?php _e( '', 'nf-mad' ); ?></p>
            <font style="padding: 10px; font-size: 20px;"><?php _e( 'Choose form', 'nf-mad' ); ?>:</font> <select onchange="location.href='<?php echo menu_page_url('nfmadoptions',false); ?>&form='+this.value" name="select_forms" style="padding: 10px; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; -webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; -moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; background: #f8f8f8; color:#888; border:none; outline:none; font-size: 20px; border: 0px;  height: 50px !important;"><option></option>
            	<?php
          // loop through the forms and actions to only show the forms with petition action
					$formid = (int) $_GET['form'];
  					$forms = Ninja_Forms()->form()->get_forms();
  					foreach($forms as $form) {
  						$frmid = $form->get_id();
              $actions =Ninja_Forms()->form($frmid)->get_actions();
              print_r($actions);
              foreach ($actions as $action) {
                $type = $action->get_setting('type');
          			if ($type == 'nfmakeadifference') {
                  echo '<option value="'.$frmid.'"'.($formid==$frmid?' selected':'').'>#'.$frmid.' '.$form->get_setting( 'title' ).'</option>';
                }
              }
  					}
				?>
            </select><br /><br />
<?php if ($formid!='') { ?>
            <hr />
          <h3><i class="dashicons dashicons-chart-bar" style=""></i><?php _e( 'Statistics', 'nf-mad' ); ?></h3>


           <span style="padding: 10px; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; -webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; -moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; background: #f8f8f8; color:#888; border:none; outline:none; font-size: 14px; border: 0px;  height: 30px !important;"><strong><?php _e( 'Goal', 'nf-mad' ); ?>:</strong> <?php $goal= do_shortcode('[madgraphgoal formid='.$formid.']'); echo $goal; ?></span>
           <span style="padding: 10px; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; -webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; -moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; background: #f8f8f8; color:#888; border:none; outline:none; font-size: 14px; border: 0px;  height: 30px !important;"><strong><?php _e( 'Total signed/send', 'nf-mad' ); ?>:</strong> <?php $signatures= do_shortcode('[madgraphsignatures formid='.$formid.']'); $perc =  round(($signatures / $goal) * 100); echo "$signatures ($perc%)"; ?></span>
		<script>
            var MadGraphsettings<?php echo $formid; ?>={form_protest_confirmed_subs:<?php echo $signatures; ?>,form_protest_goals:<?php echo $goal; ?>,form_protest_perc:<?php echo $perc; ?>,form_protest_perc_of:"<?php _e(' of ','nf-mad'); ?>"};
        </script><br /><br />

        <?php echo do_shortcode('[madgraph type="barsimple" color="#ff0000" bgcolor="#ffffff" txtcolor="#ffffff" formid='.$formid.']'); ?>

          <br />


 			<h3><i class="dashicons dashicons-admin-tools"></i><?php _e( 'Tools', 'nf-mad' ); ?></h3>
      <?php if( class_exists( 'NF_confirm_mail' ) ) { ?>
        <form method="post" id="download_form" action=""><input type="hidden" value="<?php echo $formid; ?>" name="form_id">
          <button type="submit" name="download_csv" style="padding: 10px; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; -webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; -moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; background:#F33; color:#fff; border:none; outline:none; font-size: 14px; border: 0px;  height: 40px !important;"><?php _e( 'Export', 'nf-mad' ); ?></button>
        </form>
      <?php } ?>
        <form method="post" id="calculate_new" action=""><input type="hidden" value="<?php echo $formid; ?>" name="form_id">
          <button type="submit" name="calculate_newtotal"  style="padding: 10px; -webkit-border-radius:4px; -moz-border-radius:4px; border-radius:4px; -webkit-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; -moz-box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; box-shadow: 0 3px 0 #ccc, 0 -1px #fff inset; background:#F33; color:#fff; border:none; outline:none; font-size: 14px; border: 0px;  height: 40px !important;"><?php _e( 'Recalculate total', 'nf-mad' ); ?></button>
        </form>
    </div>
		<?php
    }
	}

  /**
	 * Export csv file with all the signed entries
	 *
	 * @param none
	 *
	 * @return $fields
	 */
   function download_csv() {

       global $wpdb;
      if (isset($_POST['download_csv'])) {
          $formid = (int) $_POST['form_id'];
          $subs = Ninja_Forms()->form($formid)->get_subs();
          $newtotal=0;
          if ( class_exists( 'NF_confirm_mail' ) ) { // if installed, export confirmed submissions only
               // check if this form has this action setup
               $actions = Ninja_Forms()->form($formid)->get_actions();
               $hasConfirm=0;
               foreach($actions as $action) {
                 $type = $action->get_setting('type');
                 if ($type == 'Confirmmailwithlink') {
                   $hasConfirm=1;
                 }
               }

               if ($hasConfirm==1) { //this export is only working to export items who are confirmed, we have to check if the submissions are confirmed

                 $output_filename = substr(md5($formid.time()),0,20) .'.csv';
                 $output_handle = @fopen('php://output', 'w');

                 header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                 header('Content-Description: File Transfer');
                 header('Content-type: text/csv');
                 header('Content-Disposition: attachment; filename=' . $output_filename);
                 header('Expires: 0');
                 header('Pragma: public');

                 $first=0;
                 // generate all confirmed signers
                  foreach($subs as $sub) {
                    $field_value = $sub->get_extra_value( '_confirmation_status' );
                    if ($field_value==1) {
                      $field_values = $sub->get_field_values();
                      if ($first==0) {
                        $titles = array();
                         foreach ($field_values as $key => $val) {
                             $titles[] = $key;
                         }
                         $first++;
                         fputcsv($output_handle, $titles);
                      }
                      fputcsv($output_handle, (array) $field_values);
                    }
                  }

                  fclose($output_handle);

                  die();
               }
          }

     }
   }

   /**
 	 * Calculate new total of petition
 	 *
 	 * @param none
 	 *
 	 * @return $fields
 	 */
    function calculate_newtotal() {
       if (isset($_POST['calculate_newtotal'])) {
          $formid = (int) $_POST['form_id'];
          $subs = Ninja_Forms()->form($formid)->get_subs();
          $newtotal=0;
          if ( class_exists( 'NF_confirm_mail' ) ) {

             // check if this form has this action setup
             $actions = Ninja_Forms()->form($formid)->get_actions();
             $hasConfirm=0;
             foreach($actions as $action) {
               $type = $action->get_setting('type');
               if ($type == 'Confirmmailwithlink') {
                 $hasConfirm=1;
               }
             }

             if ($hasConfirm==1) { // because of the installed confirmation addon, we have to check if the submissions are confirmed
                foreach($subs as $sub) {
                  $field_value = $sub->get_extra_value( '_confirmation_status' );
                  if ($field_value==1) $newtotal++;
                }
             } else {
                $newtotal = count($subs);
             }
          } else {
            $newtotal = count($subs);
          }

          // place new total in form action setting
          $actions = Ninja_Forms()->form($formid)->get_actions();
          foreach($actions as $action) {
            $type = $action->get_setting('type');
            if ($type == 'nfmakeadifference') {
              $action->update_setting('submissionscounted',$newtotal);
              $action->save();
            }
          }

          // send a message in admin area that calculation was done
          add_action( 'admin_notices', function () {
              ?>
              <div class="notice notice-success">
                  <p><?php _e( 'Submissions recounted!', 'nf-mad' ); ?></p>
              </div>
              <?php
          });
       }
    }

	/**
	 * add new field
	 *
	 * @param $fields
	 *
	 * @return $fields
	 */

	function addField($fields) {
		require_once(plugin_dir_path(__FILE__)."/fields/class.field.madgraph.php");

		$fields['MadGraph'] = new MadGraph;
		return $fields;
	}

	/**
	 * Get action setting from form
	 *
	 * @param $form_id
	 *
	 * @return $settings array()
	 */
	function get_action_settings($form_id) {
		$actions = Ninja_Forms()->form( $form_id )->get_actions();

		foreach($actions as $action) {
			$type = $action->get_setting( 'type' );
			if ($type=='nfmakeadifference') {
				$settings=$action->get_settings();
			}
		}
		return $settings;
	}

	/**
	 * Get field name from tag
	 *
	 * @param $tag
	 *
	 * @return field_name
	 */
	function get_field_name($tag) {
		$tag = str_replace("{","",$tag);
		$tag = str_replace("}","",$tag);
		$fields = explode(":",$tag);
		//$f = explode("_",$fields[1]);
		return $fields[1];
	}


	/**
	 * Register Ninja Forms custom action
	 *
	 * @param $actions
	 *
	 * @return $actions
	 */
	function register_actions( $actions ) {
	  require_once(realpath(plugin_dir_path(__FILE__))."/includes/"."nf-action-mad.php");
	  $actions['nfmakeadifference'] = new NF_Action_MakeADifference();
	  return $actions;
	}


	/**
	 * Load custom CSS and/or JS for this plugin
	 *
	 * @param none
	 *
	 * @return none
	 */
  public function loadCssAndJs() {
   	wp_enqueue_script( 'NF_mad_form_js', plugins_url('assets/form.js', __FILE__), array('jquery') );
  	wp_enqueue_style( 'NF_mad_form_css', plugins_url('assets/form.css', __FILE__));
  }

  /**
	 * Load custom CSS and/or JS for this plugin, only code for the shortcodes
	 *
	 * @param none
	 *
	 * @return none
	 */
  public function loadCssAndJsShortcode() {
   	wp_enqueue_script( 'NF_mad_formshort_js', plugins_url('assets/frmshort.js', __FILE__), array('jquery') );
  	wp_enqueue_style( 'NF_mad_formshort_css', plugins_url('assets/form.css', __FILE__));
  }


	/**
	 * Load custom CSS and/or JS for this plugin admin
	 *
	 * @param none
	 *
	 * @return none
	 */
  public function loadCssAndJsAdmin() {
   	wp_enqueue_script( 'NF_mad_admin_js', plugins_url('assets/admin.js', __FILE__), array('jquery') );
  	wp_enqueue_style( 'NF_mad_admin_css', plugins_url('assets/form.css', __FILE__));
  }

	/**
	 * Shortcode function
	 *
	 * @param none
	 */
	function madgraph_embed($atts, $content=NULL) {
		$atts = shortcode_atts( array(
			'type' => 'onlypercentage',
			'formid' => '',
			'color' => '#ff0000',
			'bgcolor' => '#ffffff',
			'txtcolor' => '#ffffff'
		), $atts, 'madgraph' );

    $formid = (int) $atts['formid'];
    // get settings for the forms
    $form = Ninja_Forms()->form( $formid )->get();

    $form_protest_confirmed_subs = $this->getActionSetting($formid,'submissionscounted' );
    $form_protest_confirmed_subs += $this->getActionSetting($formid,'submissionscountedoffline');
    $form_protest_goals = $this->getActionSetting($formid, 'goal' );

    if ((!$form_protest_confirmed_subs) || $form_protest_confirmed_subs=='') $form_protest_confirmed_subs=0;
    if ((!$form_protest_goals) || $form_protest_goals=='') $form_protest_goals=1000;

    $script= '
      <script>
        var MadGraphsettings'.$formid.'={form_protest_confirmed_subs:'.$form_protest_confirmed_subs.',form_protest_goals:'.$form_protest_goals.',form_protest_perc:'.round(($form_protest_confirmed_subs/$form_protest_goals)*100).',form_protest_perc_of:"'.__(' of ','nf-mad').'"};
      </script>
    ';

		switch($atts['type']) {

			case "barsimple":
      case "barsimpleanimated":
				$html .= '
					<div class="bar barsimple" style="background-color: '.$atts['bgcolor'].'">
						<span class="perc" style="color: '.$atts['txtcolor'].'; background-color: '.$atts['color'].';"></span>
					</div>
				';
			break;

      case "bar3d":
      case "bar3danimated":
				$html .= '
					<div class="bar bar3d" style="background-color: '.$atts['bgcolor'].'">
						<span class="perc" style="color: '.$atts['txtcolor'].'; background-color: '.$atts['color'].'; "></span>
					</div>
				';
			break;

			default:
				$html = '<span class="numberfrom"></span><span class="between"></span><span class="goal"></span>';
			break;
		}

		return $script.'<div class="madgraph" formid="'.$formid.'" graph-type="'.$atts['type'].'">'.$html.'</div>';
	}

	/**
	 * get value from action
	 *
	 * @param none
	 */
	function getActionSetting($formid,$key) {
		$allactions = Ninja_Forms()->form($formid)->get_actions();
		$value=0;
		foreach($allactions as $act) {
			$type = $act->get_setting('type');
			if ($type == 'nfmakeadifference') {
				//print_r($act->get_settings());
				//echo "<hr>";
				$value = $act->get_setting( $key );
			}
		}
		return $value;
	}

	/**
	 * update value from action
	 *
	 * @param none
	 */
	function updateActionSetting($formid, $key, $value) {
		$allactions = Ninja_Forms()->form($formid)->get_actions();
		$value=0;
		foreach($allactions as $act) {
			$type = $act->get_setting('type');
			if ($type == 'nfmakeadifference') {
				$act->update_setting( $key, $value)->save();
			}
		}
	}
	/**
	 * Shortcode function
	 *
	 * @param none
	 */
	function madgraph_formgoal($atts, $content=NULL) {
		$atts = shortcode_atts( array(
			'formid' => '',
		), $atts, 'madgraphgoal' );

		$form_protests_goals = $this->getActionSetting($atts['formid'],'goal');
		return ($form_protests_goals==''?1000:$form_protests_goals);
	}

	/**
	 * Shortcode function
	 *
	 * @param none
	 */
	function madgraph_formsignatures($atts, $content=NULL) {
		$atts = shortcode_atts( array(
			'formid' => '',
		), $atts, 'madgraphsignatures' );

		$form_protest_confirmed_subs = $this->getActionSetting($atts['formid'],'submissionscounted');
		$form_protest_confirmed_subs += $this->getActionSetting($atts['formid'],'submissionscountedoffline');
		return $form_protest_confirmed_subs;
	}


}

/**
 * Init this class
 *
 */
new NF_mad();


?>
