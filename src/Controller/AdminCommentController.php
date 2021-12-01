<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentController extends AbstractController
{
    /**
     * Permet d'administrer la liste des commentaires du site
     * @Route("/admin/comments", name="admin_comments_index")
     */
    public function index(CommentRepository $repo): Response
    {
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $repo->findAll(),
        ]);
    }

    /**
     * Permet d'éditer un commentaire
     * @Route("/admin/comments/{id}/edit", name="admin_comments_edit")
     *
     * @param Comment $comment
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function edit(Comment $comment, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le commentaire n° <strong>{$comment->getId()}</strong> a été modifié"
            );
        }

        return $this->render("admin/comment/edit.html.twig", [
            "comment" => $comment,
            "form" => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer un commentaire
     * @Route("/admin/comments/{id}/delete", name="admin_comments_delete")
     */
    public function delete(Comment $comment, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le commentaire n° {$comment->getId()} a bien été supprimé"
        );

        $manager->remove($comment);
        $manager->flush();

        return $this->redirectToRoute("admin_comments_index");
    }
}
