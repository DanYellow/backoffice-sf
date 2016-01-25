<?php

namespace Backend\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use Backend\AdminBundle\Entity\GalleryItem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Validator\Constraints\NotBlank;


use Backend\AdminBundle\CustomClass\Utils;


class GalleryController extends Controller
{
  private $itemsPerPage = 16;

  /**
   * Method({"GET"})
   * Route("/admin/gallery/")
   * Route("/admin/gallery")
   * Route("/admin/gallery/{page}", 
   *        name="backend_admin_display_gallery", 
   *        defaults={"page" = 1}, 
   *        requirements={
   *          "page": "\d+"
   *        })
   */

  public function displayGalleryAction($page = 1)
  {
    $em = $this->getDoctrine()->getManager();
    
    $dql   = 'SELECT a FROM BackendAdminBundle:GalleryItem a';
    
    $galleryQuery = $em->createQuery($dql)
                       ->setFirstResult(($page * $this->itemsPerPage) - $this->itemsPerPage)
                       ->setMaxResults($this->itemsPerPage);
    $galleryItemsForPageCurrentPage = $galleryQuery->getResult();

    $nbGalleryItems = count($em->createQuery($dql)->getResult());
    $nbPages = ceil($nbGalleryItems / $this->itemsPerPage);

    if ($nbGalleryItems == 0)
      $nbPages = 1;

    return $this->render('BackendAdminBundle:Gallery:gallery.html.twig', 
        array( 
              'galleryItems' => $galleryItemsForPageCurrentPage,
              'nbPages' => $nbPages
        ) 
    );
  }

  public function addItemGalleryAction(Request $request)
  {
      $galleryItem = new GalleryItem();
      $form = $this->createFormBuilder($galleryItem)
          ->add('name')
          ->add('file', 'file')
          ->add('save', SubmitType::class)
          ->getForm();

      $form->handleRequest($request);

      $em = $this->getDoctrine()->getManager();
      $query = $em->createQueryBuilder('p')
                  ->select('p')
                  ->from('BackendAdminBundle:GalleryItem', 'p')
                  ->orderBy('p.id', 'DESC');
      $lastImages = $query->setMaxResults(4)->getQuery()->getResult();

      if ($form->isValid()) {
          $em = $this->getDoctrine()->getManager();

          $galleryItem->upload();

          $em->persist($galleryItem);
          $em->flush();

          $request->getSession()
                  ->getFlashBag()
                  ->add('successMessage', 'L\'image a été ajoutée !');

          $dql   = 'SELECT a FROM BackendAdminBundle:GalleryItem a';
          $nbGalleryItems = count($em->createQuery($dql)->getResult());
          $lastPage = ceil($nbGalleryItems / $this->itemsPerPage);

          // http://symfony.com/doc/current/book/controller.html#forwarding-to-another-controller
          return $this->redirectToRoute('backend_admin_gallery_display', array('page' => $lastPage), 301);
      }

      return $this->render('BackendAdminBundle:Gallery:add-item.gallery.html.twig', 
                  array(
                    'form' => $form->createView(),
                    'lastImages' => $lastImages
                  ) 
              );
  }

  public function deleteItemGalleryAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();
 
    $entity = $em->getRepository('BackendAdminBundle:GalleryItem')
                 ->find($id);
    
    if ($entity) {
      $em->remove($entity);
      $em->flush();
      $request->getSession()
            ->getFlashBag()
            ->add('successMessage', 'L\'image a été correctement supprimée');
    } else {
      $request->getSession()
            ->getFlashBag()
            ->add('errorMessage', 'Il semblerait que cette image n\'existe plus');
    }

    return $this->redirectToRoute('backend_admin_gallery_display', array('page' => 1), 301);
  }
}
