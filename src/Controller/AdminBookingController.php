<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminBookingController extends AbstractController
{
    /**
     * Permet d'afficher les réservations
     * @Route("/admin/booking", name="admin_booking_index")
     */
    public function index(BookingRepository $repository): Response
    {
        return $this->render('admin/booking/index.html.twig', [
            'bookings' => $repository->findAll(),
        ]);
    }

    /**
     * Permet d'éditer une réservation
     * @Route("/admin/booking/{id}/edit", name="admin_booking_edit")
     */
    public function edit(Booking $booking, Request $request, EntityManagerInterface $manager)
    {
        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->setAmount(0); // 0 = empty -> donc la fonction dans le PrePersist de l'entity Booking va s'activer
            $manager->persist($booking); /* pas obligatoire parce que le booking est déjà créé */
            $this->addFlash(
                'success',
                'La réservation n° <strong>{$booking->getId()}</strong> a bien été modifiée'
            );
        }

        return $this->render('admin/booking/edit.html.twig', [
            "booking" => $booking,
            "form" => $form->createView()
        ]);
    }

    /**
     * Permet de supprimer une réservation
     * @Route("/admin/booking/{id}/delete", name="admin_booking_delete")
     * @param Booking $booking
     * @param EntityManagerInterface $manager
     */
    public function delete(Booking $booking, EntityManagerInterface $manager)
    {

    }
}
