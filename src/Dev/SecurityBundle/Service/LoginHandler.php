<?php

namespace Dev\SecurityBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    private $router;
    private $session;

    public function __construct(RouterInterface $router, Session $session)
    {
        $this->router  = $router;
        $this->session = $session;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'status' => Response::HTTP_OK
            ]);
        } else {
            if ($this->session->get('_security.main.target_path')) {
                $url = $this->session->get('_security.main.target_path');
            } else {
                $url = $this->router->generate('dev_encrypt.default.direct_stream');
            }

            return new RedirectResponse($url);
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse([
                'status'       => Response::HTTP_UNAUTHORIZED,
                'messageError' => $exception->getMessage()
            ]);
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);

            return $response;
        } else {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

            return new RedirectResponse($this->router->generate('login'));
        }
    }
}
