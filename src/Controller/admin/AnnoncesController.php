<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AnnoncesRepository;
use App\Entity\Annonces;

/**
 * @Route("/admin/annonces", name="admin_annonces_")
 * @package App\Controller\admin
 */
class AnnoncesController extends AbstractController
{
      /**
       * @Route("/", name="home")
       */
      public function index(AnnoncesRepository $annoncesRepo): Response
      {
            return $this->render('admin/annonces/index.html.twig', [
                  'annonces' => $annoncesRepo->findAll()
            ]);
      }

      /**
       * @Route("/activer/{id}", name="activer")
       */
      public function activer(Annonces $annonce): Response
      {
            $annonce->setActive(($annonce->getActive()) ? false:true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($annonce);
            $em->flush();

            return new Response("true");
      }

      /**
       * @Route("/supprimer/{id}", name="supprimer")
       */
      public function supprimer(Annonces $annonce): Response
      {
            $em = $this->getDoctrine()->getManager();
            $em->remove($annonce);
            $em->flush();

            $this->addFlash('message', 'Annonce supprimé avec succès !');

            return $this->redirectToRoute('admin_annonces_home');
      }
}
