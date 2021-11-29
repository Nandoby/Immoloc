<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Booking;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    /**
     * @Route("/ads/{slug}/book", name="booking_create")
     * @IsGranted("ROLE_USER")
     */
    public function book(Request $request, Ad $ad, EntityManagerInterface $manager): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // traitement
            $user = $this->getUser();
            $booking->setBooker($user)
                ->setAd($ad)
            ;

            // si les dates ne sont pas disponible, message d'erreur
            if (!$booking->isBookableDates()) {
                $this->addFlash(
                    'warning',
                    'Les dates que vous avez choisies ne peuvent être réservées, elles sont déjà prises'
                );
            } else {
                $manager->persist($booking);
                $manager->flush();
            }

        }

        return $this->render('booking/book.html.twig', [
            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }
}
