<?php

class ControllerExtensionPaymentNextpay extends Controller {

	private $error = array();

	public function index() {

		$this->load->language('extension/payment/nextpay');
		$this->document->setTitle($this->language->get('doc_title'));
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) 
		{
			$this->model_setting_setting->editSetting('nextpay', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));

		}
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_pin'] = $this->language->get('entry_pin');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['pin'])) 
		{

			$data['error_pin'] = $this->error['pin'];
		} else 
		{
			$data['error_pin'] = '';
		}
		if (isset($this->error['sh1'])) 
		{
			$data['error_sh1'] = $this->error['sh1'];
		} else 
		{
			$data['error_sh1'] = '';
		}
		
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/nextpay', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/nextpay', 'token=' . $this->session->data['token'], true);
		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], true);
		if (isset($this->request->post['nextpay_api'])) {
			$data['nextpay_api'] = $this->request->post['nextpay_api'];
		} else {
			$data['nextpay_api'] = $this->config->get('nextpay_api');
		}
		

		if (isset($this->request->post['nextpay_order_status_id'])) {
			$data['nextpay_order_status_id'] = $this->request->post['nextpay_order_status_id'];
		} else {
			$data['nextpay_order_status_id'] = $this->config->get('nextpay_order_status_id');
		}
		
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['nextpay_status'])) {
			$data['nextpay_status'] = $this->request->post['nextpay_status'];
		} else {
			$data['nextpay_status'] = $this->config->get('nextpay_status');
		}
		if (isset($this->request->post['nextpay_sort_order'])) {
			$data['nextpay_sort_order'] = $this->request->post['nextpay_sort_order'];
		} else {
			$data['nextpay_sort_order'] = $this->config->get('nextpay_sort_order');
		}
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/nextpay.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/nextpay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->request->post['nextpay_api']) {
			$this->error['pin'] = $this->language->get('error_pin');
		}
		return !$this->error;

	}

}

?>