<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $this->viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
        $this->viewHelperManager->get('inlineScript')->prependFile('/bower_components/parsleyjs/dist/parsley.min.js');
        $this->viewHelperManager->get('inlineScript')->prependFile('/js/application/index/index.js');


		$form = $this->getServiceLocator()->get('Application\Form\Chat');
		$model = $this->getServiceLocator()->get('Core\Model\Chat');
        if ($this->getRequest()->isXmlHttpRequest()) {
            $postData = $this->getRequest()->getPost();
            if ($postData && $postData['type']=='add') {
            	unset($postData['type']);
            	$form->setInputFilter($form->getInputFilter());
				$form->setData($postData);
                if ($form->isValid()) {
                	$submitData = $form->getData();
            		$submitData['doc'] = date("Y-m-d H:i:s");
            		if ($model->addChatRecord($submitData)) {
                        $json['success'] = true;
                        $json['form_msg'] = 'Record inserted sucessfully';
                        $json['form_msg_class'] = 'alert-success';
            		} else {
						$json['form_msg'] = 'Issue in Record insertion';
                    }
            	} else {
                    $errors = array();
                    $get_messages = $form->getMessages();
                    if (is_array($get_messages) && count($get_messages)) {
                        foreach($form as $element) {
                            if (array_key_exists($element->getAttribute('name'), $get_messages)) {
                                $errors[$element->getAttribute('name')] = $element->getAttribute('error_msg');
                            }
                        }
                        $json['field_errors'] = $errors;
                    }
            	}
            	die(json_encode($json));
            }

            $result = $model->getChatRecords();
            if ($result && count($result) > 0) {
                $json['result'] = $result;
                $json['status'] = true;
            } else {
                $json['result'] = '';
                $json['msg'] = 'Record Not found';
                $json['status'] = false;
            }
            die(json_encode($json));
        }
        return array(
            'form' => $form
        );
    }
}