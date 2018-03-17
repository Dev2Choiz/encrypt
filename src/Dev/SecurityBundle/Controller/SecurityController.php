<?php

namespace Dev\SecurityBundle\Controller;

use Dev\SecurityBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('dev_encrypt.default.stream');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $form = $this->get('form.factory')->createNamed(null, UserType::class, null, array(
            'action' => $this->get('router')->generate('login_check')
        ))->handleRequest($request);

        return $this->render('@DevSecurity/Security/login.html.twig', array(
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'form'         => $form->createView(),
        ));
    }
}
