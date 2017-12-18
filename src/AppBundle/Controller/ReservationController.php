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
        $reservations = $this->getDoctrine()
            ->getRepository('AppBundle:Reservation')
            ->findAll();

        var_dump($reservations[0]->getBegin());

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

                    $days[$day-1]['reservations'][$hour.':'.$parseMinute]['data'] = '';
                    $days[$day-1]['reservations'][$hour.':'.$parseMinute]['status'] = '';
                }
            }
        }

        $days[1]['reservations']['6:40']['status'] = 'reserved';
        $days[1]['reservations']['6:50']['status'] = 'reserved';
        $days[1]['reservations']['7:00']['status'] = 'reserved';
        $days[1]['reservations']['7:10']['status'] = 'reserved';
        $days[1]['reservations']['7:20']['status'] = 'reserved';
        $days[1]['reservations']['7:30']['status'] = 'reserved';

        $days[2]['reservations']['16:40']['status'] = 'reserved';
        $days[2]['reservations']['16:50']['status'] = 'reserved';
        $days[2]['reservations']['17:00']['status'] = 'reserved';
        $days[2]['reservations']['17:10']['status'] = 'reserved';



//        $days[$day-1]['reservations']['6:40']['reserved'] = 1;

        return $this->render('reservations/index.html.twig', ['days' => $days]);
    }

    /**
     * @Route("/reservations/create", name="reservations_create")
     */
    public function createAction(Request $request)
    {
        return $this->render('reservations/create.html.twig');
    }

    /**
     * @Route("/reservations/details/{id}", name="reservations_details")
     */
    public function detailsAction($id)
    {
        return $this->render('reservations/details.html.twig');
    }

    /**
     * @Route("/reservations/edit/{id}", name="reservations_edit")
     */
    public function editAction($id, Request $request)
    {
        return $this->render('reservations/edit.html.twig');
    }
}
