<?php

    namespace App\Controller;

    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Doctrine\ORM\EntityManagerInterface;
    use App\Entity\User;
    use App\Entity\CloudStorageProvider;
    use App\Service\AwsS3;
    use App\Repository\CloudStorageProviderRepository;

    class ProviderLoginController {
        private $manager;

        public function __construct(EntityManagerInterface $manager, CloudStorageProviderRepository $repository) {
            $this->manager = $manager;
            $this->repository = $repository;
        }

        //Permet de rajouter un nouveau CloudStorageProvider en BDD lié à un User
        //L'{id} utilisé est celui de l'USER connecté à l'application
        /**
         * @route("/create-provider/{id}", name="create-provider", methods={"POST"})
         *
         */
        public function createProvider(User $data, Request $request)
        {
            // $accessKey = password_hash($request->request->get('accessKey'), PASSWORD_ARGON2I);
            // $secretKey = password_hash($request->request->get('secretKey'), PASSWORD_ARGON2I);

            //Récupère les clés envoyées par l'utilisateur
            $accessKey = $request->request->get('accessKey');
            $secretKey = $request->request->get('secretKey');

            //Met les clés dans un tableau json
            $credentials = array("accessKey" => $accessKey, "secretKey" => $secretKey);
            json_encode($credentials);

            //Créé un nouvel objet CloudStorageProvider récupérant toutes les données à envoyer en BDD
            $provider = new CloudStorageProvider();
            $provider->setService($request->request->get('service'));
            //Rajoute le Provider correspondant au Service selectionné
            if ($request->request->get('service') === "S3") {
                $provider->setProvider("Amazon");
                //Récupère la liste des régions et la met dans un tableau json
                $bucketsRegions = AwsS3::BUCKETS_REGIONS;
                json_encode($bucketsRegions);
            }
            $provider->setUserRegion($request->request->get('userRegion'));
            $provider->setCredentials($credentials);
            $provider->setBucketsRegions($bucketsRegions);

            $data->addCloudStorageProvider($provider);

            $this->manager->flush();

            $response = "Nouveau Provider créé en BDD";
            return new Response($response);
        }

        //Permet de vérifier si l'User possède déjà une entrée en BDD qui est liée à un provider particulier
        /**
         * @route("/authenticate-provider", name="authenticate-provider", methods={"POST"})
         *
         */
        public function authenticate(Request $request){
            $id = $request->request->get('id');
            $service = $request->request->get('service');
            $data = $this->repository->findByUserAndService($id, $service);
            if($data){
                //Si une entrée existe, renvoie l'Id
                return new Response($data[0]['id']);
            } else {
                //Si aucune entrée existe, renvoie false
                return new Response("false");
            }
        }

        //Permet à un utilisateur de modifier ses clés d'accès d'un CloudStorageProvider en BDD
        //L'{id} est l'ID du CloudStorageProvider récupéré dans la méthode authenticate, pas celui de l'USER
        /**
         * @route("/modify-keys/{id}", name="modify-keys", methods={"PUT"})
         *
         */
        public function modifyKeys(CloudStorageProvider $data, Request $request)
        {
            $accessKey = $request->request->get('accessKey');
            $secretKey = $request->request->get('secretKey');

            //Met les clés dans un tableau json
            $credentials = array("accessKey" => $accessKey, "secretKey" => $secretKey);
            json_encode($credentials);

            $data->setCredentials($credentials);

            $this->manager->flush();

            $response = "Clés d'accès modifiées en BDD";
            return new Response($response);
        }

        //Permet de supprimer un CloudStorageProvider de la BDD
        //L'{id} est l'ID du CloudStorageProvider récupéré dans la méthode authenticate, pas celui de l'USER
        /**
         * @route("/delete-provider/{id}", name="delete-provider", methods={"DELETE"})
         *
         */
        public function deleteProvider(CloudStorageProvider $provider)
        {
            $this->manager->remove($provider);
            $this->manager->flush();

            $response = "Provider supprimé de la BDD";
            return new Response($response);
        }
    }
?>