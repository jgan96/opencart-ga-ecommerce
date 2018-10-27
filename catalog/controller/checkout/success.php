<?php
class ControllerCheckoutSuccess extends Controller {
	public function calculateTotal()
	{
			// Totals
			$this->load->model('extension/extension');
			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();
			// Display prices
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$sort_order = array();
				$results = $this->model_extension_extension->getExtensions('total');
				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}
				array_multisort($sort_order, SORT_ASC, $results);
				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);
						$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
					}
				}
				$sort_order = array();
				foreach ($total_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
				array_multisort($sort_order, SORT_ASC, $total_data);
			}
			$data['totals'] = array();
			foreach ($total_data as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'])
				);
			}
			return $data['totals'];
	}
	public function index() {
		$this->load->language('checkout/success');

		//variables to pass to google analytics
		$data['gaCartContents'] = array();
		$data['gaOrderId'] = 0;
		$data['gaRevenue'] = 0;
		$data['gaTax'] = 0;
		$data['gaShipping'] = 0;
		//$data['gaTax'] = ($this->cart->getTotal() ? $this->cart->getTaxes() : '0');
		//$data['gaShipping'] = $data['gaRevenue'] . $data['gaTax'] . ($this->cart->getSubTotal() ? $this->cart->getSubTotal() : '0');

		if (isset($this->session->data['order_id'])) {
			//get cart values for ga
			foreach ($this->cart->getTaxes() as $key => $value) //$data['gaTax'] = $this->cart->getTaxes(); //returns an array
			{
				if ($value > 0)
				{
					$data['gaTax'] += $value;
				}
			}

			//iterate through the totals returned in the cart
			foreach ($this->calculateTotal() as $key => $value)
			{
				if (stripos($value['title'], 'shipping') !== false) //find the shipping cost
				{
						$data['gaShipping'] += preg_replace("/[^0-9\.]/", '', $value['text']); //remove any extraneous currency or digit placemarkers
				}
			}

			$data['gaOrderId'] = $this->session->data['order_id'];
			//$data['gaRevenue'] = $this->cart->getTotal(); //does not factor in shipping or tax?? what
			$data['gaRevenue'] = $data['gaShipping'] + $data['gaTax'] + $this->cart->getSubTotal();
			$data['gaCartContents'] = $this->cart->getProducts();

			$this->cart->clear();

			// Add to activity log
			$this->load->model('account/activity');

			if ($this->customer->isLogged()) {
				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'order_id'    => $this->session->data['order_id']
				);

				$this->model_account_activity->addActivity('order_account', $activity_data);
			} else {
				$activity_data = array(
					'name'     => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
					'order_id' => $this->session->data['order_id']
				);

				$this->model_account_activity->addActivity('order_guest', $activity_data);
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['button_continue'] = $this->language->get('button_continue');

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success_ga.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success_ga.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/success_ga.tpl', $data));
		}
	}
}
