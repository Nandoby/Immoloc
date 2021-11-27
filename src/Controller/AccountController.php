<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Entity\UserImgModify;
use App\Form\AccountType;
use App\Form\ImgModifyType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AccountController extends AbstractController
{
    /**
     * Permet à l'utilisateur de se connecter
     * @Route("/login", name="account_login")
     */
    public function index(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();

        return $this->render('account/index.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    /**
     * Permet à l'utilisateur de se déconnecter
     * @Route("/logout", name="account_logout")
     */
    public function logout()
    {
        // ...
    }

    /**
     * Permet d'afficher le formulaire d'inscription et d'inscrire un utilisateur dans le site
     * @Route("/register", name="account_register")
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form['picture']->getData();

            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();
                try {
                    $file->move($this->getParameter('uploads_directory'), $newFilename);
                } catch (FileException $exception) {
                    return $exception->getMessage();
                }
                $user->setPicture($newFilename);
            }

            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre compte a bien été créé'
            );

            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier son profil
     * @Route("/account/profile", name="account_profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager)
    {
        $user = $this->getUser(); // récup l'utilisateur connecté
        $form = $this->createForm(AccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                "success",
                "Les données ont été enregistrées avec succès"
            );
        }

        return $this->render("account/profile.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Permet de modifier le mot de passe
     * @Route("/account/password-update", name="account_password")
     */
    public function updatePassword(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher)
    {
        $passwordUpdate = new PasswordUpdate();
        $user = $this->getUser();
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // vérif que le mot de passe correspond à l'ancien

            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                // gérer l'erreur
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est pas votre mot de passe actuel"));
            } else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $hasher->hashPassword($user, $newPassword);

                $user->setPassword($hash);
                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    "success",
                    "Votre mot de passe a bien été modifié"
                );

                return $this->redirectToRoute('homepage');
            }
        }

        return $this->render("account/password.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Permet de modifier l'avatar de l'utilisateur
     * @Route("/account/imgmodify", name="account_modifimg")
     */
    public function imgModify(): Response
    {
        $imgModify = new UserImgModify();
        $user = $this->getUser();
        $form = $this->createForm(ImgModifyType::class, $imgModify);


        return $this->render("account/imgModify.html.twig", [
            "form" => $form->createView()
        ]);
    }
}

