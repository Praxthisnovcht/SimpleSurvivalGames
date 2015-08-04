<?php

namespace SimpleSurvivalGames;

use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    public $settings;
    public $game = false;
    /**@var Timer*/
    public $timer;

    public function onEnable(){
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder()."settings.yml")){
            $r = $this->getResource("settings.yml");
            $o = stream_get_contents($r);
            fclose($r);
            file_put_contents($this->getDataFolder()."settings.yml", $o);
        }
        $this->settings = yaml_parse(file_get_contents($this->getDataFolder()."settings.yml"));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($task = new Timer($this), 1200, 1200);
        $this->timer = $task;
    }

    public function getFreeSpawn(Player $player){
        foreach($this->settings["spawns"] as $key => $spawn){
            if(isset($spawn["occupiedBy"])){
                continue;
            }
            $array = array_map("intval", explode(":", $spawn));
            $this->settings["spawns"][$key]["occupiedBy"] = $player->getId();
            return new Vector3($array[0], $array[1], $array[2]);
        }
        return null;
    }

    public function freeSpawns(){
        foreach($this->settings["spawns"] as $key => $spawn){
            if(isset($spawn["occupiedBy"])){
                unset($this->settings["spawns"][$key]["occupiedBy"]);
            }
        }
    }

    public function end(){
        $this->getServer()->broadcastMessage("SG Ends now!");
        foreach($this->getServer()->getOnlinePlayers() as $p){
            $p->sendMessage("You won the match!");
            $p->kick("Match Finished! You won!", false);
        }
        $this->timer->minute = $this->timer->total;
        $this->game = false;
    }

}