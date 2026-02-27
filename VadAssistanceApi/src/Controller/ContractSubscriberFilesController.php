<?php
declare(strict_types=1);

namespace App\Controller;

use function Cake\Error\dd;

/**
 * ContractSubscriberFiles Controller
 *
 * @property \App\Model\Table\ContractSubscriberFilesTable $ContractSubscriberFiles
 */
class ContractSubscriberFilesController extends AppController
{

    public $contractSubscriberFiles;


    public function initialize(): void
    {
        parent::initialize();

        $this->contractSubscriberFiles = $this->fetchTable('ContractSubscriberFiles');
    }
   public function download($filePath)
    {   
        $relativePath = base64_decode($filePath); // récupère le vrai chemin


        $filepath = STORAGE_PATH . urldecode($relativePath);
        
        // Décode l'URL pour récupérer le vrai chemin
        if (!file_exists($filepath) || !is_file($filepath)) {
            // Ajout du \ devant pour CakePHP 5
            throw new \Cake\Http\Exception\NotFoundException('Fichier introuvable');
        }

        // Envoi du fichier au navigateur pour téléchargement
        return $this->response->withFile($filepath, [
            'download' => true,
        ])
        ->withType('pdf'); // On force le Content-Type: application/pdf
    }


}
