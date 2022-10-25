<?php

namespace App\Controller;

use App\Entity\User;
use Gumlet\ImageResize;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\AuntenticatorAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AuntenticatorAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $dirUpload=str_replace("\\","/",$this->getParameter('upload_directory')."/");
            $dirAvatar=str_replace("\\","/",$this->getParameter('avatar_directory')."/");
            $dirAvatarX=str_replace("\\","/",$this->getParameter('avatarx256_directory')."/");
            
            if($form->get('plainPassword')->getData() === $form->get('confirmPassword')->getData() ){
               
                move_uploaded_file($_FILES['registration_form']['tmp_name']['avatar'],$dirUpload.$_FILES['registration_form']['name']['avatar']);
              


                $user->setRoles(['ROLE_USER']);
                // encode the plain password
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();
                
                $image= new ImageResize($dirUpload.$_FILES['registration_form']['name']['avatar']);
                $image->resizeToWidth(100);
                $image->save($dirAvatar.$user->getId().".webP",IMAGETYPE_WEBP);
                $image2= new ImageResize($dirUpload.$_FILES['registration_form']['name']['avatar']);
                $image2->resizeToWidth(256);
                $image2->save($dirAvatarX.$user->getId()."x256.webP",IMAGETYPE_WEBP);

                unlink($dirUpload.$_FILES['registration_form']['name']['avatar']);
                // do anything else you need here, like send an email

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }else {
                return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
                'passError' => 'Les mots de pass ne sont pas identiques'
            ]);
            }
        }     
        return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
                
            ]);
           
    }
}
