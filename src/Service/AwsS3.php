<?php

    namespace App\Service;

    require '../vendor/autoload.php'; //Pour lancer le serveur de Symfony, retirer le "../", une fois le serveur lancé les remettre

    use Aws\S3\S3Client;
    use Aws\Credentials\Credentials;
    use Aws\S3\Exception\S3Exception;
    
    class AwsS3 extends AbstractStorageProvider {
        //Liste de toutes les régions des buckets AWS
        const BUCKETS_REGIONS = array(
            "Europe (Francfort)" => "eu-central-1",
            "Europe (Irlande)" => "eu-west-1",
            "Europe (Londres)" => "eu-west-2",
            "Europe (Paris)" => "eu-west-3",
            "Europe (Milan)" => "eu-south-1",
            "Europe (Stockholm)" => "eu-north-1",
            "US Est (Virginie du Nord)" => "us-east-1",
            "US Est (Ohio)" => "us-east-2",
            "US Ouest (Californie du Nord)" => "us-west-1",
            "US Ouest (Oregon)" => "us-west-2",
            "Afrique (Le Cap)" => "af-south-1",
            "Asie Pacifique (Hong Kong)" => "ap-east-1",
            "Asie Pacifique (Mumbai)" => "ap-south-1",
            "Asie Pacifique (Tokyo)" => "ap-northeast-1",
            "Asie Pacifique (Seoul)" => "ap-northeast-2",
            "Asie Pacifique (Osaka-Local)" => "ap-northeast-3",
            "Asie Pacifique (Singapour)" => "ap-southeast-1", 
            "Asie Pacifique (Sydney)" => "ap-southeast-2",
            "Canada (Central)" => "ca-central-1",
            "Chine (Pékin)" => "cn-north-1",
            "Chine (Ningxia)" => "cn-northwest-1",
            "Moyen-Orient (Bahreïn)" => "me-south-1",
            "Amérique du Sud (São Paulo)" => "sa-east-1"
        );

        public function __construct(String $region, String $secretKey, String $accessKey)
        {
            $this->region = $region;
            $this->secretKey = $secretKey;
            $this->accessKey = $accessKey;
        }

        //Création d'un client utilisé pour les opérations sur ses Buckets AWS
        public function createClient() {
            //Crée les Credentials du Client
            $credentials = new Credentials($this->accessKey , $this->secretKey);

            //Crée le Client avec ses infos
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => $this->region,
                'credentials' => $credentials,
                //A modifier : https
                'http' => [
                    'verify' => false
                ]
            ]);
            return $s3;
        }

        //Récupération de la région d'un Bucket spéficique
        public function getBucketRegion($bucketName){
            //Crée un Client
            $client = $this->createClient();
            //Récupère la région d'un Bucket
            $result = $client->getBucketLocation([
                'Bucket' => $bucketName,
            ]);

            return $result['LocationConstraint'];
        }

        //Vérification de la région du Bucket
        public function regionVerif($bucketName){
            //Crée un Client
            $client = $this->createClient();
            //Récupère la région du Bucket
            $bucketRegion = $this->getBucketRegion($bucketName);
        
            //Si la région du Client et celle du Bucket sont différentes, on créé un nouveau Client avec la région du Bucket
            if ($this->region !== $bucketRegion) {
                $credentials = new Credentials($this->accessKey , $this->secretKey);
        
                $client = new S3Client([
                    'version' => 'latest',
                    'region'  => $bucketRegion,
                    'credentials' => $credentials,
                    //A modifier : https
                    'http' => [
                        'verify' => false
                    ]
                ]);
            }
            return $client;
        }

        //Création d'un Bucket
        public function createBucket($newBucketName, $newBucketRegion) {
            //Crée un Client 
            $client = $this->createClient();
            //Si la région choisie pour créer le Bucket est différente de celle de l'User, on crée un nouveau Client avec la région désirée
            if($this->region !== $newBucketRegion && $newBucketRegion !== '') {
                $credentials = new Credentials($this->accessKey , $this->secretKey);

                $client = new S3Client([
                    'version' => 'latest',
                    'region'  => $newBucketRegion,
                    'credentials' => $credentials,
                    //A modifier : https
                    'http' => [
                        'verify' => false
                    ]
                ]);
            }
            //Crée le Bucket, avec son nom et sa région
            $client->createBucket(array('Bucket' => $newBucketName, 
                                        'LocationConstraint' => $newBucketRegion));
        }

        //Envoi d'un fichier sur un Bucket
        public function uploadToBucket($bucketName, $key) {
            //Vérifie que la région du Bucket et du Client sont les mêmes
            $client = $this->regionVerif($bucketName);
            $file = self::TARGET_PATH.$key;
            //Envoie le fichier sur le Bucket, où Key est le nom du fichier et SourceFile le fichier
            //A modifier : Rajouter la date sur le nom du fichier ?
            $client->putObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
                'SourceFile'   => $file,
                'ACL'    => 'public-read'
                ]);
        }

        //Récupération d'un fichier sur un Bucket AWS
        public function downloadFromBucket($file, $bucketName) {
            //Vérifie que la région du Bucket et du Client sont les mêmes
            $client = $this->regionVerif($bucketName);
            //Récupère le fichier depuis le Bucket
            $client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $file,
                'SaveAs' => self::SOURCE_PATH.$file
                ]);            
        }

        //Suppression d'un fichier sur un Bucket AWS
        public function deleteFromBucket($file, $bucketName) {
            //Vérifie que la région du Bucket et du Client sont les mêmes
            $client = $this->regionVerif($bucketName);
            //Supprime le fichier sur le Bucket
            $client->deleteObject([
                'Bucket' => $bucketName,
                'Key'    => $file
                ]);
        }

        //Création d'un tableau contenant la liste des Buckets d'un User
        public function getBucketsList() {
            //Crée un Client
            $client = $this->createClient();
            //Récupére la liste des Buckets du Client dans un tableau
            $bucketList = $client->listBuckets();

            //A modifier : Ajouter dans le tableau la région du Bucket ?
            return $bucketList['Buckets'];
        }

        //Création d'un tableau contenant la liste des contenus d'un Bucket
        public function getBucketContent($bucketName) {
            //Crée un Client
            $client = $this->regionVerif($bucketName);
            //Itère un tableau contenant les fichiers présents dans le Bucket
            $contentBucketList = $client->getIterator('ListObjects', array(
                'Bucket' => $bucketName
            ));
            foreach ($contentBucketList as $object) {
               $content[] = $object;
            }
            return $content;
        }
    }
?>