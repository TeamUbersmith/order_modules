<?php

/**
 * My Order Module
 *
 * You will want to change order_module_myordermodule to something a little
 * more descriptive, however be sure to retain the order_module_ prefix.
 *
 * When complete, place your finished module into include/order_modules/
 *
 * @package ubersmith_customizations
 */

/**
 * My Order Module Class
 *
 * @package ubersmith_customizations
 */
class order_module_myordermodule extends order_module
{
	/**
	 * 'interactive' determines whether or not the order module should display
	 * a popup to allow the administrator to change settings, make a
	 * selection, etc.  If the order module is only meant to process an action
	 * without any additional input, set 'interactive' to false.
	 *
	 * @var bool
	 */
	public $interactive = true;

	/**
	 * 'complete_view' indicates whether the order module has data to display
	 * after processing has been completed. For example, a module that
	 * performs fraud detection may have some output which would be useful to
	 * display to the administrator. If you want the module to display data
	 * after completion, set 'complete_view' to true. A module which simply
	 * sends an email or performs some simple task may not have any
	 * data to display. In this case, set 'complete_view' to false.
	 *
	 * @var bool
	 */
	public $complete_view = true;

	/**
	 * 'reprocess' determines if a module should be able to be run more than
	 * once. This is useful for modules that reach out to an external service
	 * that may require an additional call, or a call once order data has
	 * been updated.
	 *
	 * @var bool
	 */
	public $reprocess = false;

	/**
	 * The name of the order module. This title will be shown in your order
	 * queues in 'Setup & Admin', as well as the drop down for the popup when
	 * adding a new order module to an order queue.
	 *
	 * @return string
	 */
	public static function name()
	{
		return uber_i18n('My Order Module');
	}

	/**
	 * An optional method for setting fields to display on order forms.
	 *
	 * @return array
	 */
	public static function fields()
	{
		return array(
			'ach_acct' => array(
				'type' => 'text',
				'name' => 'acct',
				'label' => uber_i18n('Account Number'),
				'extra_label' => '',
				'extra' => array(
					'attributes' => array(
						'size' => 16,
					),
				),
				'rules' => array(),
			),
			'username' => array(
				'label'   => uber_i18n('Username Style'),
				'type'    => 'select',
				'options' => array(
					'email'    => uber_i18n('Email Address'),
					'username' => uber_i18n('Ubersmith Username'),
				),
				'default' => 'email',
			),
			'discount_type' => array(
				'type' => 'radio',
				'name' => 'discount_type',
				'label' => uber_i18n('Discount Type'),
				'radio_group' => array(
					'0' => '$',
					'1' => '%',
				),
			),
		);
	}

	/**
	 * This function performs the main function of the order module, whatever
	 * that may be. You can have the module call out to a remote service,
	 * update the order in some way, or perform other tasks. To allow an
	 * administrator to interact with the module, have this function return
	 * 'false' until an expected input or other interaction has been provided.
	 * Then, return 'true'.
	 *
	 * @return bool
	 */
	public function process()
	{
		$order =& $this->order;
		$data  = $order->data();
		$info  = $order->info();

		if (empty($info['my_order_module'])) {
			$order->info_set('my_order_module','You did it!');

			echo '<div><span>';
			echo h(uber_i18n("If you click process, you'll never see me again!"));
			echo '</span></div>';

			return false;
		}

		return true;
	}

	/**
	 * This function displays the output of the order module. Any data
	 * collected by your process function and stored in the order can be
	 * displayed here. In this example, we're dumping out the complete
	 * details of the order for your reference.
	 *
	 * @return string
	 */
	public function view()
	{
		$order =& $this->order;
		$data  = $order->data();
		$info  = $order->info();

		$sky = $this->config('my_option');
		if (empty($sky)) {
			$sky = uber_i18n('dunno!');
		}

		echo '<div><span>';
		echo h(uber_i18nf('Is the sky blue?: %s',$sky));
		echo '</span></div>';

		echo '<pre>';
		ph(var_dump($info,true));
		echo '</pre>';
	}

	/**
	 * This function returns an array of configuration options that will be
	 * displayed when the module is configured for your order queue. You can
	 * add as many configuration items as you like. Retrieval of the
	 * configuration data is shown in the view() function above.
	 *
	 * @return array
	 */
	public function config_items()
	{
		return array(
			'my_textfield' => array(
				'label'  => uber_i18n('A Text Field'),
				'type'   => 'text',
				'size'   => '20',
				'default'=> '',
			),
			'my_option' => array(
				'label'   => uber_i18n('Is the sky blue?'),
				'type'    => 'select',
				'options' => array(
					'yes' => uber_i18n('Yes'),
					'no' => uber_i18n('No'),
				),
				'default' => 'false',
			),
		);
	}
}

// end of script
