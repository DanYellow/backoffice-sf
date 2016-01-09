<?php
  namespace Backend\AdminBundle\CustomClass;

  use Symfony\Bundle\FrameworkBundle\Controller\Controller;


  class Utils extends Controller {

    public function isUserLogged () {
        dump($this);
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            throw "How did you do that?";
        }
    }
  }