<?php
/**
 * My Order Module
 */

/**
 * My Order Module Class
 *
 * Please change the class name from "order_module_sample" to something descriptive, 
 * but retain the "order_module_" prefix to ensure that it's detected by Ubersmith. 
 * Please review the existing modules there and make sure you don't choose 
 * a filename that has already been used. This file will need to be named 
 * whatever this class is called with a ".php" extension.
 *
 * When you're ready to use your module, simply place this file in the 
 * "include/order_modules/" subdirectory of your Ubersmith base directory.
 */
class order_module_sample extends order_module
{
	/**
	 * 'interactive' determines whether or not the order module should display 
	 * a popup to allow the administrator to change settings, make a 
	 * selection, etc.  If the order module is only meant to process an action 
	 * without any additional input, set 'interactive' to false.
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
	 */
	public $complete_view = true;

	/**
	 * 'reprocess' determines if a module should be able to be run more than 
	 * once. This is useful for modules that reach out to an external service 
	 * that may require an additional call, or a call once order data has 
	 * been updated.
	 */
	public $reprocess = true;

	/**
	 * The name of the order module. This title will be shown in your order
	 * queues in 'Setup & Admin', as well as the drop down for the popup when
	 * adding a new order module to an order queue.
	 *
	 * @return string
	 */
	public static function name()
	{
		return uber_i18n('IFTTT Event');
	}

	/**
	 * An optional method for setting fields to display on order forms.
	 *
	 * @return array
	 */
	public static function fields()
	{
//TODO: evaluate this
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
		
		// Set key
		$key = $this->config('ifttt_maker_key');
		if (empty($key)) {
			return PEAR::raiseError('No API key specified',1);
		}
		
		// Set event
		$event = $this->config('ifttt_maker_event');
		if (empty($event)) {
			return PEAR::raiseError('No event specified',1);
		}
		
		// Set Values
		$value1_key = $this->config('value1');
		if (empty($value1_key)) {
			$value1 = '';
		} else {
			$value1 = empty($info[$value1_key]) ? '' : $info[$value1_key];
		}
		
		if (empty($info['ifttt_maker_response'])) {
			echo '<span style="color: #4a4a4a">'. h('No response from IFTTT yet. Please process the appropriate step.') .'</span>';
			
			return false;
		}
		
		// Initialize cURL client
		$client = new uber_http_client();
		
		// Fill data for parameters
		$request = [
			"value1" => $value1,
			"value2" => "value two",
			"value3" => "value three"
		];
		
		// Set headers for JSON request
		$headers = [
			'Content-Type: application/json',
		];
		
		// Execute request to IFTTT Maker
		$url = 'https://maker.ifttt.com/trigger/'. u($event) .'/with/key/'. u($key);
		$result = $client->post(
			$url,
			json_encode($request),
			$headers
		);
		if (PEAR::isError($result)) {
			return $result;
		}
		
		// Save response from IFTTT Maker in order info with date and time stamps
		$this->order->info_set('ifttt_maker_response',[
			'ts'   => d() .' '. t(),
			'text' => $result
		]);
		
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
		
		$response = $this->order->info('ifttt_maker_response');
		echo '<div>';
		if (!empty($response)) {
			echo '<span style="color: #4a4a4a">'. h('Last successful response ['. $response['ts'] .']:') .'</span><br>'. $response['text'];
		} else {
			echo '<span style="color: #4a4a4a">'. h('No response from IFTTT yet') .'</span>';
		}
		echo '<br><br><span style="color: #4a4a4a"><a target="_blank" href="https://internal-api.ifttt.com/myrecipes/personal">View or create your IFTTT Recipes</a></span>';
		echo '<br><br><span style="color: #4a4a4a"><a target="_blank" href="https://internal-api.ifttt.com/maker">View Maker Channel</a></span>';
		echo '</div>';
		
/*
echo '<pre>';
ph(var_dump($info,true));
echo '</pre>';
*/
		return true;
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
		$fields = [
			'ip_address'   => uber_i18n('IP Address'),
			'client_id'    => uber_i18n('Client ID'),
			'first'        => uber_i18n('First Name'),
			'last'         => uber_i18n('Last Name'),
			'company'      => uber_i18n('Company'),
			'email'        => uber_i18n('Email'),
			'uber_login'   => uber_i18n('Ubersmith Login Name'),
			'address'      => uber_i18n('Address'),
			'city'         => uber_i18n('City'),
			'state'        => uber_i18n('State'),
			'zip'          => uber_i18n('Zip Code'),
			'country'      => uber_i18n('Country/Territory'),
			'phone'        => uber_i18n('Phone'),
		];
		
		return [
			'ifttt_maker_key' => [
				'label'   => uber_i18n('Maker Channel Key'),
				'type'    => 'text',
				'size'    => '32',
				'default' => '',
				'class'   => 'input_required',
//				'required' => true
			],
			'ifttt_maker_event' => [
				'label'   => uber_i18n('Maker Event Name'),
				'type'    => 'text',
				'size'    => '32',
				'default' => 'ubersmith_order',
				'class'   => 'input_required',
//				'required' => true
			],
//TODO: make these an edit/select combo box if possible
			'value1' => [
				'label'   => uber_i18n('Value 1'),
				'type'    => 'select',
				'options' => $fields,
				'default' => 'ip_address',
			],
			'value2' => [
				'label'   => uber_i18n('Value 2'),
				'type'    => 'select',
				'options' => $fields,
				'default' => 'first',
			],
			'value3' => [
				'label'   => uber_i18n('Value 3'),
				'type'    => 'select',
				'options' => $fields,
				'default' => 'last',
			],
		];
	}
}

// end of script
