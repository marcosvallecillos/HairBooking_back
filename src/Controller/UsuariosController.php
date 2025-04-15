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
    #[Route('/login', name: 'app_usuarios_login', methods: ['GET','POST'])]
       
public function login(Request $request): Response
{
    $data = json_decode($request->getContent(), true);

    // Verificamos que los datos necesarios estén presentes
    if (!isset($data['email']) || !isset($data['password'])) {
        return new Response(
            json_encode([
                'status' => 'bad',
                'message' => 'Faltan datos requeridos'
            ]),
            400,
            ['Content-Type' => 'application/json']
        );
    }

    $email = $data['email'];
    $password = $data['password'];

    // Creamos los criterios de búsqueda
    $criteria = ['email' => $email, 'password' => $password];

    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository("App\Entity\Usuario")->findOneBy($criteria);

    $result = [];
    if ($user !== null) {
        $result['status'] = 'ok';
        $result['email'] = $user->getEmail();
        $result['nombre'] = $user->getNombre();
        $result['apellidos'] = $user->getApellidos();
        $result['telefono'] = $user->getTelefono();
    } else {
        $result['status'] = 'bad';
        $result['email'] = '';
        $result['nombre'] = '';
        $result['apellidos'] = '';
        $result['telefono'] = '';
    }

    return new Response(
        json_encode($result),
        200,
        ['Content-Type' => 'application/json']
    );
}

     
#[Route('/{id}/edit', methods: ['PUT'], name: 'app_usuarios_edit')]
public function edit(Request $request, Usuarios $usuario, EntityManagerInterface $entityManager): JsonResponse
{
    $data = json_decode($request->getContent(), true); // Se recibe la información en JSON.

    // Actualizamos los campos del usuario con los datos recibidos
    $usuario->setNombre($data['nombre'] ?? $usuario->getNombre());
    $usuario->setApellidos($data['apellidos'] ?? $usuario->getApellidos());
    $usuario->setEmail($data['email'] ?? $usuario->getEmail());
    $usuario->setTelefono($data['telefono'] ?? $usuario->getTelefono());
    if (isset($data['password'])) {
        $usuario->setPassword($data['password']);
    }

    $entityManager->flush();

    return new JsonResponse(['status' => 'Usuario actualizado']);
}

   #[Route('/{id}', name: 'app_usuarios_delete', methods: ['DELETE'])]
public function delete(Usuarios $usuario, EntityManagerInterface $entityManager): JsonResponse
{
    $entityManager->remove($usuario);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Usuario eliminado con éxito'], 200);
}
    



}
