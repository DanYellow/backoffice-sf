<?php

namespace Backend\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Backend\AdminBundle\Entity\Project;

class APIController extends Controller
{
    public function getProjectsAction($category = null)
    {
    	$em = $this->getDoctrine()->getManager();

      $query = $em->createQueryBuilder('p')
                  ->select('p')
                  ->from('BackendAdminBundle:Project', 'p')
                  ->andWhere('p.isOnline = 1')
                  ->leftJoin('p.category', 'c');

      if ($category !== null && $category !== "all") {

        $query->andWhere('c.name = :categoryName')
              ->setParameters(array('categoryName' => $category));
      }
      
      $projects = $query->getQuery()->getResult();

      /*{
        "id": 12,
        "name": null,
        "thumb": "d_chantier_chartres3-thumb.jpg",
        "description": "En chantier !",
        "category": "perso",
        "online": true,
        "images": "p_chantier_chartres1.jpg,p_chantier_chartres2.jpg,p_chantier_chartres3.jpg,p_chantier_chartres4.jpg",
        "createdAt": "17/09/2013 12:00:06",
        "realisedAt": "17/09/2013"
      }*/
      
      $projectsJSON = array();
      foreach ($projects as $key => $project) {
      	
      	$projectImages = [];
      	foreach ($project->getGalleryItems() as $galleryItem) {
      		$projectImages[] = $galleryItem->getPath();
      	}

      	$projectsJSON[] = array(
															'id'          => $project->getId(), 
															'name'        => $project->getTitle(),
															'thumb'       => '',
															'description' => $project->getDescription(), 
															'category'    => $project->getCategory()->getSlugName(),
															'images'      => implode(',', $projectImages),
															'online'      => true,
															'createdAt'   => $project->getCreatedAt()->format('Y-m-d H:i:s'),
															'realisedAt'  => '17/09/2013'
      											 );
      }


      return new JsonResponse($projectsJSON);
    }
}
