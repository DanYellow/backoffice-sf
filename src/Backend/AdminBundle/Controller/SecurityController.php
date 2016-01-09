<?php

namespace Backend\AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use FOS\UserBundle\Controller\SecurityController as BaseController;


class SecurityController extends BaseController
{
	public function loginAction(Request $request)
	{
	    $securityContext = $this->container->get('security.context');
	    $router = $this->container->get('router');

	    if ($securityContext->isGranted('ROLE_ADMIN')) {
	    	return $this->redirectToRoute('backend_admin_gallery_display');
	    }

	    $response = parent::loginAction($request);
	    return $response;
	}
}

