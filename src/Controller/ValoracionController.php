<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Valoracion;
use App\Entity\Usuarios;
use Psr\Log\LoggerInterface;
use App\Entity\Reservas;
#[Route('/api/valoracion')]
final class ValoracionController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('', name: 'app_valoracion')]
    public function index(): Response
    {
        return $this->render('valoracion/index.html.twig', [
            'controller_name' => 'ValoracionController',
        ]);
    }

    #[Route('/valoraciones', name: 'api_crear_valoracion', methods: ['GET','POST'])]
public function crear(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = $request->request->all();
    if (empty($data)) {
        $content = $request->getContent();
        $data = json_decode($content, true);
    }

    if (empty($data)) {
        $this->logger->debug('Datos recibidos vacíos', [
            'content' => $request->getContent(),
            'form_data' => $request->request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->getMethod()
        ]);
        return new JsonResponse(['error' => 'No se recibieron datos'], 400);
    }

    if (!isset($data['servicioRating'], $data['peluqueroRating'], $data['comentario'], $data['usuario_id'])) {
        $this->logger->debug('Datos incompletos', ['recibido' => $data]);
        return new JsonResponse(['error' => 'Datos incompletos', 'recibido' => $data], 400);
    }

    if (!is_numeric($data['servicioRating']) || $data['servicioRating'] < 1 || $data['servicioRating'] > 5) {
        return new JsonResponse(['error' => 'servicioRating debe ser un número entre 1 y 5'], 400);
    }
    if (!is_numeric($data['peluqueroRating']) || $data['peluqueroRating'] < 1 || $data['peluqueroRating'] > 5) {
        return new JsonResponse(['error' => 'peluqueroRating debe ser un número entre 1 y 5'], 400);
    }
    if (empty($data['comentario'])) {
        return new JsonResponse(['error' => 'El comentario no puede estar vacío'], 400);
    }

    $usuario = $em->getRepository(Usuarios::class)->find($data['usuario_id']);
    if (!$usuario) {
        return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
    }


    $valoracion = new Valoracion();
    $valoracion->setServicioRating((int) $data['servicioRating']);
    $valoracion->setPeluqueroRating((int) $data['peluqueroRating']);
    $valoracion->setComentario($data['comentario']);
    $valoracion->setFecha(new \DateTime());
    $valoracion->setUsuario($usuario);

    try {
        $this->logger->debug('Antes de persistir', ['valoracion' => $data]);
        $em->persist($valoracion);
        $this->logger->debug('Después de persistir, antes de flush');
        $em->flush();
        $this->logger->debug('Después de flush', [
            'id' => $valoracion->getId(),
            'fecha' => $valoracion->getFecha() ? $valoracion->getFecha()->format('Y-m-d H:i:s') : null,
            'usuario_id' => $valoracion->getUsuario() ? $valoracion->getUsuario()->getId() : null,
        ]);

        return new JsonResponse([
            'id' => $valoracion->getId(),
            'servicioRating' => $valoracion->getServicioRating(),
            'peluqueroRating' => $valoracion->getPeluqueroRating(),
            'comentario' => $valoracion->getComentario(),
            'fecha' => $valoracion->getFecha()->format(\DateTimeInterface::ISO8601),
            'usuario_id' => $valoracion->getUsuario()->getId(),
                ], 201);
    } catch (\Exception $e) {
        $this->logger->error('Error al guardar la valoración', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'data' => $data,
            'valoracion_id' => $valoracion->getId()
        ]);
        return new JsonResponse([
            'error' => 'Error al guardar la valoración',
            'message' => $e->getMessage()
        ], 500);
    }
}

    #[Route('/list', name: 'api_listar_valoraciones', methods: ['GET'])]
    public function listar(EntityManagerInterface $em): JsonResponse
    {
        $valoraciones = $em->getRepository(Valoracion::class)->findAll();
        if (empty($valoraciones)) {
            return new JsonResponse(['message' => 'No se encontraron valoraciones'], 404);
        }

        $data = [];
        foreach ($valoraciones as $valoracion) {
            if (!$valoracion->getFecha() || !$valoracion->getUsuario()) {
                $this->logger->error('Valoración con datos inválidos', [
                    'id' => $valoracion->getId(),
                    'fecha' => $valoracion->getFecha(),
                    'usuario' => $valoracion->getUsuario()
                ]);
                continue;
            }
            $data[] = [
                'id' => $valoracion->getId(),
                'servicioRating' => $valoracion->getServicioRating(),
                'peluqueroRating' => $valoracion->getPeluqueroRating(),
                'comentario' => $valoracion->getComentario(),
                'fecha' => $valoracion->getFecha()->format(\DateTimeInterface::ISO8601),
                'usuario_id' => $valoracion->getUsuario()->getId(),
            ];
            
        }

        return new JsonResponse(['valoraciones' => $data], 200);
    }
}