<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;
use App\Entity\Annonces;
use App\Entity\Images;
use App\Form\AnnoncesType;
use App\Form\EditProfileType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig'/* , [
            'controller_name' => 'UserController',
        ] */);
    }

    /**
     * @Route("/user/annonces/ajout", name="user_annonces_ajout")
     */
    public function ajoutAnnonce(Request $request): Response
    {
        $annonce = new Annonces;

        $form = $this->createForm(AnnoncesType::class, $annonce);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $annonce->setUsers($this->getUser());
            $annonce->setActive(false);

            // recupere les images transmises
            $images = $form ->get('images')->getData();

            // on boucle sur les images
            foreach($images as $image)
            {
                // on génère un nouveau nom de fichier
                $fichiers = md5(uniqid()).'.'.$image->guessExtension();

                // on copie le fichier dans le dossier uploads
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichiers
                );

                // on stock le nom de l'image dans la bdd
                $img = new Images();
                $img->setName($fichiers);
                $annonce->addImage($img);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($annonce);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('user/annonces/ajout.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/profil/modifier", name="user_profil_modifier")
     */
    public function editProfil(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('message', 'Profil mis à jour');
            return $this->redirectToRoute('user');
        }

        return $this->render('user/edit_profil.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/pass/modifier", name="user_pass_modifier")
     */
    public function editPass(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {  
        if ($request->isMethod('POST'))
        {
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            // vérifie si les deux mdp sont identiques
            if ($request->request->get('pass_1') == $request->request->get('pass_2'))
            {
                $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('pass_1')));
                $em->flush();
                $this->addFlash('message', 'Mot de passe mis à jour avec succès');
                return $this->redirectToRoute('user');
            }
            else
            {
                $this->addFlash('error', 'Les deux mots de passe ne sont pas identiques');
            }
        }

        return $this->render('user/edit_pass.html.twig');
    }

    /**
     * @Route("/supprime/image/{id}", name="annonce_delete_image", methods={"DELETE"})
     */
    public function deleteImage(Images $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        
        // on vérifie si le token est valid
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token']))
        {
            // on récupère le nom de l'image
            $nom = $image->getName();

            // on supprime le fichier
            unlink($this->getParameter('images_directory').'/'.$nom);

            // on supprime le nom de l'image de la bdd
            $em = $this->getDoctrine()->getManager();
            $em ->remove($image);
            $em->flush();

            // on répond en json
            return new JsonResponse(['success'  => 1]);
        }
        else
        {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
