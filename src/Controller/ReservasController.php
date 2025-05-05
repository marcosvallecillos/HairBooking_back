<?php

namespace App\Controller;

use App\Entity\Reservas;
use App\Entity\Usuarios;
use App\Entity\Valoracion;
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
    public function index(ReservasRepository $reservasRepository): JsonResponse
    {
        $reservas = $reservasRepository->findAll();
        $data = [];
        
        foreach ($reservas as $reserva) {
            $valoracion = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getId() : null;
            $comentario = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getComentario() : null;
            $servicioRating = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getServicioRating() : null;
            $peluqueroRating = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getPeluqueroRating() : null;

            $data[] = [
                'id' => $reserva->getId(),
                'servicio' => $reserva->getServicio(),
                'peluquero' => $reserva->getPeluquero(),
                'precio' => $reserva->getPrecio(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUsuario() ? $reserva->getUsuario()->getId() : null,
                'valoracion' => $valoracion,
                'valoracion_comentario' => $comentario,
                'valoracion_servicio' => $servicioRating,
                'valoracion_peluquero' => $peluqueroRating
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/new', name: 'app_reservas_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
    
        $requiredFields = ['servicio', 'peluquero', 'dia', 'hora', 'usuario_id', 'precio'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['status' => "El campo '$field' es obligatorio"], 400);
            }
        }
    
        // Validar fecha
        $dia = \DateTime::createFromFormat('Y-m-d', $data['dia']);
        if (!$dia) {
            return new JsonResponse(['status' => 'Formato de fecha inválido (Y-m-d)'], 400);
        }
    
        // Validar hora
        $hora = \DateTime::createFromFormat('H:i', $data['hora']);
        if (!$hora) {
            return new JsonResponse(['status' => 'Formato de hora inválido (H:i)'], 400);
        }
    
        // Validar usuario
        $usuario = $entityManager->getRepository(Usuarios::class)->find($data['usuario_id']);
        if (!$usuario) {
            return new JsonResponse(['status' => 'Usuario no encontrado'], 404);
        }
    
        $reserva = new Reservas();
        $reserva->setServicio($data['servicio']);
        $reserva->setPeluquero($data['peluquero']);
        $reserva->setDia($dia);
        $reserva->setHora($hora);
        $reserva->setUsuario($usuario);
        $reserva->setPrecio($data['precio']);
    
        $entityManager->persist($reserva);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Reserva creada', 'reserva_id' => $reserva->getId()], 201);
    }
    
        #[Route('/usuario/{id}', name: 'app_reservas_by_usuario', methods: ['GET'])]
        public function reservasPorUsuario(int $id, ReservasRepository $reservasRepository): JsonResponse
        {
            $reservas = $reservasRepository->findBy(['usuario' => $id]);
            $data = [];

            foreach ($reservas as $reserva) {
                $valoracion = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getId() : null;
                $comentario = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getComentario() : null;
                $servicioRating = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getServicioRating() : null;
            $peluqueroRating = $reserva->getValoracions()->first() ? $reserva->getValoracions()->first()->getPeluqueroRating() : null;

                $data[] = [
                    'id' => $reserva->getId(),
                    'servicio' => $reserva->getServicio(),
                    'peluquero' => $reserva->getPeluquero(),
                    'precio'=> $reserva->getPrecio(),
                    'dia' => $reserva->getDia()->format('Y-m-d'),
                    'hora' => $reserva->getHora()->format('H:i'),
                    'usuario_id' => $reserva->getUsuario() ? $reserva->getUsuario()->getId() : null,
                    'valoracion' => $valoracion,
                    'valoracion_comentario' => $comentario,
                    'valoracion_servicio' => $servicioRating,
                    'valoracion_peluquero' => $peluqueroRating
                    

                ];
            }

            return new JsonResponse($data);
        }


    #[Route('/{id}', name: 'app_reservas_show', methods: ['GET'])]
    public function show(Reservas $reserva): JsonResponse
    {
        $data = [
            'id' => $reserva->getId(),
            'servicio' => $reserva->getServicio(),
            'peluquero' => $reserva->getPeluquero(),
            'precio' => $reserva->getPrecio(),
            'dia' => $reserva->getDia()->format('Y-m-d'),
            'hora' => $reserva->getHora()->format('H:i'),
            'usuario_id' => $reserva->getUsuario() ? $reserva->getUsuario()->getId() : null
        ];
        
        return new JsonResponse($data);
    }

    #[Route('/{id}/edit', name: 'app_reservas_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, Reservas $reserva, EntityManagerInterface $entityManager): JsonResponse
    {
        // If it's a GET request, return the reservation data
        if ($request->getMethod() === 'GET') {
            $data = [
                'id' => $reserva->getId(),
                'servicio' => $reserva->getServicio(),
                'peluquero' => $reserva->getPeluquero(),
                'precio' => $reserva->getPrecio(),
                'dia' => $reserva->getDia()->format('Y-m-d'),
                'hora' => $reserva->getHora()->format('H:i'),
                'usuario_id' => $reserva->getUsuario() ? $reserva->getUsuario()->getId() : null
            ];
            
            return new JsonResponse($data);
        }
        
        // For PUT requests, update the reservation
        $data = json_decode($request->getContent(), true);
        
        if ($data === null) {
            return new JsonResponse(['status' => 'JSON inválido'], 400);
        }
        
        if (isset($data['servicio'])) {
            $reserva->setServicio($data['servicio']);
        }
        
        if (isset($data['peluquero'])) {
            $reserva->setPeluquero($data['peluquero']);
        }

        if (isset($data['precio'])) {
            $reserva->setPrecio($data['precio']);
        } 
        if (isset($data['dia'])) {
            try {
                $dia = new \DateTime($data['dia']);
                $reserva->setDia($dia);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'Formato de fecha inválido'], 400);
            }
        }
        
        if (isset($data['hora'])) {
            try {
                $hora = \DateTime::createFromFormat('H:i', $data['hora']);
                if ($hora === false) {
                    return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
                }
                $reserva->setHora($hora);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'Formato de hora inválido'], 400);
            }
        }
        
        if (isset($data['usuario_id'])) {
            $usuario = $entityManager->getRepository(Usuarios::class)->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['status' => 'Usuario no encontrado'], 404);
            }
            $reserva->setUsuario($usuario);
        }
        
        $entityManager->flush();
        
        return new JsonResponse(['status' => 'Reserva actualizada']);
    }

    #[Route('/{id}', name: 'app_reservas_delete', methods: ['DELETE'])]
    public function delete(Reservas $reserva, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($reserva);
        $entityManager->flush();
        
        return new JsonResponse(['status' => 'Reserva eliminada']);
    }
}
