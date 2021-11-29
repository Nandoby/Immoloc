<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AnnonceType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdController extends AbstractController
{
    /**
     * @Route("/ads", name="ads_index")
     */
    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
     * Permet de créer une annonce
     * @Route("/ads/new", name="ads_create")
     */
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $ad = new Ad();

        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            // on ajoute l'auteur mais attention risque de bug jusqu'à security
            $ad->setAuthor($this->getUser());

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute('ads_show', ['slug' => $ad->getSlug()]);
        }

        return $this->render("ad/new.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifier une annonce
     * @Route("/ads/{slug}/edit", name="ads_edit")
     * @Security("( is_granted('ROLE_USER') and user === ad.getAuthor() ) or is_granted('ROLE_ADMIN')", message="Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier" )
     */
    public function edit(Request $request, EntityManagerInterface $manager, Ad $ad): Response
    {
        $form = $this->createForm(AnnonceType::class, $ad);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach($ad->getImages() as $image) {
                $image->setAd($ad);
                $manager->persist($image);
            }

            $manager->persist($ad);
            $manager->flush();

            $this->addFlash(
                'success',
                "L'annonce <strong>{$ad->getTitle()}</strong> a bien été modifiée !"
            );
        }

        return $this->render("ad/edit.html.twig", [
            "form" => $form->createView(),
            'ad' => $ad
        ]);
    }

    /**
     * Permet de supprimer une annonce
     * @Route("/ads/{slug}/delete", name="ads_delete")
     * @Security("( is_granted('ROLE_USER') and user === ad.getAuthor() ) or is_granted('ROLE_ADMIN')",
     *      message="Cette annonce ne vous appartient pas, vous ne pouvez pas l'effacer")
     */
    public function delete(Ad $ad, EntityManagerInterface $manager)
    {
        $this->addFlash(
            'success',
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimée"
        );

        $manager->remove($ad);
        $manager->flush();

        return $this->redirectToRoute('ads_index');
    }

    /**
     * Permet d'afficher une seule annonce
     * @Route("/ads/{slug}", name="ads_show")
     */
    public function show($slug, Ad $ad): Response
    {
        return $this->render('ad/show.html.twig', [
            'ad' => $ad
        ]);
    }
}
