<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ApiRecipeController
 *
 * @Route("/api", name="api_")
 *
 * @package App\Controller
 */
class ApiRecipeController extends AbstractController
{
  /**
   * get all user recipes
   *
   * @Route("/recipes", name="get_recipes", methods={"GET"})
   *
   * @param RecipeRepository    $repository
   * @param SerializerInterface $serializer
   *
   * @return JsonResponse
   */
  public function getRecipes(RecipeRepository $repository, SerializerInterface $serializer)
  {
    $user = $this->getUser();
    $recipes = $repository->findBy(['user' => $user->getId()]);
    $data = $serializer->serialize($recipes, 'json' ,["groups" => ["user:recipe"]]);

    return new JsonResponse($data, 200, [], true);
  }

  /**
   * add a new recipe
   *
   * @Route("/recipe", name="post_recipe", methods={"POST"})
   *
   * @param Request                $request
   * @param EntityManagerInterface $em
   * @param SerializerInterface    $serializer
   * @param ValidatorInterface     $validator
   *
   * @return JsonResponse
   */
  public function addRecipe(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator)
  {
      $data = $request->getContent();
      try {
        $recipe = $serializer->deserialize($data, Recipe::class, 'json');

        $user = $this->getUser();
        $recipe->setUser($user);
        $errors = $validator->validate($recipe);

        if (count($errors) > 0) {
          return $this->json($errors, 400);
        }
        $em->persist($recipe);
        $em->flush();

        return $this->json($recipe, 201, [], ["groups" => "user:recipe"]);

      } catch (NotEncodableValueException $e) {

        return $this->json([
          "status" => 400,
          "message" => $e->getMessage()
        ], 400);
      }
  }

  /**
   * get one user recipe
   *
   * @Route("/recipe/{id}", name="get_recipe", methods={"GET"})
   *
   * @param int              $id
   * @param RecipeRepository $recipeRepository
   *
   * @return JsonResponse
   */
  public function getRecipe(int $id, RecipeRepository $recipeRepository)
  {
    $user = $this->getUser();
    $recipe = $recipeRepository->findBy(["id" => $id,"user" => $user->getId()]);

     if (!$recipe) {
       $response = json_encode([
         "status" => 400,
         "message" => "Recette introuvable pour l'utilisateur ". $user->getEmail()
       ], JSON_UNESCAPED_UNICODE);

       return new JsonResponse($response, 400, [], true);
     }
    return $this->json($recipe, 200, [], ["groups" => "user:recipe"]);
  }

  /**
   * update a user recipe
   *
   * @Route("/recipe/{id}", name="update_recipe", methods={"PUT"})
   *
   * @param int                    $id
   * @param Request                $request
   * @param RecipeRepository       $RecipeRepository
   * @param EntityManagerInterface $em
   *
   * @return JsonResponse
   */
  public function updateRecipe(int $id, Request $request, RecipeRepository $Reciperepository, EntityManagerInterface $em)
  {
      $data = json_decode($request->getContent(), true);
      $user = $this->getUser();
      $recipe = $Reciperepository->findOneBy(["id" => $id,"user" => $user->getId()]);


      if (!$recipe) {
        $response = json_encode([
          "status" => 400,
          "message" => "Recette introuvable pour l'utilisateur ". $user->getEmail()
        ], JSON_UNESCAPED_UNICODE);

        return new JsonResponse($response, 400, [], true);
      }

      $form = $this->createForm(RecipeType::class, $recipe);
      $form->submit($data, false);

      foreach ($recipe->getIngredient() as $ingredient) {
        $ingredient->setRecipe($recipe);
      }
      $em->persist($recipe);
      $em->flush();

      return $this->json($recipe, 200, [], ["groups" => "user:recipe"]);
  }

  /**
   * delete a user recipe
   *
   * @Route("/recipe/{id}", name="delete_recipe", methods={"DELETE"})
   *
   * @param int                    $id
   * @param RecipeRepository       $RecipeRepository
   * @param EntityManagerInterface $em
   *
   * @return JsonResponse
   */
  public function deleteRecipe(int $id, RecipeRepository $RecipeRepository, EntityManagerInterface $em)
  {
      $user = $this->getUser();
      $recipe = $RecipeRepository->findOneBy(["id" => $id, "user" => $user->getId()]);

      if (!$recipe) {
        $response = json_encode([
          "status" => 400,
          "message" => "Recette introuvable pour l'utilisateur ". $user->getEmail() 
        ], JSON_UNESCAPED_UNICODE);

        return new JsonResponse($response, 400, [], true);
      }
      $em->remove($recipe);
      $em->flush();

      $response = json_encode([
        "status" => 200,
        "success" => "La recette à bien été suprimée"
      ], JSON_UNESCAPED_UNICODE);

      return new JsonResponse($response, 200, [], true);
  }
}
