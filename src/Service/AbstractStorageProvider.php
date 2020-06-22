<?php

    namespace App\Service;

    abstract class AbstractStorageProvider {

        const SOURCE_PATH = 'SOURCE/PATH'; //Chemin à modifier
        const TARGET_PATH = 'TARGET/PATH'; //Chemin à modifier
        protected $secretKey;
        protected $accessKey;
        protected $region;

        //Creation d'un Bucket
        abstract function createBucket(string $newBucketName, string $newBucketRegion);

        //Envoi d'un fichier sur un Bucket
        abstract function uploadToBucket(string $bucketName, string $key);

        //Récuparation et téléchargement d'un fichier sur un Bucket
        abstract function downloadFromBucket(string $file, string $bucketName);

        //Suppression d'un fichier sur un Bucket
        abstract function deleteFromBucket(string $file, string $bucketName);

        //Récupération de la liste de tous les Buckets d'un User
        abstract function getBucketsList();

        //Récupération de la liste du contenu d'un Bucket
        abstract function getBucketContent(string $bucketName);
    }
?>