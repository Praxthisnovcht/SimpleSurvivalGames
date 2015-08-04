<?php

namespace SimpleSurvivalGames;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener{

    private $ssg;

    public function __construct(Main $ssg){
        $this->ssg = $ssg;
    }

    public function onPreLogin(PlayerPreLoginEvent $event){
        if($this->ssg->game){
            $event->getPlayer()->kick("SG Match being played!", false);
        }elseif(count($this->ssg->getServer()->getOnlinePlayers()) >= count($this->ssg->settings["spawns"])){
            $event->getPlayer()->kick("Server full!", false);
        }
    }

    public function onJoin(PlayerJoinEvent $event){
        $spawn = $this->ssg->getFreeSpawn($event->getPlayer());
        if($spawn === null){
            $event->getPlayer()->kick("Internal error", false);
        }
        $event->getPlayer()->teleport($spawn);
    }

    public function onLeave(PlayerQuitEvent $event){
        foreach($this->ssg->settings["spawns"] as $key => $spawn){
            if(isset($spawn["occupiedBy"]) and $spawn["occupiedBy"] === $event->getPlayer()->getId()){
                unset($this->ssg->settings["spawns"][$key]["occupiedBy"]);
            }
        }
        if(count($players = $this->ssg->getServer()->getOnlinePlayers()) === 1 and $this->ssg->game){
            $this->ssg->end();
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        $event->getEntity()->kick("You lost the match!", false);
        if(count($players = $this->ssg->getServer()->getOnlinePlayers()) === 1 and $this->ssg->game){
            $this->ssg->end();
        }
    }

    public function onMove(PlayerMoveEvent $event){
        if(!$this->ssg->game){
            $event->setCancelled();
        }
    }

    public function onBlockBreak(BlockBreakEvent $event){
        if(!$event->getPlayer()->isOp()){
            $event->setCancelled();
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event){
        if(!$event->getPlayer()->isOp()){
            $event->setCancelled();
        }
    }

}