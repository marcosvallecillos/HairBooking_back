<?php

namespace App\Controller;

use App\Entity\Productos;
use App\Form\ProductosType;
use App\Repository\ProductosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/productos')]
final class ProductosController extends AbstractController
{
    #[Route(name: 'app_productos_index', methods: ['GET'])]
    public function index(ProductosRepository $productosRepository): Response
    {
        return $this->render('productos/index.html.twig', [
            'productos' => $productosRepository->findAll(),
        ]);
    }
    #[Route('/list', methods: ['GET'], name: 'list')]
    public function list(EntityManagerInterface $em): JsonResponse{
        $productos = $em->getRepository(Productos::class)->findAll();
        $data = [];
        foreach($productos as $producto){
            $data[] = [
                'id' => $producto->getId(),
                'name' => $producto->getName(),
                'price' => $producto->getPrice(),
                'image' => $producto->getImage(),
                'cantidad'=> $producto->getCantidad(),
                'favorite'=> $producto->isFavorite(),
                'cart'=> $producto->isInsideCart(),
                'date'=> $producto->getFecha(),
                'compras'=> $producto->getCompras(),
                'categorias'=> $producto->getCategoria(),
                'subcategorias' => $producto->getSubCategoria(),

            ];
        }
        return new JsonResponse($data);
    }
    #[Route('/carrito/{id}', name: 'agregar_al_carrito', methods: ['POST'])]
    public function agregarAlCarrito(int $id,Request $request,EntityManagerInterface $em): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no autenticado'], 401);
        }

        $producto = $em->getRepository(Producto::class)->find($id);

        if (!$producto) {
            return new JsonResponse(['error' => 'Producto no encontrado'], 404);
        }

        $usuario = $entityManager->getRepository(Usuarios::class)->find($data['usuario_id']);
        $relacion = $usuarioProductoRepo->findOneBy([
            'user' => $user,
            'producto' => $producto
        ]);

        if (!$relacion) {
            $relacion = new UsuarioProducto();
            $relacion->setUser($user);
            $relacion->setProducto($producto);
            $relacion->setCantidad(1);
            $relacion->setInsideCart(true);
        } else {
            // Si ya está en carrito, incrementar cantidad
            $relacion->setCantidad($relacion->getCantidad() + 1);
            $relacion->setInsideCart(true);
        }

        $em->persist($relacion);
        $em->flush();

        return new JsonResponse([
            'message' => 'Producto agregado al carrito correctamente',
            'producto' => $producto->getName(),
            'cantidad' => $relacion->getCantidad()
        ]);
    }
    #[Route('/favoritos/{id}', name: 'agregar_a_favoritos', methods: ['GET','POST'])]
    public function agregarAFavoritos(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Buscar el producto por ID
        $producto = $em->getRepository(Productos::class)->find($id);

        // Verificar si el producto existe
        if (!$producto) {
            return new JsonResponse(['error' => 'Producto no encontrado'], 404);
        }

        // Alternar el estado de favorito
        $producto->setIsFavorite(!$producto->isFavorite());

        // Guardar los cambios en la base de datos
        $em->persist($producto);
        $em->flush();

        // Retornar la respuesta
        return new JsonResponse([
            'status' => 'success',
            'message' => $producto->isFavorite() ? 'Producto agregado a favoritos' : 'Producto removido de favoritos',
            'producto' => [
                'id' => $producto->getId(),
                'name' => $producto->getName(),
                'favorite' => $producto->isFavorite()
            ]
        ]);
    }
    #[Route('/new', name: 'app_productos_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = new Productos();
        $form = $this->createForm(ProductosType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($producto);
            $entityManager->flush();

            return $this->redirectToRoute('app_productos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('productos/new.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_productos_show', methods: ['GET'])]
    public function show(Productos $producto): Response
    {
        return $this->render('productos/show.html.twig', [
            'producto' => $producto,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_productos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Productos $producto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductosType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_productos_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('productos/edit.html.twig', [
            'producto' => $producto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_productos_delete', methods: ['POST'])]
    public function delete(Request $request, Productos $producto, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$producto->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($producto);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_productos_index', [], Response::HTTP_SEE_OTHER);
    }
}
