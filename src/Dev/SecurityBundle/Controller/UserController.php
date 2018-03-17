<?php

namespace Dev\SecurityBundle\Controller;

use Dev\SecurityBundle\Form\CreateUserType;
use Dev\SecurityBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    public function loginFormAction(Request $request)
    {
        $form = $this->get('form.factory')->create(
            UserType::class,
            null,
            [
                'action' => $this->get('router')->generate('login_check')
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $form->getData();
            $em   = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $response = [];
        $response['status'] = 'ok';
        $response['view'] = $this
            ->get('templating')
            ->render(
                '@DevSecurity/User/loginFormModal.html.twig',
                array(
                    'form' => $form->createView()
                )
            );

        return new JsonResponse($response);
    }

    public function userPanelAction()
    {
        $response = [];
        $response['status'] = 'ok';
        $response['view'] = $this
            ->get('templating')
            ->render(
                '@DevSecurity/User/modalUserPanel.html.twig',
                array(
                    'user' => $this->getUser()
                )
            );

        return new JsonResponse($response);
    }

    public function inscriptionAction(Request $request)
    {
        $form = $this->get('form.factory')->create(CreateUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $form->getData();
            $em   = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        return $this->render('@DevSecurity/User/inscription.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction()
    {
        $token = $this->get('security.context');
        $user = $this->getUser();
        $form = $this->get('form.factory')->create(CreateUserType::class, $user);

        return $this->render('DevSecurityBundle:User:inscription.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
