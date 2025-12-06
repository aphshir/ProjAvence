<?php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\Security\Http\HttpUtils;

class CsrfAwareAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function __construct(
        HttpKernelInterface $httpKernel,
        HttpUtils $httpUtils,
        array $options = [],
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($httpKernel, $httpUtils, $options, $logger);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof InvalidCsrfTokenException) {
            $session = $request->getSession();
            $session->invalidate();

            $session->getFlashBag()->add('warning', 'csrf.session_expired');

            return $this->httpUtils->createRedirectResponse(
                $request,
                $this->options['login_path'] ?? 'app_login'
            );
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
