<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/utilisateurs", name="utilisateurs", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

/**
 * @Route("/utilisateurs/modifier/{id}", name="modifier_utilisateur")
 */
public function editUser(Request $request, User $user, EntityManagerInterface $em) {

    $form = $this->createForm(EditUserType::class,$user);
    
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
    $em->flush();
    
    return $this->redirectToRoute('admin_utilisateurs');
    }
    
    return $this->render('admin/editUser.html.twig', ['formUser' => $form->createView()]);
    }
 

    /**
     * @Route("/{id}", name="admin_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_index');
    }
}
