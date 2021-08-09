<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function PHPUnit\Framework\returnArgument;

class SecurityController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('review');
         }

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
     * @Route("/resetPassword", name="reset_password")
     */
    public function resetPassword():Response
    {
        return  $this->render('security/reset_password.html.twig');
    }
    /**
     * @Route("/resetPasswordAction", name="reset_password_action")
     */
    public function resetPasswordAction(Request $request):Response
    {
        $user=$this->getUser();
        $currentPassword= $request->request->all()['CurrentPassword'];
        $newPassword = $request->request->all()['newPassword'];
        $confirmPassword=$request->request->all()['confirmPassword'];
         $status = $this->userRepository->userPasswordMatch($user,$currentPassword);
//        dd($status);
        if(!$status)
        {
             $this->addFlash('error','current password is wrong');
        }
        if(empty($newPassword) || empty($confirmPassword))
        {
            $this->addFlash('error','new password fields cant be empty');
        }
        elseif ($newPassword!==$confirmPassword )
        {
            $this->addFlash('error','passwords does not match');
        }
        else
        {
                $this->userRepository->passwordUpdate($user->getId(),$newPassword);
                $this->addFlash('success','password updated successfully');
        }
        return $this->redirectToRoute('review');
   }
}
