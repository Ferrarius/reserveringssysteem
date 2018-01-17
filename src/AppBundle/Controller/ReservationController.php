<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Reservation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ReservationController extends Controller
{
    /**
     * @Route("/reservations", name="reservations")
     */
    public function indexAction(Request $request)
    {
        $week = $request->get('week') ?: date("W");
        $year = $request->get('year') ?: date("Y");
        $days = [];

        for($day=1; $day<=7; $day++)
        {
            $days[$day-1]['date'] = date('d/m/Y', strtotime($year."W".$week.$day))."\n";
            for($hour=0; $hour<24; $hour++)
            {
                for($minute=0; $minute<=60; $minute+=10)
                {
                    if($minute == 60) {
                        $parseMinute = 0;
                    } else {
                        $parseMinute = $minute;
                    }

                    if($parseMinute == 0) {
                        $parseMinute = '00';
                    }

                    $hour = sprintf("%02d", $hour);

                    if(!isset($days[$day-1]['reservations'][$hour.':'.$parseMinute]['status'])) {
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['id'] = '';
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['data'] = '';
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['status'] = '';
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['begin'] = '';
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['end'] = '';
                    }

                    if($parseMinute == '00') {
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['full'] = 1;
                    } else {
                        $days[$day-1]['reservations'][$hour.':'.$parseMinute]['full'] = 0;
                    }

                    $americanDate = preg_replace('/\s+/', '', implode("-", array_reverse(explode("/", $days[$day-1]['date']))));

                    if($reservation = $this->getDoctrine()->getManager()->createQuery("SELECT e FROM AppBundle:Reservation e WHERE e.begin = '".$americanDate." ".$hour.":".$parseMinute.":00'")->getResult()) {
                        $reservation = $reservation[0];

                        $begin = $reservation->getBegin();
                        $loopTime = $reservation->getBegin();
                        $end = $reservation->getEnd();

                        while($loopTime < $end) {
                            $days[$day-1]['reservations'][$loopTime->format("H:i")]['begin'] = $hour.':'.$parseMinute;
                            $days[$day-1]['reservations'][$loopTime->format("H:i")]['id'] = $reservation->getId();
                            $days[$day-1]['reservations'][$loopTime->format("H:i")]['end'] = $end->format("H:i");
                            $days[$day-1]['reservations'][$loopTime->format("H:i")]['data'] = $reservation->getName();
                            $days[$day-1]['reservations'][$loopTime->format("H:i")]['status'] = 'reserved';

                            $loopTime->modify('+10 minutes');
                        }
                    }
                }
            }
        }

        return $this->render('reservations/index.html.twig', ['days' => $days]);
    }

    /**
     * @Route("/reservations/store", name="reservations_create")
     */
    public function storeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $date = preg_replace('/\s+/', '', implode("-", array_reverse(explode("/", $request->get('date')))));

        $reservation = new Reservation();
        $reservation->setName($request->get('name'));
        $reservation->setBegin(new \DateTime($date.' '.$request->get('begin')));
        $reservation->setEnd(new \DateTime($date.' '.$request->get('end')));
        $reservation->setLocationId(1);

        $em->persist($reservation);

        $em->flush();

        return $this->redirectToRoute('reservations');
    }

    /**
     * @Route("/reservations/update/{id}", name="reservations_update")
     */
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository(Reservation::class)->find($id);

        if(!$reservation) {
            throw $this->createNotFoundException(
                'Geen reservering gevonden met nummer '.$id
            );
        }

        $date = preg_replace('/\s+/', '', implode("-", array_reverse(explode("/", $request->get('date')))));

        $reservation->setName($request->get('name'));
        $reservation->setBegin(new \DateTime($date.' '.$request->get('begin')));
        $reservation->setEnd(new \DateTime($date.' '.$request->get('end')));

        $em->flush();

        return $this->redirectToRoute('reservations');
    }

    /**
     * @Route("/reservations/delete/{id}", name="reservations_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $reservation = $em->getRepository(Reservation::class)->find($id);

        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('reservations');
    }
}
