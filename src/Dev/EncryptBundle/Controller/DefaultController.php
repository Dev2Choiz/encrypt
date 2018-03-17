<?php

namespace Dev\EncryptBundle\Controller;

use Dev\EncryptBundle\Service\JsConfig;
use Dev\EncryptBundle\Form\TypeTaskType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package Dev\EncryptBundle\Controller
 *
 */
class DefaultController extends Controller
{
    /**
     * @Dev\EncryptBundle\Annotation\JsConfig(method="streamParams")
     */
    public function streamAction()
    {
        return $this->render('@DevEncrypt/Default/stream.html.twig', array(
            'formTask' => $this->getTaskListForm()->createView()
        ));
    }

    public function streamParams()
    {
        $this->setParamsJsConfig();
        JsConfig::addVariable('string', 'mode', 'worker');
        JsConfig::addVariable(
            'string',
            'url.formFactory',
            $this->get('router')->generate('dev_encrypt.task.form_factory', ['task' => '===task==='])
        );
    }

    /**
     * @Dev\EncryptBundle\Annotation\JsConfig(method="directStreamParams")
     */
    public function directStreamAction()
    {
        return $this->render('@DevEncrypt/Default/stream.html.twig', array(
            'formTask' => $this->getTaskListForm()->createView()
        ));
    }

    public function directStreamParams()
    {
        $this->setParamsJsConfig();
        JsConfig::addVariable('string', 'mode', 'direct');
        JsConfig::addVariable(
            'string',
            'url.formFactory',
            $this->get('router')->generate('dev_encrypt.task.form_factory', ['task' => '===task==='])
        );

        $variablesCss = $this->getParameter('variablesCss');
        $variablesJs = [];
        foreach ($variablesCss as $variableCss => $value) {
            $variable = str_replace("-", "_", $variableCss);
            $variablesJs [$variable] = $value;
            JsConfig::addVariable('string', $variable, $value, false);
            JsConfig::addCssVariable($variableCss, $value);
        }
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getTaskListForm()
    {
        return $this->get('form.factory')->create(TypeTaskType::class);
    }

    public function setParamsJsConfig()
    {
        JsConfig::addVariable(
            'string',
            'url.upload',
            $this->get('router')->generate(
                'dev_encrypt.upload.upload',
                ['task' => '===task===', 'idProcess' => '===idProcess===', 'mode' => '===mode===']
            )
        );
        JsConfig::addVariable(
            'string',
            'url.user.panel',
            $this->get('router')->generate('dev_security.user.userPanel')
        );
        JsConfig::addVariable('string', 'longPolling.setTimeOut', 2000);
    }
}
