<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\NewPasswordFormType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/mon-profil')]
class UtilisateurController extends AbstractController
{
    #[Route('/{id}', name: 'app_user', methods: ['GET'])]
    public function index(int $id, UtilisateurRepository $userRep): Response
    {
        $utilisateur = $userRep->findOneBy(['id' => $id]); 
        return $this->render('utilisateur/index.html.twig', [
            'user' => $utilisateur,
            'biens' => $utilisateur->getBiens(),
        ]);
    }

    #[Route('/{id}/changepassword', name: 'user_changepassword')]
    public function editPass(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, int $id, UtilisateurRepository $userRep)
    {
        $profilepageuser = $userRep->findOneBy(['id' => $id]); 
        if ($this->getUser() != $profilepageuser) {
            return $this->redirectToRoute('accueil');
        }

        if($request->isMethod('POST')){
            $user = $this->getUser();

            if($request->request->get('pass') == $request->request->get('pass2')){
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->request->get('pass')
                    )
                );
                $entityManager->flush();
                $this->addFlash('message', 'Le mot de passe a été modifié!');

                return $this->redirectToRoute('app_user',['id' => $user->getId()]);
            }else{
                $this->addFlash('error', 'Les mots de passe sont différents. Veuillez réessayer.');
            }
        }
        return $this->render('utilisateur/editpass.html.twig');
    }
}