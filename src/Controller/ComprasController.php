<?php

namespace App\Controller;

use App\Entity\Compra;
use App\Entity\CompraProducto;
use App\Entity\Usuarios;
use App\Entity\Productos;
use App\Repository\UsuariosRepository;
use App\Repository\ProductosRepository;
use App\Repository\CompraRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/compras')]
class ComprasController extends AbstractController
{
    #[Route(name: 'app_compras_index', methods: ['GET'])]
    public function index(CompraRepository $comprasRepository): JsonResponse
    {
        $compras = $comprasRepository->findAll();
        $data = [];
        
        foreach ($compras as $compra) {
            $data[] = [
                'id' => $compra->getId(),
                'nombre' => $compra->getName(),
                'imagen' => $compra->getImage(),
                'cantidad' => $compra->getCantidad(),
                'precio' => $compra->getPrice(),
                'fecha' => $compra->getFecha()->format('Y-m-d'),
                'detalles' => array_map(function (CompraProducto $detalle) {
                    return [
                        'productoId' => $detalle->getProducto()->getId(),
                        'nombre' => $detalle->getProducto()->getName(),
                        'cantidad' => $detalle->getCantidad(),
                        'precioUnitario' => $detalle->getPrecio(),
                        'total' => $detalle->getTotal()
                    ];
                }, $compra->getDetalles()->toArray()),
                'usuario' => $compra->getUsuario() ? [
                    'id' => $compra->getUsuario()->getId(),
                    'nombre' => $compra->getUsuario()->getNombre(),
                    'apellidos' => $compra->getUsuario()->getApellidos(),
                    'email' => $compra->getUsuario()->getEmail(),
                    'telefono' => $compra->getUsuario()->getTelefono(),
                ] : null,
            ];
        }
        
        return new JsonResponse($data);
    }

    #[Route('/usuarios/{usuarioId}/compras', name: 'realizar_compra', methods: ['GET','POST'])]
    public function realizarCompra(
        int $usuarioId,
        Request $request,
        EntityManagerInterface $em,
        UsuariosRepository $usuarioRepo,
        ProductosRepository $productoRepo
    ): JsonResponse {
        try {
            $usuario = $usuarioRepo->find($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }
        
            $data = json_decode($request->getContent(), true);
            if ($data === null) {
                return $this->json(['error' => 'JSON inválido'], 400);
            }
        
            $items = $data['productos'] ?? [];
            if (empty($items)) {
                return $this->json(['error' => 'No se proporcionaron productos'], 400);
            }
        
            $compra = new Compra();
            $compra->setUsuario($usuario);
            $compra->setFecha(new \DateTime());
            $compra->setImage("default.jpg"); // Puedes modificar esto según necesites
            $compra->setCantidad(0); // Se actualizará con la suma de las cantidades
            $compra->setPrice(0); // Se actualizará con la suma de los precios
        
            $totalCantidad = 0;
            $totalPrecio = 0;
            $primerProducto = null;
        
            foreach ($items as $item) {
                $producto = $productoRepo->find($item['productoId']);
                if (!$producto) {
                    return $this->json(['error' => "Producto ID {$item['productoId']} no encontrado"], 404);
                }
        
                $cantidad = (int) $item['cantidad'];
                if ($cantidad <= 0) {
                    return $this->json(['error' => "Cantidad inválida para producto ID {$item['productoId']}"], 400);
                }
        
                $precioUnidad = $producto->getPrice();
                $totalCantidad += $cantidad;
                $totalPrecio += ($precioUnidad * $cantidad);
        
                $detalle = new CompraProducto();
                $detalle->setProducto($producto);
                $detalle->setCantidad($cantidad);
                $detalle->setPrecio($precioUnidad);
                $compra->addDetalle($detalle);
                $detalle->setCompra($compra);
                
                // También agregamos el producto a la relación ManyToMany
                $compra->addProducto($producto);
                
                // Guardamos el primer producto para el nombre
                if ($primerProducto === null) {
                    $primerProducto = $producto;
                    $compra->setName($producto->getName());
                }
            }
        
            // Actualizamos los totales en la compra
            $compra->setCantidad($totalCantidad);
            $compra->setPrice($totalPrecio);
        
            $em->persist($compra);
            $em->flush();
        
            return $this->json([
                'mensaje' => 'Compra registrada',
                'compraId' => $compra->getId(),
                'total' => $totalPrecio,
                'cantidadTotal' => $totalCantidad
            ], 201);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/usuarios/{usuarioId}/historial', name: 'historial_compras', methods: ['GET'])]
    public function getHistorialCompras(
        int $usuarioId,
        EntityManagerInterface $em,
        UsuariosRepository $usuarioRepo
    ): JsonResponse {
        try {
            $usuario = $usuarioRepo->find($usuarioId);
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }

            $compras = $em->getRepository(Compra::class)->findBy(['usuario' => $usuario], ['fecha' => 'DESC']);
            
            $data = [];
            foreach ($compras as $compra) {
                $detalles = [];
                foreach ($compra->getDetalles() as $detalle) {
                    $producto = $detalle->getProducto();
                    $detalles[] = [
                        'productoId' => $producto->getId(),
                        'nombre' => $producto->getName(),
                        'cantidad' => $detalle->getCantidad(),
                        'precioUnitario' => $detalle->getPrecio(),
                        'total' => $detalle->getTotal()
                    ];
                }

                $data[] = [
                    'id' => $compra->getId(),
                    'nombre' => $compra->getName(),
                    'fecha' => $compra->getFecha()->format('Y-m-d'),
                    'total' => $compra->getPrice(),
                    'cantidadTotal' => $compra->getCantidad(),
                    'detalles' => $detalles
                ];
            }

            return $this->json([
                'status' => 'success',
                'usuario_id' => $usuarioId,
                'compras' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/carrito/cantidad/{productoId}', name: 'actualizar_cantidad', methods: ['POST'])]
    public function actualizarCantidad(
        int $productoId,
        Request $request,
        EntityManagerInterface $em,
        ProductosRepository $productoRepo
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['usuario_id']) || !isset($data['cantidad'])) {
                return $this->json(['error' => 'Datos incompletos'], 400);
            }

            $producto = $productoRepo->find($productoId);
            if (!$producto) {
                return $this->json(['error' => 'Producto no encontrado'], 404);
            }

            // Aquí puedes agregar la lógica para actualizar la cantidad en tu base de datos
            // Por ejemplo, si tienes una tabla de carrito:
            $carrito = $em->getRepository('App\Entity\Carrito')->findOneBy([
                'usuario' => $data['usuario_id'],
                'producto' => $productoId
            ]);

            if ($carrito) {
                $carrito->setCantidad($data['cantidad']);
                $em->flush();
                return $this->json([
                    'status' => 'success',
                    'message' => 'Cantidad actualizada correctamente'
                ]);
            }

            return $this->json(['error' => 'Producto no encontrado en el carrito'], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    
    #[Route('/delete/{id}', name: 'app_compras_delete', methods: ['DELETE'])]
    public function delete(Compra $compra, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($compra);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Usuario eliminado con éxito'], 200);
    }
    
}

