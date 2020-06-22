<?php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Aws\S3\Exception\S3Exception;
    use Aws\Exception\AwsException;
    use App\Repository\CloudStorageProviderRepository;
    use App\Service\AwsS3;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    /**
    * @route("/", name="api")
    *
    */
    class StorageController extends AbstractController {

        /**
         * @var ObjectManager
         */
        private $repository;

        public function __construct(CloudStorageProviderRepository $repository)
        {
            $this->repository= $repository;
        }

        //Création d'un nouveau Client
        public function createClient(Request $request) {
            //Récupère les données nécessaires d'un id spécifique de la table cloud_storage_provider
            $id = $request->query->get('id');
            if($id === null){
                $id = $request->request->get('id');
            } 
            $data = $this->repository->findByUser($id);
            $credentials = $data[0]['credentials'];
            $secretKey = $credentials['secretKey'];
            $region = $data[0]['userRegion'];
            $accessKey = $credentials['accessKey'];
            
            //Créé un nouveau Client AWS et le retourne
            $awsS3 = new AwsS3($region, $secretKey, $accessKey);
            return $awsS3;
        }

        /**
         * @route("/regions-list", name="regions-list", methods={"GET"})
         *
         */
        public function getRegionsLists(Request $request) {
            //Récupère la liste de toutes les régions disponibles pour un User spécifique et la retourne
            $id = $request->query->get('id');
            $data = $this->repository->findByUser($id);
            $regionsList = $data[0]['bucketsRegions'];
            $response = json_encode($regionsList);
            return new Response($response);
        }

        /**
         * @route("/create-bucket", name="create-bucket", methods={"PUT"})
         *
         */
        public function createBucket (Request $request) {
            //Récupère les données nécessaires
            $newBucketName = $request->request->get('bucketName');
            $newBucketRegion = $request->request->get('region');
            $client = $this->createClient($request);

            try{
                //Créé un nouveau Bucket
                $response = $client->createBucket($newBucketName, $newBucketRegion);
                return new Response($response);
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }

        /**
         * @route("/bucket-list", name="bucket-list", methods={"GET"})
         *
         */
        public function getBucketsList(Request $request) {
            //Récupère les données nécessaires
            $client = $this->createClient($request);

            try {
                //Récupère la liste des Buckets de l'User et la retourne
                $response = json_encode($client->getBucketsList());
                return new Response($response);
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }

        /**
         * @route("/bucket-content", name="bucket-content", methods={"GET"})
         *
         */
        public function getBucketContent(Request $request) {
            //Récupère les données nécessaires
            $client = $this->createClient($request);
            $bucketName = $request->query->get('bucketName');

            try{
                //Récupère la liste du contenu d'un Bucket spécfique et la retourne
                $response = $client->getBucketContent($bucketName);
                return new Response(json_encode($response));
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }

        /**
         * @route("/delete-item", name="delete-item", methods={"DELETE"})
         *
         */
        public function deleteFromBucket(Request $request) {
            //Récupère les données nécessaires
            $client = $this->createClient($request);
            $bucketName = $request->request->get('bucketName');
            $file = $request->request->get('key');

            try{
                //Supprime un item d'un Bucket
                $client->deleteFromBucket($file, $bucketName);
                $response = "item supprimmé";
                return new Response($response);
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }

        /**
         * @route("/download-item", name="download-item", methods={"GET"})
         *
         */
        public function downloadFromBucket(Request $request) {
            //Récupère les données nécessaires
            $client = $this->createClient($request);
            // $id = $request->query->get('id');
            $bucketName = $request->query->get('bucketName');
            $file = $request->query->get('key');

            try{
                //Récupère un item d'un Bucket et le télécharge
                $var = $client->downloadFromBucket($file, $bucketName);
                $response = "item téléchargé";
                return new Response($response);
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }

        /**
         * @route("/upload-item", name="upload-item", methods={"PUT"})
         *
         */
        public function uploadToBucket(Request $request) {
            //Récupère les données nécessaires
            $client = $this->createClient($request);
            $bucketName = $request->request->get('bucketName');
            $name = $request->request->get('key');

            try{
                //Récupère un item et l'upload dans un Bucket
                $client->uploadToBucket($bucketName, $name);
                $response = "item $name ajouté a $bucketName";
                return new Response($response);
            }catch (S3Exception $e){
                $response = $e->getMessage();
                $response = json_encode($response);
                return new Response($response);
            }
        }
    }
?>