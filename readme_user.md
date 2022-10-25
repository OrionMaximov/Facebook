# Creation Projet avec user pas à pas

## création du projet

- composer create-project symfony/website-skeleton nom_projet

## creation du user

- ```php bin/console make:user```

## ajout des champs au user

- ```php bin/console make:entity```
-> User

## ajout de l'authentification

- ```php bin/console make:auth```
-> 1
-> Authenticator
-> SecurityController
-> yes

## ajout du crud user

- ```php bin/console make:crud```
-> User
-> SecurityController
-> no

## ajout du registration form

- ```php bin/console make:registration-form```
-> User
-> uniqEntity (depend on your project)
-> no
-> yes

## Configurer votre .env et creation de bdd

- DATABASE_URL="mysql://root:@127.0.0.1:3306/tutouser"
- ```php bin/console doctrine:database:create```

## création des autre entities pour mon projet

## creation de leurs crud

## migrations

- ```php bin/console make:migration```
- ```php bin/console doctrine:migrations:migrate```

## création d'une landing page : Home

- je crée un nouveau controller : Home
- ```php bin/console make:controller```
-> HomeController
- Je dois modifier sa route pour qu'elle corresponde à la landing page
- ```@Route("/home", name="home")```
- devient :
- ```@Route("/", name="home")```
- je peux aussi effectuer une redirection vers un autre controller ( cf UserController new )

## suppression du user new inutil car remplacé par le register

- Dans mon UserController dans la methode new je remplace :

<code><pre>
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
</pre></code>

- une redirection vers register
<code><pre>
    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        return $this->redirectToRoute('app_register');
    }
</pre></code>

## RegistrationFormType : ajout des elements de formulaire qui me manque(ex:fistName,lastName...)  

- ```->add('firstName')```

## \Security\Authenticator.php effectuer la redirection après inscription :

<code><pre>
// For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
</pre></code>

- a remplacer par :
 return new RedirectResponse($this->urlGenerator->generate('home'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);

## automatiser le ROLE_USER lors de l'enregistrement d'un nouvel utilisateur :

- RegistrationController :
    - juste après la validation :
    ```if ($form->isSubmitted() && $form->isValid()) {```
    - ```$user->setRoles(['ROLE_USER']);``` n'oubliez pas le type array []

## ajout d'un champ personnalisé dans la vue (template) registrationForm :

- {{ form_row(registrationForm.confirmPassword, {
            label: 'Confirm Password'
        }) }}
- ou
- {{ form_row(registrationForm.firstName) }}

## Confirmation du password

- form\RegistrationFormType.php
- ajout d'un nouveau champ confirmPassword copier/coller du plainPassword :
<code><pre>
->add('confirmPassword', PasswordType::class, [
    // instead of being set onto the object directly,
    // this is read and encoded in the controller
    'mapped' => false,
    'attr' => ['autocomplete' => 'new-password'],
    'constraints' => [
        new NotBlank([
            'message' => 'Please enter a password',
        ]),
        new Length([
            'min' => 6,
            'minMessage' => 'Your password should be at least {{ limit }} characters',
            // max length allowed by Symfony for security reasons
            'max' => 4096,
        ]),
    ],
])
</pre></code>

- j'effectue la comparaison dans mon RegistrationController :
- apres validation du formulaire je crée une condition pour comparer plainPassword et confirmPassword :

 if($form->get('plainPassword')->getData() === $form->get('confirmPassword')->getData() ){ ...

 - dans laquelle j'integre mon ash de plainpassword et l'enregistrement (persist et flush)

 } else {

- je renvoie mon utilisateur vers le formulaire avec une nouvelle variable 'passError' :

    return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'passError' => 'Les mots de pass ne sont pas identiques'
        ]);
 }


- dans la partie view (register.html.twig) j'affiche passError à l'utilisateur en le testant :

{% block body %}
    {% if passError is defined %}
        <div class="alert alert-danger" role="alert">{{ passError }}</div>
{% endif %}


## Installation du reset password
 - ```composer require symfonycasts/reset-password-bundle```
 - ```php bin/console make:reset-password```

 - dans le .env il faudra ensuite configurer le MAILER_DSN= 