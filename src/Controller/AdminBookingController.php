<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
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
        return $this->render('admin_booking/index.html.twig', [
            'bookings' => $repository->findAll(),
        ]);
    }

    /**
     * Permet d'éditer une réservation
     * @Route("/admin/booking/{id}/edit", name="admin_booking_edit")
     */
    public function edit(Booking $booking, Request $request)
    {
        $form = $this->createForm(AdminBookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // traitement
        }

        return $this->render('admin_booking/edit.html.twig', [
            "booking" => $booking,
            "form" => $form->createView()
        ]);
    }
}
