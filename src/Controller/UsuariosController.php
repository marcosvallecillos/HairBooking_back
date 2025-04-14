<?php

namespace App\Controller;

use App\Entity\Usuarios;
use App\Form\UsuariosType;
use App\Repository\UsuariosRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
#[Route('/api/usuarios')]
final class UsuariosController extends AbstractController
{
    #[Route(name: 'app_usuarios_index', methods: ['GET'])]
    public function index(UsuariosRepository $usuariosRepository): Response
    {
        return $this->render('usuarios/index.html.twig', [
            'usuarios' => $usuariosRepository->findAll(),
        ]);
    }

        #[Route('/new', name: 'app_usuarios_new', methods: ['GET','POST'])]
        public function new(Request $request, EntityManagerInterface $entityManager): Response
        {
            $data = json_decode($request->getContent(), true);
        
            if ($data === null) {
                return new JsonResponse(['status' => 'JSON inválido'], 400);
            }
        
            if (empty($data['password'])) {
                return new JsonResponse(['status' => 'El password es obligatorio'], 400);
            }
        
            $usuario = new Usuarios();
            $usuario->setNombre($data['nombre'] ?? null);
            $usuario->setApellidos($data['apellidos'] ?? null);
            $usuario->setEmail($data['email'] ?? null);
            $usuario->setPassword($data['password']);
            $usuario->setTelefono($data['telefono'] ?? null);
        
            $entityManager->persist($usuario);
            $entityManager->flush();
        
            return new JsonResponse(['status' => 'Usuario creado'], 201);
        }

    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'])]
    public function show(Usuarios $usuario): Response
    {
        return $this->render('usuarios/show.html.twig', [
            'usuario' => $usuario,
        ]);
        
    }

    #[Route('/{id}/edit', name: 'app_usuarios_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UsuariosType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuarios/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_usuarios_delete', methods: ['POST'])]
    public function delete(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_usuarios_index', [], Response::HTTP_SEE_OTHER);
    }
  /*#[Route('/{id}', name: 'app_usuarios_delete', methods: ['DELETE'])]
public function delete(Usuarios $usuario, EntityManagerInterface $entityManager): JsonResponse
{
    $entityManager->remove($usuario);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Usuario eliminado con éxito'], 200);
}*/
    


    #[Route('/login', name: 'app_usuarios_login', methods: ['GET    '])]
    public function login(Request $request, UsuariosRepository $usuariosRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (empty($data['email']) || empty($data['password'])) {
            return new JsonResponse(['message' => 'El email y la contraseña son obligatorios'], 400);
        }
    
        $usuario = $usuariosRepository->findOneBy(['email' => $data['email']]);
    
        if (!$usuario || !$passwordHasher->isPasswordValid($usuario, $data['password'])) {
            return new JsonResponse(['message' => 'Credenciales inválidas'], 401);
        }
    
        return new JsonResponse([
            'id' => $usuario->getId(),
            'email' => $usuario->getEmail(),
            'nombre' => $usuario->getNombre(),
            'message' => 'Inicio de sesión exitoso'
        ], 200);
    }
    
}
