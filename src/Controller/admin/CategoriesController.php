<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;

/**
 * @Route("/admin/categories", name="admin_categories_")
 * @package App\Controller\admin
 */
class CategoriesController extends AbstractController
{
      /**
       * @Route("/", name="home")
       */
      public function index(CategoriesRepository $catsRepo): Response
      {
            return $this->render('admin/categories/index.html.twig', [
                  'categories' => $catsRepo->findAll()
            ]);
      }

      /**
       * @Route("/ajout", name="ajout")
       */
      public function ajoutCategorie(Request $request): Response
      {
            $categorie = new Categories;
            $form = $this->createForm(CategoriesType::class, $categorie);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                  $em = $this->getDoctrine()->getManager();
                  $em->persist($categorie);
                  $em->flush();

            return $this->redirectToRoute('admin_categories_home');
            }

            return $this->render('admin/categories/ajout.html.twig', [
                  'form' =>$form->createView()
            ]);
      }

      /**
      * @Route("/modifer/{{id}}", name="modifier")
      */
      public function modifCategorie(Categories $categories, Request $request): Response
      {
            $form = $this->createForm(CategoriesType::class, $categories);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                  $em = $this->getDoctrine()->getManager();
                  $em->persist($categories);
                  $em->flush();

            return $this->redirectToRoute('admin_categories_home');
            }

            return $this->render('admin/categories/ajout.html.twig', [
                  'form' =>$form->createView()
            ]);
      }
}
