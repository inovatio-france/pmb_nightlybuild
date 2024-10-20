<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SendQueue.php,v 1.2 2024/07/19 09:27:01 jparis Exp $

namespace Pmb\DSI\Models;

use Pmb\Common\Models\Model;
use Pmb\Common\Helper\Helper;
use Pmb\DSI\Orm\SendQueueOrm;

class SendQueue extends Model implements CRUD
{
    protected $ormName = "Pmb\DSI\Orm\SendQueueOrm";
    public $idSendQueue = 0;
    public $channelType = 0;
    public $settings = "";
    public $flag = 0;
    public $numSubscriberDiffusion = 0;
    public $numDiffusionHistory = 0;

    public const NB_PER_PASS = 50;

    public function __construct(int $id = 0)
    {
        $this->id = $id;
        $this->read();
    }

    /**
     * Cr�ation d'une nouvelle entr�e dans la file d'attente
     * @return void
     */
    public function create()
    {
        $orm = new $this->ormName();

        $orm->channel_type = $this->channelType;
        $orm->settings = json_encode($this->settings);
        $orm->num_subscriber_diffusion = $this->numSubscriberDiffusion;
        $orm->num_diffusion_history = $this->numDiffusionHistory;
        $orm->flag = $this->flag;

        $orm->save();

        $this->id = $orm->{$this->ormName::$idTableName};
        $this->{Helper::camelize($this->ormName::$idTableName)} = $orm->{$this->ormName::$idTableName};
    }

    /**
     * Lecture des donn�es depuis la base
     * @return void
     */
    public function read()
    {
        $this->fetchData();
        $this->settings = json_decode($this->settings);
    }

    /**
     * Mise � jour de l'entr�e dans la file d'attente
     * @return void
     */
    public function update()
    {
        $orm = new $this->ormName($this->id);

        $orm->channel_type = $this->channelType;
        $orm->settings = json_encode($this->settings);
        $orm->num_subscriber_diffusion = $this->numSubscriberDiffusion;
        $orm->num_diffusion_history = $this->numDiffusionHistory;
        $orm->flag = $this->flag;

        $orm->save();
    }

    /**
     * Suppression de l'entr�e dans la file d'attente
     * @return void
     */
    public function delete()
    {
        $orm = new $this->ormName($this->id);
        $orm->delete();
    }

    /**
     * R�cup�ration des prochaines entr�es � traiter
     * @param int $numDiffusionHistory
     * @return array
     */
    public static function getNext(int $numDiffusionHistory): array
    {
        $instance = new self();

        $nextQueue = $instance->getList([
            "num_diffusion_history" => $numDiffusionHistory,
            "flag" => 0
        ]);

        return array_slice($nextQueue, 0, self::NB_PER_PASS);
    }

    /**
     * Marquer les prochaines entr�es comme trait�es
     * @param int $numDiffusionHistory
     * @return void
     */
    public static function flagNext(int $numDiffusionHistory): void
    {
        $nextQueue = self::getNext($numDiffusionHistory);

        foreach ($nextQueue as $queue) {
            $queue->flag = 1;
            $queue->update();
        }
    }

    /**
     * Remplir la file d'attente avec de nouveaux abonn�s
     * @param array $subscribersIds
     * @param int $numDiffusionHistory
     * @param int $channelType
     * @return void
     */
    public static function fillQueue(array $subscribersIds, int $numDiffusionHistory, int $channelType): void
    {
        $instances = array_map(function($id) use ($numDiffusionHistory, $channelType) {
            $instance = new self();

            $instance->numSubscriberDiffusion = $id;
            $instance->numDiffusionHistory = $numDiffusionHistory;
            $instance->channelType = $channelType;
            $instance->flag = 0;

            return $instance;
        }, $subscribersIds);

        // Cr�ation en masse si possible
        foreach ($instances as $instance) {
            $instance->create();
        }
    }

    /**
     * Nettoyer la file d'attente si tous les �l�ments sont flag�s
     * @param int $numDiffusionHistory
     * @return void
     */
    public static function cleanQueue(int $numDiffusionHistory): void
    {
        if (empty(self::getNext($numDiffusionHistory))) {
            $instance = new self();    

            $list = $instance->getList([
                "num_diffusion_history" => $numDiffusionHistory
            ]);
    
            foreach ($list as $element) {
                $element->delete();
            }
        }
    }


    /**
     * R�cup�re les �l�ments restants dans la file d'attente
     *
     * @param int $numDiffusionHistory
     * @return array
     */
    public static function getRemaining(int $numDiffusionHistory): array
    {
        $instance = new self(); 

        $remainingElements = $instance->getList([
            "num_diffusion_history" => $numDiffusionHistory,
            "flag" => 0
        ]);

        return $remainingElements;
    }

    /**
     * R�cup�re tous les �l�ments la file d'attente
     *
     * @param int $numDiffusionHistory
     * @return array
     */
    public static function getAll(int $numDiffusionHistory): array
    {
        $instance = new self(); 

        $remainingElements = $instance->getList([
            "num_diffusion_history" => $numDiffusionHistory,
        ]);

        return $remainingElements;
    }

    public static function initSettings(int $numDiffusionHistory, array $settings): void
    {

        $sendQueueElements = SendQueueOrm::find("num_diffusion_history", $numDiffusionHistory);
        foreach ($sendQueueElements as $sendQueueElement) {
            $sendQueueElement->settings = json_encode($settings);
            $sendQueueElement->save();
        }
    }

    public static function getSettings(int $numDiffusionHistory)
    {
        $sendQueueOrm = SendQueueOrm::find("num_diffusion_history", $numDiffusionHistory);
        if(!empty($sendQueueOrm)) {
            $settings = json_decode($sendQueueOrm[0]->settings);
            return $settings;
        }

        return "";
    }
}