<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use App\Service\AutherizedApiAccess;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class UserController extends AbstractController
{
    private $userRepository,$passwordEncoder,$authorizedApiAccess,$validator;

    public function __construct(UserRepository $userRepository,UserPasswordEncoderInterface $passwordEncoder,AutherizedApiAccess $autherizedApiAccess,ValidatorInterface $validator)
    {
        $this->userRepository=$userRepository;
        $this->passwordEncoder=$passwordEncoder;
        $this->authorizedApiAccess=$autherizedApiAccess;
        $this->validator=$validator;
    }

    /**
     * @Route("api/admin/add", name="add_admin", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        $check=$this->authorizedApiAccess->authorize($request);
        $response=array();
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            $response= new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
        }
        else {

            $data = json_decode($request->getContent(), true);
            $newUser = new User();
            $newUser->setEmail($data['email']);
            $newUser->setPassword($data['password']);
            $newUser->setSuperAdmin( $data['superAdmin']);
            $newUser->setName( $data['name']);
            $newUser->setRoles($data['roles']);
            $errors=$this->validator->validate($newUser);

            if (count($errors) > 0) {
                throw new \Exception($errors[0]->getMessage());;
            } else {
                $this->userRepository->saveAdmin($newUser,$data);
                $response= new  JsonResponse(['status' => 'Admin created!'], Response::HTTP_CREATED);
            }
        }
        return $response;
    }
    /**
     * @Route("api/admin/delete/{id}", name="delete_admin", methods={"DELETE"},requirements={"id"="\d+"})
     */
    public function delete(Request $request,$id): JsonResponse
    {
        $check=$this->authorizedApiAccess->authorize($request);
        $user = $this->userRepository->findOneBy(['id' => $id]);
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            throw new \Exception($data,Response::HTTP_UNAUTHORIZED);
        }
        else if (!$user) {
            $data=["message"=>"Record not found"];
            throw new \Exception($data,Response::HTTP_NOT_FOUND);

        }
        else {
            $this->userRepository->removeAdmin($user);
            $response = new JsonResponse(['status' => 'Admin deleted'], Response::HTTP_NO_CONTENT);
        }
        return $response;
    }

    /**
     * @Route("api/admin/", name="get_all_admin", methods={"GET"})
     */
    public function getAll(Request $request): JsonResponse
    {
        $check=$this->authorizedApiAccess->authorize($request);
        $data=array();
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            throw new \Exception($data,Response::HTTP_UNAUTHORIZED);
        }
        else
        {
          $users = $this->userRepository->retriveSubAdmins($request->get('id'));
            foreach ($users as $user) {
                array_push($data, [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name'=>$user->getName(),
                    'SuperAdmin' => $user->getSuperAdmin(),
                    'Roles'=>$user->getRoles(),

                ]);
            }
            $response=new JsonResponse($data, Response::HTTP_OK);
        }
        return $response;
    }

    /**
     * @Route("api/admin/update/{id}", name="update_admin", methods={"PUT"},requirements={"id"="\d+"})
     */
    public function update($id, Request $request): JsonResponse
    {
        $check=$this->authorizedApiAccess->authorize($request);
        $user=$this->userRepository->validateUserId($id);
        if(!$check)
        {
            $data=["message"=>"user is unauthorized"];
            throw new \Exception($data,Response::HTTP_UNAUTHORIZED);
        }
        elseif (!$user) {
            $data=["message"=>"Record not found"];
            throw new \Exception($data,Response::HTTP_NOT_FOUND);
        }
        else{
            $data = json_decode($request->getContent(), true);
            empty($data['email']) ? true : $user->setEmail($data['email']);
            empty($data['password']) ? true :  $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $data['password']
            ));
            empty($data['superAdmin']) ? true : $user->setSuperAdmin($data['superAdmin']);
            empty($data['name']) ? true : $user->setName($data['name']);
            empty($data['roles']) ? true : $user->setRoles($data['roles']);

            $updatedCostumer = $this->userRepository->updateUser($user);
            $response=new JsonResponse($updatedCostumer->toArray(), Response::HTTP_OK);
        }
    return $response;
    }
}
