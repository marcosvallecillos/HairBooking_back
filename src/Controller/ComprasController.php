<?php

namespace App\Controller;

use App\Entity\Compra;
use App\Entity\CompraProducto;
use App\Entity\Usuarios;
use App\Entity\Productos;
use App\Repository\UsuariosRepository;
use App\Repository\ProductosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/compras')]
class ComprasController extends AbstractController
{
    #[Route('/usuarios/{usuarioId}/compras', name: 'realizar_compra', methods: ['GET', 'POST'])]
    public function realizarCompra(
        int $usuarioId,
        Request $request,
        EntityManagerInterface $em,
        UsuariosRepository $usuarioRepo,
        ProductosRepository $productoRepo
    ): JsonResponse {
        $usuario = $usuarioRepo->find($usuarioId);
        if (!$usuario) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $items = $data['productos'] ?? [];

        if (empty($items)) {
            return $this->json(['error' => 'No se proporcionaron productos'], 400);
        }

        $compra = new Compra();
        $compra->setUsuario($usuario);
        $compra->setFecha(new \DateTime());

        foreach ($items as $item) {
            $producto = $productoRepo->find($item['productoId']);
            if (!$producto) {
                return $this->json(['error' => "Producto ID {$item['productoId']} no encontrado"], 404);
            }

            $cantidad = (int) $item['cantidad'];
            $precioUnidad = $producto->getPrecio(); 

            $detalle = new CompraProducto();
            $detalle->setProducto($producto);
            $detalle->setCantidad($cantidad);
            $detalle->setPrecioUnidad($precioUnidad);
            $compra->addDetalle($detalle);
        }

        $em->persist($compra);
        $em->flush();

        return $this->json(['mensaje' => 'Compra registrada', 'compraId' => $compra->getId()], 201);
    }
}

