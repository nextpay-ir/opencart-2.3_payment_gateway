<?php
class ControllerExtensionPaymentNextpay extends Controller {
	public function index() {
		$this->language->load('extension/payment/nextpay');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['back'] = $this->url->link('checkout/payment', '', true);
		$order_id = $this->session->data['order_id'];
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		

		
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$amount = round($amount);
		//$amount = $this->currency->convert($amount, $order_info['currency_code'], "RLS");
			
		$data['order_id'] = $order_id;
		$data['redirect_url']  =  ($this->url->link('extension/payment/nextpay/callback','', true));
		
		$amount = trim($amount);
		$invoiceNumber = time();
		$api_key = trim($this->config->get('nextpay_api'));
		$redirectAddress =$data['redirect_url'];
		
		$params = array(
		  "api_key" => $api_key,
		  "order_id" => $order_id,
		  "amount" => $amount,
		  "callback_uri" => $redirectAddress
		);
		
		$trans_id = "";
		$code_error = -1000;
		
		$soap_client = new SoapClient("https://api.nextpay.org/gateway/token.wsdl", array('encoding' => 'UTF-8'));
		$res = $soap_client->TokenGenerator($params);

		$res = $res->TokenGeneratorResult;

		if ($res != "" && $res != NULL && is_object($res)) {
		    if (intval($res->code) == -1){
			$trans_id = $res->trans_id;
			$data['trans_id'] = $trans_id;
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/nextpay.tpl')) {

				return $this->load->view($this->config->get('config_template') . '/template/payment/nextpay.tpl', $data);

			}else{			    
			    $data['code_error'] = $code_error;			
			    $data['description'] = "فایل تمپلیت یافت نشد";
			}
		    }else{
			$code_error = $res->code;
			$data['code_error'] = $code_error;			
			$data['description'] = "خطا";
		    }
		}else{
		    $data['code_error'] = $code_error;			
		    $data['description'] = "خطا در پاسخ دهی به درخواست با SoapClinet";		    
		    }
		    
		$data['action'] = "https://api.nextpay.org/gateway/payment/".$trans_id;
		return $this->load->view('default/template/payment/nextpay.tpl', $data);
		
		
		
		/*if(!function_exists('timestamp_to_iso8601'))
		{
			$pat = dirname(__FILE__);
			$get = 'nusoap2.php';
			$pat .= '/';
			for($i = 0; $i < 10; $i++)
			{
				if(!file_exists($pat.$get))
				{
					$pat.="../";
				}else
				{
					break;
				}
			}
			include_once $pat . 'nusoap2.php';
		}*/
		
	}

	public function callback() 
	{
		$this->language->load('payment/nextpay');
		@session_start();

		$order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0;

		$this->document->setTitle($this->language->get('text_heading'));
		$data['text_wait'] = $this->language->get('text_wait');
		$data['text_heading'] = $this->language->get('text_heading');
		$data['text_results'] = $this->language->get('text_results');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_continue'] = $this->language->get('button_continue');
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', 'SSL'), 'separator' => false);
		$data['breadcrumbs'][] = array('text' => $this->language->get('text_heading'), 'href' => $this->url->link('payment/nextpay/callback', '', 'SSL'), 'separator' => $this->language->get('text_separator'));
		$data['error_warning'] = '';

		if( isset($_POST['trans_id']) && isset($_POST['order_id']) )
		{

			$api_key = trim($this->config->get('nextpay_api'));
			$trans_id = $_POST['trans_id'];
			
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);
			
			$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
			$amount = round($amount);
			//$amount = $this->currency->convert($amount, $order_info['currency_code'], "RLS");
	
			if ($order_info) 
			{
				/*if(!function_exists('timestamp_to_iso8601'))
				{
					$pat = dirname(__FILE__);
					$get = 'nusoap2.php';
					$pat .= '/';
					for($i = 0; $i < 10; $i++)
					{
						if(!file_exists($pat.$get))
						{
							$pat.="../";
						}else
						{
							break;
						}
					}
					include_once $pat . 'nusoap2.php';
				}*/
				
				$params = array(
				  "api_key" => $api_key,
				  "order_id" => $order_id,
				  "amount" => $amount,
				  "trans_id" => $trans_id
				);
				
				$soap_client = new SoapClient("https://api.nextpay.org/gateway/verify.wsdl", array('encoding' => 'UTF-8'));
				$res = $soap_client->PaymentVerification($params);

				$res = $res->PaymentVerificationResult;
				$code = -1000;

				if ($res != "" && $res != NULL && is_object($res)) {
				    $code = $res->code;
				}
				
				
				if (intval($code) == 0)
				{
				  $data['continue'] = $this->url->link('checkout/success');
				  $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('nextpay_order_status_id'), 'کد ارجاع : '.$trans_id, true);
				  $data['error_warning'] = NULL;
				  $location = ($this->url->link('checkout/success', '', 'SSL'));
				  
				  if (!headers_sent()) {
					  header('Location: ' . $location);
				    } else
				    {
					    echo '<script type="text/javascript">';
					    echo 'window.location.href="' . $location . '";';
					    echo '</script>';
				    }

				}
				else 
				{
				    $data['error_warning'] = $this->language->get('error_veryfi');
				    $data['continue'] = $this->url->link('checkout/checkout');
				}
			}
			else 
			{
				$data['error_warning'] = $this->language->get('error_order_id');
				$data['continue'] = $this->url->link('checkout/checkout');
			}
		}
		else 
		{
			$data['error_warning'] = $this->language->get('error_order_2');
			$data['continue'] = $this->url->link('checkout/checkout');
		}
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/nextpay_confirm.tpl')) 
		{
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/nextpay_confirm.tpl', $data));

		} else 
		{
			$this->response->setOutput($this->load->view('default/template/payment/nextpay_confirm.tpl', $data));
		}
	

	}
}
?>