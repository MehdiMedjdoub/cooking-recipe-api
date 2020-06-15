<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="api_auth_register",  methods={"POST"})
     */
    public function register(
      Request $request,
      UserPasswordEncoderInterface $passwordEncoder,
      GuardAuthenticatorHandler $guardHandler
      )
    {
      $data = json_decode($request->getContent(),true);

      if ( !isset($data['email']) || !isset($data['password'])) {
        $response = json_encode([
          "status" => 400,
          "message" => "un email et un mot de passe sont necessaire pour s'enregistrer"
        ], JSON_UNESCAPED_UNICODE);

        return new JsonResponse($response, 400, [], true);
      }

      try {

        $validator = Validation::createValidator();
        $notBlank = new Assert\NotBlank();
        $notBlank->message = "l'email ou le mot de passe ne peuvent etre vide";
        $constraint = new Assert\Collection(array(
          // the keys correspond to the keys in the input array
          'password' => $notBlank,
          'email' => $notBlank,
        ));

        $errors = $validator->validate($data, $constraint);
        if ($errors->count() > 0) {
            $response = json_encode(["erreur" => (string)$errors], JSON_UNESCAPED_UNICODE);
            return new JsonResponse($response, 500, [], true);
        }
        $user = new User();

        $user
          ->setEmail($data['email'])
          ->setRoles(['ROLE_USER'])
          ->setPassword($passwordEncoder->encodePassword($user,$data['password']));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $response = json_encode([
          "success" => "L' utilisateur ". $user->getUsername(). " est bien enregistré!"
        ], JSON_UNESCAPED_UNICODE);

        return new JsonResponse($response, 200, [], true);
      } catch (UniqueConstraintViolationException $e) {
        $response = json_encode([
          "status" => 400,
          "message" => "Un utilisateur existe deja avec cet email"
        ], JSON_UNESCAPED_UNICODE);

        return new JsonResponse( $response, 400, [], true);
      }
    }
}
