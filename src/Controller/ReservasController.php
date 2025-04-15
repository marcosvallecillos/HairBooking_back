<?php

namespace App\Controller;

use App\Entity\Reservas;
use App\Form\ReservasType;
use App\Repository\ReservasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reservas')]
final class ReservasController extends AbstractController
{
    #[Route(name: 'app_reservas_index', methods: ['GET'])]
    public function index(ReservasRepository $reservasRepository): Response
    {
        return $this->render('reservas/index.html.twig', [
            'reservas' => $reservasRepository->findAll(),
        ]);
    }

   #[Route('/new', name: 'app_reservas_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $data = json_decode($request->getContent(), true);

    if ($data === null) {
        return new JsonResponse(['status' => 'JSON inválido'], 400);
    }

    if (empty($data['servicio']) || empty($data['peluquero']) || empty($data['dia']) || empty($data['hora']) || empty($data['usuario_id'])) {
        return new JsonResponse(['status' => 'Faltan datos obligatorios'], 400);
    }

    $reserva = new Reservas();
    $reserva->setServicio($data['servicio']);
    $reserva->setPeluquero($data['peluquero']);

    // Parsear el campo dia
    try {
        $dia = new \DateTime($data['dia']);
        $reserva->setDia($dia);
    } catch (\Exception $e) {
        return new JsonResponse(['status' => 'Formato de fecha inválido'], 400);
    }

    // Parsear el campo hora
    try {
        $hora = \DateTime::createFromFormat('H:i', $data['hora']);
        if ($hora === false) {
            return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
        }
        $reserva->setHora($hora);
    } catch (\Exception $e) {
        return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
    }

    // Validar usuario
    $usuario = $entityManager->getRepository(Usuarios::class)->find($data['usuario_id']);
    if (!$usuario) {
        return new JsonResponse(['status' => 'Usuario no encontrado'], 404);
    }
    $reserva->setUsuario($usuario);

    $entityManager->persist($reserva);
    $entityManager->flush();

    return new JsonResponse(['status' => 'Reserva creada'], 201);
}


    #[Route('/{id}', name: 'app_reservas_show', methods: ['GET'])]
    public function show(Reservas $reserva): Response
    {
        return $this->render('reservas/show.html.twig', [
            'reserva' => $reserva,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservas_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservas $reserva, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservasType::class, $reserva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservas_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservas/edit.html.twig', [
            'reserva' => $reserva,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservas_delete', methods: ['POST'])]
    public function delete(Request $request, Reservas $reserva, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reserva->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reserva);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservas_index', [], Response::HTTP_SEE_OTHER);
    }
}
